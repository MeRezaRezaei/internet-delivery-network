<?php

namespace App\Http\Controllers\IDN;

use App\Http\Controllers\Controller;
use App\Models\IDN\Node;
use App\Models\IDN\Tunnel;
use App\Services\ControlPlane\NodeMonitorService;
use Illuminate\Http\Request;
use App\Events\LogsUpdated;
use App\Events\TrafficUpdated;

class DashboardController extends Controller
{
    protected NodeMonitorService ;

    public function __construct(NodeMonitorService )
    {
        ->monitor = ;
    }

    public function index()
    {
         = Node::all();
         = Tunnel::all();
         = ->monitor->getFleetStatus();

        return view('idn.dashboard', compact('nodes', 'tunnels', 'fleetStatus'));
    }

    public function routing(Request , \App\Services\ControlPlane\RoutingEngine )
    {
         = ->generateDynamicRules();
        return response()->json();
    }

    public function logs(Request )
    {
         = ->input('last_id', '0');
        
         = \Illuminate\Support\Facades\Redis::executeRaw([
            'XREAD', 'COUNT', '50', 'BLOCK', '100', 'STREAMS', \App\Services\ControlPlane\LogDispatcher::LOG_STREAM_KEY, 
        ]);

         = [];
         = ;

        if (!empty()) {
            foreach ( as ) {
                 = [1] ?? [];
                foreach ( as ) {
                     = [0];
                     = [1];
                     = ->parseFields();
                    [] = [
                        'id' => ,
                        'timestamp' => ['timestamp'] ?? now()->toIso8601String(),
                        'node' => ['node'] ?? 'unknown',
                        'level' => ['level'] ?? 'INFO',
                        'message' => ['message'] ?? '',
                    ];
                }
            }
        }

        if (count() > 0) {
            broadcast(new LogsUpdated(, ));
        }

        return response()->json([
            'status' => 'broadcasted',
            'count' => count(),
            'last_id' => 
        ]);
    }

    protected function parseFields(array ): array
    {
         = [];
        for ( = 0;  < count();  += 2) {
            [[]] = [+1];
        }
        return ;
    }

    public function traffic(Request )
    {
        // Mock traffic data for Grafana-like visualization
         = Tunnel::count();
        // Base traffic based on tunnels
         =  > 0 ? ( * 15) : 2; 
        
         = rand(,  + 30) + (rand(0, 99) / 100);
         = rand(,  + 20) + (rand(0, 99) / 100);
        
         = [
            'timestamp' => now()->toIso8601String(),
            'rx_mbps' => round(, 2),
            'tx_mbps' => round(, 2),
        ];

        broadcast(new TrafficUpdated());

        return response()->json([
            'status' => 'broadcasted',
            'data' => 
        ]);
    }
}
