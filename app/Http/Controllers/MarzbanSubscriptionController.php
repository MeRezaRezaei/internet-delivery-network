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

        // 2. Extract UUID from Marzban (Support VLESS or VMess)
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
        $hosts = SubHost::where('is_active', true)->get();

        foreach ($hosts as $host) {
            $uris[] = $this->buildVlessUri($uuid, $host);
        }

        return array_values(array_unique($uris));
    }

    /**
     * Build complex VLESS + XHTTP URI according to Xray v1.8.8+ standards.
     */
    protected function buildVlessUri($uuid, $host)
    {
        // 1. Prepare the Extra Object (XHTTP logic)
        $extra = [
            'headers' => $host->extra['headers'] ?? [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36'
            ],
            'xPaddingBytes' => $host->extra['xPaddingBytes'] ?? '100-500',
            'noGRPCHeader' => $host->extra['noGRPCHeader'] ?? false,
            'scMaxEachPostBytes' => $host->extra['scMaxEachPostBytes'] ?? '1000000-2000000',
            'scMinPostsIntervalMs' => $host->extra['scMinPostsIntervalMs'] ?? '50-150',
            'xmux' => $host->extra['xmux'] ?? [
                'maxConcurrency' => '8-16',
                'maxConnections' => 0,
                'cMaxReuseTimes' => '10-20',
                'hMaxRequestTimes' => '100-300',
                'hMaxReusableSecs' => '120-240',
                'hKeepAlivePeriod' => 0
            ]
        ];

        // 2. Add downloadSettings (The core of Split-Domain Mapping)
        if ($host->download_address) {
            $extra['downloadSettings'] = [
                'address' => $host->download_address,
                'port' => $host->download_port ?: ($host->is_reverse ? 2096 : 8443),
                'serverName' => $host->download_sni ?: $host->download_address,
            ];

            // Add certificates for REVERSE hosts
            if ($host->is_reverse && $host->cert_pem) {
                // Split PEM if multiple exist
                $certs = array_filter(explode('-----END CERTIFICATE-----', $host->cert_pem));
                $formattedCerts = [];
                foreach ($certs as $cert) {
                    $formattedCerts[] = trim($cert) . "\n-----END CERTIFICATE-----";
                }
                $extra['downloadSettings']['certificates'] = $formattedCerts;
            }
        }

        // 3. Build Base Parameters
        $params = [
            'encryption' => 'none',
            'security' => $host->security ?: 'tls',
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

        // 4. Handle PCS for Reverse Proxy
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
        $used = $user->used_traffic ?? 0;
        $total = $user->data_limit ?? 0;
        $expire = $user->expire ? $user->expire : 0;

        return "upload=0; download={$used}; total={$total}; expire={$expire}";
    }
}
