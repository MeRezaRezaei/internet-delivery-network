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
        return $this->handleSubscription($request, $token, false);
    }

    /**
     * Development endpoint for the subscription.
     */
    public function showDev(Request $request, $token)
    {
        return $this->handleSubscription($request, $token, true);
    }

    /**
     * Core handler for subscription generation.
     */
    protected function handleSubscription(Request $request, $token, $isDev)
    {
        $startTime = microtime(true);
        Log::info("Sub request: " . $token . " UA: " . $request->header('User-Agent') . " DevMode: " . ($isDev ? 'YES' : 'NO'));

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

        // 3. Generate URIs based on SUBHOST PRIMACY and Dev mode
        $uris = $this->generateUris($uuid, $user, $isDev);

        // 4. Handle Dual-Mode Output
        $userAgent = $request->header('User-Agent', '');
        $accept = $request->header('Accept', '');
        
        $isBrowser = str_contains($accept, 'text/html') || 
                     (preg_match('/Mozilla|Chrome|Safari|Opera|Edge/i', $userAgent) && 
                      !preg_match('/v2ray|Clash|Streisand|Shadowrocket|Quantumult|v2fly/i', $userAgent));

        $duration = round((microtime(true) - $startTime) * 1000, 2);
        Log::info("Sub generated in {$duration}ms for {$username} (Hosts: " . count($uris) . ") DevMode: " . ($isDev ? 'YES' : 'NO'));

        if (($isBrowser || $request->has('html')) && !$request->has('raw')) {
            $subLink = url()->current();
            return view('sub.show', compact('user', 'uris', 'subLink'));
        }

        $content = base64_encode(implode("\n", $uris));

        return response($content)
            ->header('Content-Type', 'text/plain; charset=utf-8')
            ->header('Profile-Title', $isDev ? 'IDN Dev Subscription' : 'IDN Advanced Subscription')
            ->header('Profile-Update-Interval', '12')
            ->header('Subscription-Userinfo', $this->getUserInfo($user));
    }

    /**
     * Generates VLESS URIs exclusively from SubHost model.
     */
    protected function generateUris($uuid, $user, $isDev = false)
    {
        $uris = [];
        $hosts = SubHost::where('is_active', true)
            ->where('is_template', false)
            ->where('is_dev', $isDev)
            ->get();

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
        // Load relationships if not already loaded
        if (!$host->relationLoaded('tlsProfile') && $host->tls_profile_id) {
            $host->load('tlsProfile');
        }
        if (!$host->relationLoaded('xhttpProfile') && $host->xhttp_profile_id) {
            $host->load('xhttpProfile');
        }
        if (!$host->relationLoaded('xmuxProfile') && $host->xmux_profile_id) {
            $host->load('xmuxProfile');
        }
        if (!$host->relationLoaded('downloadHost') && $host->download_host_id) {
            $host->load('downloadHost');
        }

        $tlsSettings = $host->tlsProfile ? ($host->tlsProfile->settings ?? []) : [];
        $xhttpSettings = $host->xhttpProfile ? ($host->xhttpProfile->settings ?? []) : [];
        $xmuxSettings = $host->xmuxProfile ? ($host->xmuxProfile->settings ?? []) : [];

        // 1. Prepare Automated Extra Object with Profile overrides
        $padding = $xhttpSettings['padding'] ?? $host->padding ?: '100-500';
        $noGrpcHeader = isset($xhttpSettings['no_grpc_header']) ? (bool)$xhttpSettings['no_grpc_header'] : (bool)$host->no_grpc_header;
        
        $extra = [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36'
            ],
            'xPaddingBytes' => $padding,
            'noGRPCHeader' => $noGrpcHeader,
        ];

        $scMaxEachPostBytes = $xhttpSettings['sc_max_each_post_bytes'] ?? $host->sc_max_each_post_bytes;
        if ($scMaxEachPostBytes) $extra['scMaxEachPostBytes'] = $scMaxEachPostBytes;

        $scMinPostsIntervalMs = $xhttpSettings['sc_min_posts_interval_ms'] ?? $host->sc_min_posts_interval_ms;
        if ($scMinPostsIntervalMs) $extra['scMinPostsIntervalMs'] = $scMinPostsIntervalMs;

        // XMUX Logic
        $xmuxMaxConcurrency = $xmuxSettings['xmux_max_concurrency'] ?? $host->xmux_max_concurrency;
        $xmuxMaxConnections = $xmuxSettings['xmux_max_connections'] ?? $host->xmux_max_connections;
        $xmuxCMaxReuseTimes = $xmuxSettings['xmux_c_max_reuse_times'] ?? $host->xmux_c_max_reuse_times;
        $xmuxHMaxRequestTimes = $xmuxSettings['xmux_h_max_request_times'] ?? $host->xmux_h_max_request_times;
        $xmuxHMaxReusableSecs = $xmuxSettings['xmux_h_max_reusable_secs'] ?? $host->xmux_h_max_reusable_secs;

        if ($xmuxMaxConcurrency || $xmuxMaxConnections) {
            $extra['xmux'] = [
                'maxConcurrency' => $xmuxMaxConcurrency ?: '16-32',
                'maxConnections' => $xmuxMaxConnections ?: 0,
                'cMaxReuseTimes' => $xmuxCMaxReuseTimes ?: '64-128',
                'hMaxRequestTimes' => $xmuxHMaxRequestTimes ?: '800-900',
                'hMaxReusableSecs' => $xmuxHMaxReusableSecs ?: '120-240',
                'hKeepAlivePeriod' => 0
            ];
        }

        // 2. Add downloadSettings (Split-Domain Mapping)
        $downloadAddress = null;
        $downloadPort = null;
        $downloadSni = null;
        $downloadCertPem = null;
        $downloadIsReverse = false;

        if ($host->downloadHost) {
            $dlHost = $host->downloadHost;
            $downloadAddress = $dlHost->address;
            $downloadPort = $dlHost->port;
            $downloadSni = $dlHost->sni ?: $dlHost->address;
            
            // Resolve download host TLS settings
            $dlTlsSettings = $dlHost->tlsProfile ? ($dlHost->tlsProfile->settings ?? []) : [];
            $downloadCertPem = $dlTlsSettings['cert_pem'] ?? $dlHost->cert_pem;
            $downloadIsReverse = $dlHost->is_reverse;
        } else {
            $downloadAddress = $host->download_address;
            $downloadPort = $host->download_port;
            $downloadSni = $host->download_sni;
            
            // Fallback to host direct fields / profiles
            $downloadCertPem = $tlsSettings['cert_pem'] ?? $host->cert_pem;
            $downloadIsReverse = $host->is_reverse;
        }

        if ($downloadAddress) {
            $extra['downloadSettings'] = [
                'address' => $downloadAddress,
                'port' => (int)($downloadPort ?: ($downloadIsReverse ? 2096 : 8443)),
                'serverName' => $downloadSni ?: $downloadAddress,
            ];

            if ($downloadIsReverse && $downloadCertPem) {
                $certs = array_filter(explode('-----END CERTIFICATE-----', $downloadCertPem));
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
        // TLS & Security Settings from TLS Profile or Host Direct fields
        $security = $tlsSettings['security'] ?? $host->security ?: ($host->is_cdn ? 'tls' : 'tls');
        $sni = $tlsSettings['sni'] ?? $host->sni ?: $host->address;
        $alpn = $tlsSettings['alpn'] ?? $host->alpn ?: 'h2';
        $insecure = isset($tlsSettings['insecure']) ? (bool)$tlsSettings['insecure'] : (bool)$host->insecure;
        $pcs = $tlsSettings['pcs'] ?? $host->pcs;

        $params = [
            'encryption' => 'none',
            'security' => $security,
            'sni' => $sni,
            'alpn' => $alpn,
            'insecure' => $insecure ? 1 : 0,
            'allowInsecure' => $insecure ? 1 : 0,
            'type' => 'xhttp',
            'host' => $host->host ?: $sni,
            'path' => $host->path ?: '/',
            'mode' => $host->mode ?: 'packet-up',
            'extra' => json_encode($extra),
        ];

        if ($host->flow && $host->flow !== 'none') {
            $params['flow'] = $host->flow;
        }

        if ($host->is_reverse && $pcs) {
            $params['pcs'] = $pcs;
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
