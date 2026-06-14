<?php

namespace App\Http\Controllers;

use App\Models\SubHost;
use Illuminate\Http\Request;

class HostManagerController extends Controller
{
    public function index()
    {
        return [
            'hosts' => SubHost::all(),
            'marzban_hosts' => \App\Models\Marzban\Host::where('is_disabled', 0)->get(),
        ];
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'address' => 'required|string',
            'port' => 'required|integer',
            'sni' => 'nullable|string',
            'host' => 'nullable|string',
            'path' => 'nullable|string',
            'mode' => 'nullable|string',
            'security' => 'nullable|string',
            'insecure' => 'nullable|boolean',
            'alpn' => 'nullable|string',
            'extra' => 'nullable|array',
            'type' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'is_template' => 'nullable|boolean',
            'remark_prefix' => 'nullable|string',
            'download_address' => 'nullable|string',
            'download_port' => 'nullable|integer',
            'download_sni' => 'nullable|string',
            'is_reverse' => 'nullable|boolean',
            'cert_pem' => 'nullable|string',
            'pcs' => 'nullable|string',
        ]);

        return SubHost::create($validated);
    }

    public function show(SubHost $subHost)
    {
        return $subHost;
    }

    public function update(Request $request, SubHost $subHost)
    {
        $validated = $request->validate([
            'name' => 'string',
            'address' => 'string',
            'port' => 'integer',
            'sni' => 'nullable|string',
            'host' => 'nullable|string',
            'path' => 'string',
            'mode' => 'string',
            'security' => 'string',
            'insecure' => 'boolean',
            'alpn' => 'string',
            'extra' => 'nullable|array',
            'type' => 'string',
            'is_active' => 'boolean',
            'is_template' => 'boolean',
            'remark_prefix' => 'nullable|string',
            'download_address' => 'nullable|string',
            'download_port' => 'nullable|integer',
            'download_sni' => 'nullable|string',
            'is_reverse' => 'nullable|boolean',
            'cert_pem' => 'nullable|string',
            'pcs' => 'nullable|string',
        ]);

        $subHost->update($validated);
        return $subHost;
    }

    public function destroy(SubHost $subHost)
    {
        $subHost->delete();
        return response()->noContent();
    }
}
