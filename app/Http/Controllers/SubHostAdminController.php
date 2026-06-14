<?php

namespace App\Http\Controllers;

use App\Models\SubHost;
use App\Models\SubProfile;
use Illuminate\Http\Request;

class SubHostAdminController extends Controller
{
    public function index()
    {
        $hosts = SubHost::with(['tlsProfile', 'xhttpProfile', 'xmuxProfile', 'downloadHost'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        $profiles = SubProfile::orderBy('type')->orderBy('name')->get();
        
        $tlsProfiles = $profiles->where('type', 'tls')->values();
        $xhttpProfiles = $profiles->where('type', 'xhttp')->values();
        $xmuxProfiles = $profiles->where('type', 'xmux')->values();
        
        return view('admin.dashboard', compact('hosts', 'tlsProfiles', 'xhttpProfiles', 'xmuxProfiles', 'profiles'));
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

    /**
     * Store a new configuration profile.
     */
    public function storeProfile(Request $request)
    {
        $validated = $this->validateProfileRequest($request);
        SubProfile::create($validated);
        return redirect('/sub/admin/dashboard')->with('success', 'Profile added successfully');
    }

    /**
     * Update an existing configuration profile.
     */
    public function updateProfile(Request $request, SubProfile $subProfile)
    {
        $validated = $this->validateProfileRequest($request);
        $subProfile->update($validated);
        return redirect('/sub/admin/dashboard')->with('success', 'Profile updated successfully');
    }

    /**
     * Destroy a configuration profile.
     */
    public function destroyProfile(SubProfile $subProfile)
    {
        $subProfile->delete();
        return redirect('/sub/admin/dashboard')->with('success', 'Profile deleted successfully');
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
            
            // New foreign profile IDs and relationships
            'tls_profile_id' => 'nullable|integer|exists:sub_profiles,id',
            'xhttp_profile_id' => 'nullable|integer|exists:sub_profiles,id',
            'xmux_profile_id' => 'nullable|integer|exists:sub_profiles,id',
            'download_host_id' => 'nullable|integer|exists:sub_hosts,id',
        ];

        $validated = $request->validate($rules);

        // Booleans from checkboxes
        $validated['insecure'] = $request->has('insecure');
        $validated['is_active'] = $request->has('is_active');
        $validated['is_template'] = $request->has('is_template');
        $validated['is_reverse'] = $request->has('is_reverse');
        $validated['is_cdn'] = $request->has('is_cdn');
        $validated['no_grpc_header'] = $request->has('no_grpc_header');
        $validated['is_dev'] = $request->has('is_dev');

        // Manual JSON extra if provided
        if ($request->filled('extra_json')) {
            $validated['extra'] = json_decode($request->extra_json, true);
        }

        return $validated;
    }

    protected function validateProfileRequest(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:tls,xhttp,xmux',
        ]);

        $settings = [];

        if ($request->input('type') === 'tls') {
            $settings = [
                'security' => $request->input('profile_security', 'tls'),
                'sni' => $request->input('profile_sni'),
                'alpn' => $request->input('profile_alpn', 'h2'),
                'insecure' => $request->has('profile_insecure'),
                'pcs' => $request->input('profile_pcs'),
                'cert_pem' => $request->input('profile_cert_pem'),
            ];
        } elseif ($request->input('type') === 'xhttp') {
            $settings = [
                'padding' => $request->input('profile_padding', '100-500'),
                'no_grpc_header' => $request->has('profile_no_grpc_header'),
                'sc_max_each_post_bytes' => $request->input('profile_sc_max_each_post_bytes'),
                'sc_min_posts_interval_ms' => $request->input('profile_sc_min_posts_interval_ms'),
            ];
        } elseif ($request->input('type') === 'xmux') {
            $settings = [
                'xmux_max_concurrency' => $request->input('profile_xmux_max_concurrency'),
                'xmux_max_connections' => $request->input('profile_xmux_max_connections'),
                'xmux_c_max_reuse_times' => $request->input('profile_xmux_c_max_reuse_times'),
                'xmux_h_max_request_times' => $request->input('profile_xmux_h_max_request_times'),
                'xmux_h_max_reusable_secs' => $request->input('profile_xmux_h_max_reusable_secs'),
            ];
        }

        return [
            'name' => $request->input('name'),
            'type' => $request->input('type'),
            'settings' => $settings,
        ];
    }
}
