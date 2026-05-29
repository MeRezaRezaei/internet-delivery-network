<?php

namespace App\Http\Controllers\IDN;

use App\Http\Controllers\Controller;
use App\Models\IDN\Node;
use App\Models\IDN\Tunnel;
use App\Services\ControlPlane\NodeMonitorService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected NodeMonitorService $monitor;

    public function __construct(NodeMonitorService $monitor)
    {
        $this->monitor = $monitor;
    }

    public function index()
    {
        $nodes = Node::all();
        $tunnels = Tunnel::all();
        $fleetStatus = $this->monitor->getFleetStatus();

        return view('idn.dashboard', compact('nodes', 'tunnels', 'fleetStatus'));
    }

    public function routing(Request $request, AppServicesControlPlaneRoutingEngine $engine)
    {
        $data = $engine->generateDynamicRules();
        return response()->json($data);
    }

    public function logs(Request $request)
    {
        $lastId = $request->input('last_id', '0');
        
        $raw = \Illuminate\Support\Facades\Redis::executeRaw([
            'XREAD', 'COUNT', '50', 'BLOCK', '100', 'STREAMS', \App\Services\ControlPlane\LogDispatcher::LOG_STREAM_KEY, $lastId
        ]);

        $logs = [];
        $newLastId = $lastId;

        if (!empty($raw)) {
            foreach ($raw as $streamData) {
                $messages = $streamData[1] ?? [];
                foreach ($messages as $msg) {
                    $newLastId = $msg[0];
                    $fields = $msg[1];
                    $data = $this->parseFields($fields);
                    $logs[] = [
                        'id' => $newLastId,
                        'timestamp' => $data['timestamp'] ?? now()->toIso8601String(),
                        'node' => $data['node'] ?? 'unknown',
                        'level' => $data['level'] ?? 'INFO',
                        'message' => $data['message'] ?? '',
                    ];
                }
            }
        }

        return response()->json([
            'logs' => $logs,
            'last_id' => $newLastId
        ]);
    }

    protected function parseFields(array $fields): array
    {
        $data = [];
        for ($i = 0; $i < count($fields); $i += 2) {
            $data[$fields[$i]] = $fields[$i+1];
        }
        return $data;
    }

    public function traffic(Request $request)
    {
        // Mock traffic data for Grafana-like visualization
        $tunnelsCount = Tunnel::count();
        // Base traffic based on tunnels
        $baseTraffic = $tunnelsCount > 0 ? ($tunnelsCount * 15) : 2; 
        
        $rx = rand($baseTraffic, $baseTraffic + 30) + (rand(0, 99) / 100);
        $tx = rand($baseTraffic, $baseTraffic + 20) + (rand(0, 99) / 100);
        
        return response()->json([
            'timestamp' => now()->toIso8601String(),
            'rx_mbps' => round($rx, 2),
            'tx_mbps' => round($tx, 2),
        ]);
    }
}
