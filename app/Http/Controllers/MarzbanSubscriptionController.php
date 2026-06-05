<?php

namespace App\Http\Controllers;

use App\Models\Marzban\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MarzbanSubscriptionController extends Controller
{
    /**
     * Handle the subscription request.
     *
     * @param string $token
     * @return \Illuminate\Http\Response
     */
    public function show($token)
    {
        // For Marzban, the sub URL is often /sub/{token}
        // If token is base64 encoded, decode it to check if it's a username
        $username = $token;
        if (base64_decode($token, true)) {
            $decoded = base64_decode($token);
            // Sometimes it's "username,timestamp,sig"
            $parts = explode(',', $decoded);
            $username = $parts[0];
        }

        $user = User::where('username', $username)->first();

        if (!$user) {
            // Try searching by token directly if it's stored somewhere else (future proof)
            return response()->json(['error' => 'User not found'], 404);
        }

        if ($user->status !== 'active') {
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

        $templates = config('marzban_sub.templates');
        $uris = [];

        foreach ($templates as $key => $tpl) {
            $uris[] = $this->buildVlessUri($uuid, $tpl, $key);
        }

        $content = base64_encode(implode("\n", $uris));

        return response($content)
            ->header('Content-Type', 'text/plain; charset=utf-8')
            ->header('Profile-Title', 'IDN Custom Sub')
            ->header('Profile-Update-Interval', '12')
            ->header('Subscription-Userinfo', $this->getUserInfo($user));
    }

    protected function buildVlessUri($uuid, $tpl, $key)
    {
        $params = [
            'encryption' => 'none',
            'security' => $tpl['security'],
            'sni' => $tpl['sni'],
            'alpn' => 'h2',
            'insecure' => $tpl['insecure'],
            'allowInsecure' => $tpl['insecure'],
            'type' => 'xhttp',
            'host' => $tpl['host'],
            'path' => $tpl['path'],
            'mode' => $tpl['mode'],
            'extra' => json_encode($tpl['extra']),
        ];

        $queryString = http_build_query($params);
        $remark = urlencode("IDN-" . strtoupper(str_replace('_', '-', $key)));

        return "vless://{$uuid}@{$tpl['address']}:{$tpl['port']}?{$queryString}#{$remark}";
    }

    protected function getUserInfo($user)
    {
        $used = $user->used_traffic ?? 0;
        $total = $user->data_limit ?? 0;
        $expire = $user->expire ? $user->expire : 0;

        return "upload=0; download={$used}; total={$total}; expire={$expire}";
    }
}
