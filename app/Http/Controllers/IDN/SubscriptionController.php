<?php

namespace App\Http\Controllers\IDN;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    /**
     * Display the specified subscription.
     *
     * @param string $uuid
     * @return \Illuminate\Http\Response
     */
    public function show($uuid)
    {
        $subscription = Subscription::where('id', $uuid)->first();

        if (!$subscription) {
            return response()->json(['error' => 'Subscription not found'], 404);
        }

        $package = $subscription->package;
        
        $uris = [];

        // Mock URI generation based on package level
        if ($package && $package->level === 'full_power') {
            $uris = [
                'vless://premium-uuid-1@premium.node.1:443?encryption=none&security=tls&type=ws#Premium-Node-1',
                'vless://premium-uuid-2@premium.node.2:443?encryption=none&security=tls&type=ws#Premium-Node-2',
            ];
        } else {
            // basic
            $uris = [
                'vless://basic-uuid-1@basic.node.1:443?encryption=none&security=tls&type=ws#Basic-Node-1',
                'vless://basic-uuid-2@basic.node.2:443?encryption=none&security=tls&type=ws#Basic-Node-2',
            ];
        }

        $base64Content = base64_encode(implode("\n", $uris));

        return response($base64Content)
            ->header('Content-Type', 'text/plain')
            ->header('Profile-Update-Interval', '24')
            ->header('Profile-Title', 'IDN Subscription');
    }
}

