<?php

namespace App\Http\Controllers;

use App\Models\Marzban\User;
use App\Models\Marzban\Jwt;
use App\Models\Marzban\Host as MarzbanHost;
use App\Models\SubHost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MarzbanSubscriptionController extends Controller
{
    public function show(Request $request, $token)
    {
        Log::info("Sub request: " . $token . " UA: " . $request->header('User-Agent'));

        if ($token === 'admin') {
            return redirect('/sub/admin');
        }

        $username = $this->extractUsername($token);

        $user = User::where('username', $username)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if ($user->status !== 'active' && $user->status !== 'on_hold') {
            return response()->json(['error' => 'Account is ' . $user->status], 403);
        }

        $vlessProxy = $user->proxies()->where('type', 'VLESS')->first();
        if (!$vlessProxy) {
            return response()->json(['error' => 'VLESS proxy not found for user'], 404);
        }

        $uuid = $vlessProxy->settings['id'] ?? null;
        if (!$uuid) {
            return response()->json(['error' => 'UUID not found'], 500);
        }

        $uris = $this->generateUris($uuid, $user);

        // Browser Detection
        $userAgent = $request->header('User-Agent', '');
        $accept = $request->header('Accept', '');
        
        $isBrowser = str_contains($accept, 'text/html') || 
                     (preg_match('/Mozilla|Chrome|Safari|Opera|Edge/i', $userAgent) && 
                      !preg_match('/v2ray|Clash|Streisand|Shadowrocket|Quantumult|v2fly/i', $userAgent));

        if ($isBrowser && !$request->has('raw')) {
            return view('sub.show', compact('user', 'uris'));
        }

        $content = base64_encode(implode("\n", $uris));

        return response($content)
            ->header('Content-Type', 'text/plain; charset=utf-8')
            ->header('Profile-Title', 'IDN Custom Sub')
            ->header('Profile-Update-Interval', '12')
            ->header('Subscription-Userinfo', $this->getUserInfo($user));
    }

    protected function getActiveInboundTags()
    {
        $configFile = '/opt/Marzban/xray_config.json';
        if (!file_exists($configFile)) {
            Log::warning("xray_config.json not found at $configFile");
            return [];
        }

        $config = json_decode(file_get_contents($configFile), true);
        if (!$config || !isset($config['inbounds']) || !is_array($config['inbounds'])) {
            return [];
        }

        $tags = [];
        foreach ($config['inbounds'] as $inbound) {
            if (isset($inbound['tag'])) {
                $tags[] = $inbound['tag'];
            }
        }
        return $tags;
    }

    protected function generateUris($uuid, $user)
    {
        $uris = [];

        // 1. Get our custom Host Overrides (Highest priority, full control)
        $customHosts = SubHost::where('is_active', true)->get();
        foreach ($customHosts as $host) {
            $uris[] = $this->buildVlessUri($uuid, $host);
        }

        // 2. Get active hosts from Marzban (Filtered by actually active inbound tags)
        try {
            $activeTags = $this->getActiveInboundTags();
            
            if (!empty($activeTags)) {
                $marzbanHosts = MarzbanHost::where('is_disabled', 0)
                    ->whereIn('inbound_tag', $activeTags)
                    ->get();

                foreach ($marzbanHosts as $mHost) {
                    // Skip placeholder addresses
                    if (str_contains($mHost->address, '{SERVER_IP}') || str_contains($mHost->address, '{JALALI')) {
                        continue;
                    }

                    // Avoid duplicates by address if already defined in customHosts
                    if ($customHosts->where('address', $mHost->address)->first()) {
                        continue;
                    }

                    // For remaining Marzban hosts, optionally wrap them with a default template 
                    // if they are meant to be XHTTP, or just generate as they are.
                    // The user mentioned focusing specifically on VLESS/XHTTP.
                    $uris[] = $this->buildVlessUriFromMarzban($uuid, $mHost);
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to fetch Marzban hosts: " . $e->getMessage());
        }

        return array_values(array_unique($uris));
    }

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

    protected function buildVlessUri($uuid, $host)
    {
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
        ];
        
        if ($host->extra && !empty($host->extra)) {
            $params['extra'] = json_encode($host->extra);
        }

        $queryString = http_build_query($params);
        $remark = urlencode(($host->remark_prefix ?? "IDN") . " - " . $host->name);

        return "vless://{$uuid}@{$host->address}:{$host->port}?{$queryString}#{$remark}";
    }

    protected function buildVlessUriFromMarzban($uuid, $mHost)
    {
        $params = [
            'encryption' => 'none',
            'security' => $mHost->security ?: 'tls',
            'sni' => $mHost->sni ?: $mHost->address,
            'alpn' => $mHost->alpn ?: 'h2',
            'insecure' => $mHost->allowinsecure ? 1 : 0,
            'allowInsecure' => $mHost->allowinsecure ? 1 : 0,
            'type' => 'xhttp',
            'host' => $mHost->host ?: ($mHost->sni ?: $mHost->address),
            'path' => $mHost->path ?: '/',
            'mode' => 'packet-up',
        ];
        
        // Use a generic template if available
        $template = SubHost::where('type', 'direct')->first();
        if ($template && $template->extra) {
            $params['extra'] = json_encode($template->extra);
        } else {
            // Default splitHTTP template
            $params['extra'] = json_encode([
                'headers' => ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36'],
                'xPaddingBytes' => '100-500',
                'noGRPCHeader' => false,
                'scMaxEachPostBytes' => '1000000-2000000',
                'scMinPostsIntervalMs' => '50-150',
                'xmux' => ['maxConcurrency' => '8-16', 'maxConnections' => 0, 'cMaxReuseTimes' => '10-20', 'hMaxRequestTimes' => '100-300', 'hMaxReusableSecs' => '120-240', 'hKeepAlivePeriod' => 0]
            ]);
        }

        $queryString = http_build_query($params);
        $remark = urlencode($mHost->remark ?: $mHost->address);

        return "vless://{$uuid}@{$mHost->address}:" . ($mHost->port ?: 443) . "?{$queryString}#{$remark}";
    }

    protected function getUserInfo($user)
    {
        $used = $user->used_traffic ?? 0;
        $total = $user->data_limit ?? 0;
        $expire = $user->expire ? $user->expire : 0;

        return "upload=0; download={$used}; total={$total}; expire={$expire}";
    }
}
