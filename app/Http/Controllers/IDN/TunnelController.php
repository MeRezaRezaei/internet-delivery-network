<?php

namespace App\Http\Controllers\IDN;

use App\Http\Controllers\Controller;
use App\Models\IDN\Node;
use App\Models\IDN\Tunnel;
use App\Services\ControlPlane\SignalDispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TunnelController extends Controller
{
    protected SignalDispatcher $dispatcher;

    public function __construct(SignalDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function store(Request $request)
    {
        // Pre-process JSON string to array
        if (is_string($request->input('config'))) {
            try {
                $request->merge(['config' => json_decode($request->input('config'), true, 512, JSON_THROW_ON_ERROR)]);
            } catch (\Exception $e) {
                return back()->withErrors(['config' => 'Invalid JSON format.'])->withInput();
            }
        }

        $validated = $request->validate([
            'source_node_id' => 'required|exists:idn_nodes,id',
            'target_node_id' => 'required|exists:idn_nodes,id',
            'tag' => 'required|unique:idn_tunnels,tag',
            'port' => 'required|integer',
            'protocol' => 'required|string',
            'network' => 'nullable|string',
            'config' => 'required|array',
        ]);

        return DB::transaction(function () use ($validated) {
            $tunnel = Tunnel::create([
                'source_node_id' => $validated['source_node_id'],
                'target_node_id' => $validated['target_node_id'],
                'tag' => $validated['tag'],
                'port' => $validated['port'],
                'protocol' => $validated['protocol'],
            ]);

            $payload = $validated['config'] + [
                'tag' => $validated['tag'], 
                'port' => $validated['port'], 
                'protocol' => $validated['protocol']
            ];

            if (!empty($validated['network'])) {
                if (!isset($payload['streamSettings'])) {
                    $payload['streamSettings'] = [];
                }
                
                if ($validated['network'] === 'tcp-tls') {
                    $payload['streamSettings']['network'] = 'tcp';
                    $payload['streamSettings']['security'] = 'tls';
                } else {
                    $payload['streamSettings']['network'] = $validated['network'];
                }
            }

            // Dispatch signal to the target node
            $this->dispatcher->dispatch(
                Node::find($validated['target_node_id'])->name,
                'ADD_INBOUND',
                $payload
            );

            return redirect()->route('idn.dashboard')->with('success', 'Tunnel created and signal dispatched.');
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
}
