<?php

namespace App\Http\Controllers\IDN;

use App\Http\Controllers\Controller;
use App\Models\Node;
use App\Models\XrayInbound;
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

    public function toggleDnsBlocklist(Request $request)
    {
        $enabled = $request->input('enabled') === 'true';
        
        try {
            $success = \App\Facades\Technitium::setBlocklist($enabled);
            
            if ($success) {
                // Broadcast to all nodes to update their DNS config if needed
                app(\App\Services\ControlPlane\SignalDispatcher::class)->dispatch('all', 'UPDATE_DNS_POLICY', [
                    'ad_blocking' => $enabled
                ]);
                
                return back()->with('success', 'DNS Ad-blocking ' . ($enabled ? 'ENABLED' : 'DISABLED') . ' across the fleet.');
            }
        } catch (\Exception $e) {
            return back()->withErrors(['dns' => $e->getMessage()]);
        }

        return back()->withErrors(['dns' => 'Failed to update DNS policy.']);
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
}
