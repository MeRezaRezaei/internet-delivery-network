<?php

namespace App\Http\Controllers;

use App\Models\Marzban\User;
use App\Models\Marzban\Jwt;
use App\Models\SubHost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MarzbanSubscriptionController extends Controller
{
    /**
     * Primary endpoint for the subscription.
     * Detects if request comes from a browser or a VPN client.
     */
    public function show(Request $request, $token)
    {
        $startTime = microtime(true);
        Log::info("Sub request: " . $token . " UA: " . $request->header('User-Agent'));

        if ($token === 'admin') {
            return redirect('/sub/admin');
        }

        // 1. Extract Username from Token (Marzban JWT logic)
        $username = $this->extractUsername($token);
        $user = User::where('username', $username)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if ($user->status !== 'active' && $user->status !== 'on_hold') {
            return response()->json(['error' => 'Account is ' . $user->status], 403);
        }

        // 2. Extract UUID/Password from Marzban (Support VLESS, VMess, Trojan)
        $proxy = $user->proxies()->whereIn('type', ['VLESS', 'VMess', 'trojan'])->first();
        if (!$proxy) {
            return response()->json(['error' => 'No compatible proxy found for user'], 404);
        }

        $uuid = $proxy->settings['id'] ?? ($proxy->settings['password'] ?? null);
        if (!$uuid) {
            return response()->json(['error' => 'UUID/Password not found'], 500);
        }

        // 3. Generate URIs based on SUBHOST PRIMACY
        $uris = $this->generateUris($uuid, $user);

        // 4. Handle Dual-Mode Output
        $userAgent = $request->header('User-Agent', '');
        $accept = $request->header('Accept', '');
        
        $isBrowser = str_contains($accept, 'text/html') || 
                     (preg_match('/Mozilla|Chrome|Safari|Opera|Edge/i', $userAgent) && 
                      !preg_match('/v2ray|Clash|Streisand|Shadowrocket|Quantumult|v2fly/i', $userAgent));

        $duration = round((microtime(true) - $startTime) * 1000, 2);
        Log::info("Sub generated in {$duration}ms for {$username} (Hosts: " . count($uris) . ")");

        if (($isBrowser || $request->has('html')) && !$request->has('raw')) {
            $subLink = url()->current();
            return view('sub.show', compact('user', 'uris', 'subLink'));
        }

        $content = base64_encode(implode("\n", $uris));

        return response($content)
            ->header('Content-Type', 'text/plain; charset=utf-8')
            ->header('Profile-Title', 'IDN Advanced Subscription')
            ->header('Profile-Update-Interval', '12')
            ->header('Subscription-Userinfo', $this->getUserInfo($user));
    }

    /**
     * Generates VLESS URIs exclusively from SubHost model.
     */
    protected function generateUris($uuid, $user)
    {
        $uris = [];
        $hosts = SubHost::where('is_active', true)->where('is_template', false)->get();

        foreach ($hosts as $host) {
            try {
                $uris[] = $this->buildVlessUri($uuid, $host);
            } catch (\Exception $e) {
                Log::error("Failed to build URI for host {$host->id}: " . $e->getMessage());
            }
        }

        return array_values(array_unique($uris));
    }

    /**
     * Build complex VLESS + XHTTP URI according to Xray v1.8.8+ standards.
     */
    protected function buildVlessUri($uuid, $host)
    {
        // 1. Prepare Automated Extra Object
        $extra = [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36'
            ],
            'xPaddingBytes' => $host->padding ?: '100-500',
            'noGRPCHeader' => (bool)$host->no_grpc_header,
        ];

        if ($host->sc_max_each_post_bytes) $extra['scMaxEachPostBytes'] = $host->sc_max_each_post_bytes;
        if ($host->sc_min_posts_interval_ms) $extra['scMinPostsIntervalMs'] = $host->sc_min_posts_interval_ms;

        // XMUX Logic
        if ($host->xmux_max_concurrency || $host->xmux_max_connections) {
            $extra['xmux'] = [
                'maxConcurrency' => $host->xmux_max_concurrency ?: '16-32',
                'maxConnections' => $host->xmux_max_connections ?: 0,
                'cMaxReuseTimes' => $host->xmux_c_max_reuse_times ?: '64-128',
                'hMaxRequestTimes' => $host->xmux_h_max_request_times ?: '800-900',
                'hMaxReusableSecs' => $host->xmux_h_max_reusable_secs ?: '120-240',
                'hKeepAlivePeriod' => 0
            ];
        }

        // 2. Add downloadSettings (Split-Domain Mapping)
        if ($host->download_address) {
            $extra['downloadSettings'] = [
                'address' => $host->download_address,
                'port' => (int)($host->download_port ?: ($host->is_reverse ? 2096 : 8443)),
                'serverName' => $host->download_sni ?: $host->download_address,
            ];

            if ($host->is_reverse && $host->cert_pem) {
                $certs = array_filter(explode('-----END CERTIFICATE-----', $host->cert_pem));
                $formattedCerts = [];
                foreach ($certs as $cert) {
                    $formattedCerts[] = trim($cert) . "\n-----END CERTIFICATE-----";
                }
                $extra['downloadSettings']['certificates'] = $formattedCerts;
            }
        }

        // Allow manual overrides from 'extra' column if present
        if ($host->extra && is_array($host->extra)) {
            $extra = array_replace_recursive($extra, $host->extra);
        }

        // 3. Build Base Parameters
        // CDN Logic: If is_cdn is true and security is not explicitly set, default to 'none' if user requested it
        $security = $host->security ?: ($host->is_cdn ? 'tls' : 'tls');
        
        $params = [
            'encryption' => 'none',
            'security' => $security,
            'sni' => $host->sni ?: $host->address,
            'alpn' => $host->alpn ?: 'h2',
            'insecure' => $host->insecure ? 1 : 0,
            'allowInsecure' => $host->insecure ? 1 : 0,
            'type' => 'xhttp',
            'host' => $host->host ?: ($host->sni ?: $host->address),
            'path' => $host->path ?: '/',
            'mode' => $host->mode ?: 'packet-up',
            'extra' => json_encode($extra),
        ];

        if ($host->flow && $host->flow !== 'none') {
            $params['flow'] = $host->flow;
        }

        if ($host->is_reverse && $host->pcs) {
            $params['pcs'] = $host->pcs;
        }

        $queryString = http_build_query($params);
        $remark = urlencode(($host->remark_prefix ?? "IDN") . " - " . $host->name);

        return "vless://{$uuid}@{$host->address}:{$host->port}?{$queryString}#{$remark}";
    }

    /**
     * Logic to extract username from Marzban subscription token.
     */
    protected function extractUsername($token)
    {
        if (strlen($token) < 15) return $token;

        try {
            $secretKey = Jwt::first()?->secret_key;
            $fallbackKey = "4b94c8100695518162091460fe0f5170e115f997464311c076bab2839418ac67";
            $keys = array_filter([$secretKey, $fallbackKey]);

            $data_b64_str = substr($token, 0, -10);
            $signature = substr($token, -10);

            foreach ($keys as $key) {
                $expected_signature = substr(
                    str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(hash('sha256', $data_b64_str . $key, true))),
                    0, 10
                );
                
                if ($signature === $expected_signature) {
                    $decoded = base64_decode(str_replace(['-', '_'], ['+', '/'], $data_b64_str));
                    if ($decoded !== false && strpos($decoded, ',') !== false) {
                        return explode(',', $decoded)[0];
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Token extraction failed: " . $e->getMessage());
        }

        return $token;
    }

    /**
     * Helper for subscription info header.
     */
    protected function getUserInfo($user)
    {
        $used = (int)($user->used_traffic ?? 0);
        $total = (int)($user->data_limit ?? 0);
        $expire = (int)($user->expire ? $user->expire : 0);

        return "upload=0; download={$used}; total={$total}; expire={$expire}";
    }
}
