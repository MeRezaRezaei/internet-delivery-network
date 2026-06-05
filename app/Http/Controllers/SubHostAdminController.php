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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'port' => 'required|integer',
            'sni' => 'nullable|string|max:255',
            'host' => 'nullable|string|max:255',
            'path' => 'nullable|string|max:255',
            'mode' => 'nullable|string|max:255',
            'security' => 'nullable|string|max:255',
            'insecure' => 'nullable|boolean',
            'alpn' => 'nullable|string|max:255',
            'extra' => 'nullable|array',
            'type' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
            'is_template' => 'nullable|boolean',
            'remark_prefix' => 'nullable|string|max:255',
            'download_address' => 'nullable|string|max:255',
            'download_port' => 'nullable|integer',
            'download_sni' => 'nullable|string|max:255',
            'is_reverse' => 'nullable|boolean',
            'cert_pem' => 'nullable|string',
            'pcs' => 'nullable|string|max:255',
        ]);

        // Fix boolean values from form
        $validated['insecure'] = $request->has('insecure');
        $validated['is_active'] = $request->has('is_active');
        $validated['is_template'] = $request->has('is_template');
        $validated['is_reverse'] = $request->has('is_reverse');

        // Parse extra JSON if it comes as a string
        if ($request->filled('extra_json')) {
            $validated['extra'] = json_decode($request->extra_json, true);
        }

        SubHost::create($validated);

        return redirect('/sub/admin/dashboard')->with('success', 'Host added successfully');
    }

    public function update(Request $request, SubHost $subHost)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'port' => 'required|integer',
            'sni' => 'nullable|string|max:255',
            'host' => 'nullable|string|max:255',
            'path' => 'nullable|string|max:255',
            'mode' => 'nullable|string|max:255',
            'security' => 'nullable|string|max:255',
            'insecure' => 'nullable|boolean',
            'alpn' => 'nullable|string|max:255',
            'extra' => 'nullable|array',
            'type' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
            'is_template' => 'nullable|boolean',
            'remark_prefix' => 'nullable|string|max:255',
            'download_address' => 'nullable|string|max:255',
            'download_port' => 'nullable|integer',
            'download_sni' => 'nullable|string|max:255',
            'is_reverse' => 'nullable|boolean',
            'cert_pem' => 'nullable|string',
            'pcs' => 'nullable|string|max:255',
        ]);

        $validated['insecure'] = $request->has('insecure');
        $validated['is_active'] = $request->has('is_active');
        $validated['is_template'] = $request->has('is_template');
        $validated['is_reverse'] = $request->has('is_reverse');

        if ($request->filled('extra_json')) {
            $validated['extra'] = json_decode($request->extra_json, true);
        }

        $subHost->update($validated);

        return redirect('/sub/admin/dashboard')->with('success', 'Host updated successfully');
    }

    public function destroy(SubHost $subHost)
    {
        $subHost->delete();
        return redirect('/sub/admin/dashboard')->with('success', 'Host deleted successfully');
    }
}
