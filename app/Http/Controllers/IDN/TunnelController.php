<?php

namespace App\Http\Controllers\IDN;

use App\Http\Controllers\Controller;
use App\Models\Node;
use App\Models\Tunnel;
use App\Models\XrayInbound;
use App\Services\ControlPlane\SignalDispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TunnelController extends Controller
{
    protected SignalDispatcher $dispatcher;
    protected \App\Services\Xray\Missions\PortalMission $portalMission;

    public function __construct(SignalDispatcher $dispatcher, \App\Services\Xray\Missions\PortalMission $portalMission)
    {
        $this->dispatcher = $dispatcher;
        $this->portalMission = $portalMission;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'source_node_id' => 'required|exists:idn_nodes,id',
            'target_node_id' => 'required|exists:idn_nodes,id',
            'tag' => 'required|unique:idn_tunnels,tag',
            'port' => 'required|integer',
            'protocol' => 'required|string',
            'transport' => 'nullable|string',
            'transport_params' => 'nullable|array',
            'reality_params' => 'nullable|array',
        ]);

        return DB::transaction(function () use ($validated) {
            $targetNode = Node::findOrFail($validated['target_node_id']);
            
            // 1. Provision via PortalMission
            $inbound = $this->portalMission->setup(
                $targetNode,
                $validated['port'],
                $validated['tag'],
                $validated['reality_params'] ?? [],
                $validated['transport'] ?? 'tcp',
                $validated['transport_params'] ?? []
            );

            // 2. Create Outbound on Source Node (simplified for portal)
            $sourceNode = Node::findOrFail($validated['source_node_id']);
            $outbound = XrayOutbound::create([
                'node_id' => $sourceNode->id,
                'tag' => "out-to-{$validated['tag']}",
            ]);

            // 3. Link everything in Tunnel record
            $tunnel = Tunnel::create([
                'source_node_id' => $sourceNode->id,
                'target_node_id' => $targetNode->id,
                'tag' => $validated['tag'],
                'inbound_id' => $inbound->id,
                'outbound_id' => $outbound->id,
                'port' => $validated['port'],
                'protocol' => $validated['protocol'],
                'config' => [
                    'transport' => $validated['transport'] ?? 'tcp',
                    'transport_params' => $validated['transport_params'] ?? [],
                    'reality_params' => $validated['reality_params'] ?? [],
                ],
                'is_active' => true,
            ]);

            // 4. Dispatch signals
            $this->dispatcher->dispatch($targetNode->name, 'ADD_INBOUND', $tunnel->config + [
                'tag' => $tunnel->tag,
                'port' => $tunnel->port,
                'protocol' => $tunnel->protocol,
            ]);
            
            $this->dispatcher->dispatch($sourceNode->name, 'ADD_OUTBOUND', [
                'tag' => $outbound->tag,
                'protocol' => $tunnel->protocol,
                'address' => $targetNode->ip ?? $targetNode->hostname,
                'port' => $tunnel->port,
            ] + $tunnel->config);

            return response()->json(['status' => 'success', 'tunnel' => $tunnel]);
        });
    }

    public function destroy(Tunnel $tunnel)
    {
        $nodeName = $tunnel->targetNode->name;
        $tag = $tunnel->tag;

        return DB::transaction(function () use ($tunnel, $nodeName, $tag) {
            $tunnel->delete();

            // Dispatch remove signal
            $this->dispatcher->dispatch($nodeName, 'REMOVE_INBOUND', ['tag' => $tag]);

            return redirect()->route('idn.dashboard')->with('success', 'Tunnel deleted and remove signal dispatched.');
        });
    }

    public function verify(Tunnel $tunnel, \App\Services\Xray\XrayConfigRenderer $renderer, \App\Services\Xray\XrayValidator $validator)
    {
        $tunnel->load(['sourceNode', 'targetNode']);

        $sourceConfig = $renderer->render($tunnel->sourceNode);
        $sourceResult = $validator->validate($sourceConfig);

        $targetConfig = $renderer->render($tunnel->targetNode);
        $targetResult = $validator->validate($targetConfig);

        $pingResult = false;
        if ($tunnel->targetNode->ip) {
            exec("ping -c 1 -W 2 {$tunnel->targetNode->ip} 2>&1", $output, $resultCode);
            $pingResult = ($resultCode === 0);
        }

        return response()->json([
            'status' => 'success',
            'results' => [
                'source' => $sourceResult,
                'target' => $targetResult,
                'reachability' => $pingResult,
            ]
        ]);
    }
}
