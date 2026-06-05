<?php

namespace App\Http\Controllers;

use App\Models\SubHost;
use Illuminate\Http\Request;

class SubHostAdminController extends Controller
{
    public function index()
    {
        $hosts = SubHost::orderBy('created_at', 'desc')->get();
        return view('admin.dashboard', compact('hosts'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);
        SubHost::create($validated);
        return redirect('/sub/admin/dashboard')->with('success', 'Host added successfully');
    }

    public function update(Request $request, SubHost $subHost)
    {
        $validated = $this->validateRequest($request);
        $subHost->update($validated);
        return redirect('/sub/admin/dashboard')->with('success', 'Host updated successfully');
    }

    public function destroy(SubHost $subHost)
    {
        $subHost->delete();
        return redirect('/sub/admin/dashboard')->with('success', 'Host deleted successfully');
    }

    protected function validateRequest(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'port' => 'required|integer',
            'sni' => 'nullable|string|max:255',
            'host' => 'nullable|string|max:255',
            'path' => 'nullable|string|max:255',
            'mode' => 'nullable|string|max:255',
            'security' => 'nullable|string|max:255',
            'alpn' => 'nullable|string|max:255',
            'type' => 'required|string|max:255',
            'remark_prefix' => 'nullable|string|max:255',
            'download_address' => 'nullable|string|max:255',
            'download_port' => 'nullable|integer',
            'download_sni' => 'nullable|string|max:255',
            'cert_pem' => 'nullable|string',
            'pcs' => 'nullable|string|max:255',
            'flow' => 'nullable|string|max:255',
            'padding' => 'nullable|string',
            'sc_max_each_post_bytes' => 'nullable|string',
            'sc_min_posts_interval_ms' => 'nullable|string',
            'xmux_max_concurrency' => 'nullable|integer',
            'xmux_max_connections' => 'nullable|integer',
            'xmux_c_max_reuse_times' => 'nullable|string',
            'xmux_h_max_request_times' => 'nullable|string',
            'xmux_h_max_reusable_secs' => 'nullable|string',
        ];

        $validated = $request->validate($rules);

        // Booleans from checkboxes
        $validated['insecure'] = $request->has('insecure');
        $validated['is_active'] = $request->has('is_active');
        $validated['is_template'] = $request->has('is_template');
        $validated['is_reverse'] = $request->has('is_reverse');
        $validated['is_cdn'] = $request->has('is_cdn');
        $validated['no_grpc_header'] = $request->has('no_grpc_header');

        // Manual JSON extra if provided
        if ($request->filled('extra_json')) {
            $validated['extra'] = json_decode($request->extra_json, true);
        }

        return $validated;
    }
}
