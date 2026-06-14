<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - IDN Sub Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body { 
            background-color: #0b0f19; 
            color: #f1f5f9; 
            font-family: 'Outfit', sans-serif; 
            background-image: radial-gradient(circle at 10% 20%, rgba(17, 24, 39, 0.3) 0%, rgba(9, 12, 22, 0.8) 90%);
        }
        .navbar { 
            background: rgba(30, 41, 59, 0.4); 
            backdrop-filter: blur(12px); 
            border-bottom: 1px solid rgba(255, 255, 255, 0.05); 
        }
        .nav-tabs {
            border-bottom: 2px solid rgba(255, 255, 255, 0.05);
        }
        .nav-tabs .nav-link {
            color: #94a3b8;
            border: none;
            font-weight: 600;
            padding: 1rem 1.5rem;
            transition: all 0.2s ease;
        }
        .nav-tabs .nav-link:hover {
            color: #f1f5f9;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        }
        .nav-tabs .nav-link.active {
            color: #3b82f6;
            background: transparent;
            border: none;
            border-bottom: 2px solid #3b82f6;
        }
        .card { 
            background: rgba(30, 41, 59, 0.4); 
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05); 
            border-radius: 1rem; 
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);
        }
        .table { color: #e2e8f0; }
        .table-hover tbody tr {
            transition: background-color 0.15s ease;
        }
        .table-hover tbody tr:hover { 
            background-color: rgba(255, 255, 255, 0.03); 
            color: #f8fafc; 
        }
        .table-dark {
            --bs-table-bg: #131b2e;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .modal-content { 
            background-color: #0f172a; 
            color: #f1f5f9; 
            border: 1px solid rgba(255, 255, 255, 0.1); 
            border-radius: 1.25rem;
        }
        .form-control, .form-select { 
            background-color: #0b0f19; 
            border-color: rgba(255, 255, 255, 0.08); 
            color: #f1f5f9; 
            border-radius: 0.5rem;
        }
        .form-control:focus, .form-select:focus { 
            background-color: #0b0f19; 
            border-color: #3b82f6; 
            color: #f1f5f9; 
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2); 
        }
        .badge-active { background-color: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2); }
        .badge-inactive { background-color: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2); }
        .badge-dev { background-color: rgba(245, 158, 11, 0.1); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.2); }
        .badge-profile { background-color: rgba(99, 102, 241, 0.1); color: #818cf8; border: 1px solid rgba(99, 102, 241, 0.2); }
        .btn-close { filter: invert(1); }
        .section-title { 
            font-size: 0.75rem; 
            font-weight: 800; 
            text-transform: uppercase; 
            letter-spacing: 0.15em; 
            color: #94a3b8; 
            margin-bottom: 1.25rem; 
            margin-top: 1.75rem; 
            border-bottom: 1px solid rgba(255, 255, 255, 0.05); 
            padding-bottom: 0.5rem; 
        }
        .font-monospace {
            font-family: 'JetBrains Mono', monospace;
        }
    </style>
</head>
<body class="pb-5">
    <nav class="navbar navbar-expand-lg navbar-dark mb-4 sticky-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="#">
                <span class="bg-primary text-white px-2.5 py-1 rounded-3 fw-black" style="font-size: 0.9rem;">IDN</span>
                <span>Sub-Service Admin</span>
            </a>
            <div class="d-flex">
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm px-3 rounded-pill">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible border-0 shadow fade show" style="background: rgba(16, 185, 129, 0.15); color: #34d399;" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="hosts-tab" data-bs-toggle="tab" data-bs-target="#hosts-content" type="button" role="tab" aria-controls="hosts-content" aria-selected="true">
                    Hosts Management
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="profiles-tab" data-bs-toggle="tab" data-bs-target="#profiles-content" type="button" role="tab" aria-controls="profiles-content" aria-selected="false">
                    Settings Profiles
                </button>
            </li>
        </ul>

        <div class="tab-content" id="adminTabsContent">
            <!-- TAB 1: HOSTS -->
            <div class="tab-pane fade show active" id="hosts-content" role="tabpanel" aria-labelledby="hosts-tab">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="fw-bold mb-0">Active SubHosts</h3>
                    <button class="btn btn-primary px-4 rounded-pill fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#hostModal" onclick="resetForm()">+ Add New Host</button>
                </div>

                <div class="card overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Name & Info</th>
                                    <th>Type & Mode</th>
                                    <th>Main Connection</th>
                                    <th>Assigned Profiles</th>
                                    <th>Download Config</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($hosts as $host)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-white">{{ $host->name }}</div>
                                        <div class="text-secondary small font-monospace">{{ $host->remark_prefix }}</div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <div>
                                                <span class="badge {{ $host->type == 'reverse' ? 'bg-purple text-purple-200' : 'bg-primary text-primary-200' }} text-uppercase" style="font-size: 0.65rem;">
                                                    {{ $host->type }}
                                                </span>
                                                @if($host->is_cdn)
                                                <span class="badge bg-info text-dark" style="font-size: 0.65rem;">CDN</span>
                                                @endif
                                            </div>
                                            @if($host->is_dev)
                                            <div>
                                                <span class="badge badge-dev uppercase" style="font-size: 0.65rem;">Development Node</span>
                                            </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-monospace text-slate-300">{{ $host->address }}:{{ $host->port }}</div>
                                        @if($host->path && $host->path !== '/')
                                        <div class="text-secondary small font-monospace">Path: {{ $host->path }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            @if($host->tlsProfile)
                                                <span class="badge badge-profile small">TLS: {{ $host->tlsProfile->name }}</span>
                                            @endif
                                            @if($host->xhttpProfile)
                                                <span class="badge badge-profile small">XHTTP: {{ $host->xhttpProfile->name }}</span>
                                            @endif
                                            @if($host->xmuxProfile)
                                                <span class="badge badge-profile small">XMUX: {{ $host->xmuxProfile->name }}</span>
                                            @endif
                                            @if(!$host->tls_profile_id && !$host->xhttp_profile_id && !$host->xmux_profile_id)
                                                <span class="text-secondary small">Direct Configuration</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($host->downloadHost)
                                            <div class="text-info small fw-bold">Dynamic Link:</div>
                                            <div class="text-slate-300 small font-monospace">{{ $host->downloadHost->name }} ({{ $host->downloadHost->address }})</div>
                                        @elseif($host->download_address)
                                            <div class="text-info small">Static:</div>
                                            <div class="text-slate-300 small font-monospace">{{ $host->download_address }}:{{ $host->download_port ?? $host->port }}</div>
                                        @else
                                            <span class="text-secondary small">Single-Domain</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <span class="badge {{ $host->is_active ? 'badge-active' : 'badge-inactive' }} text-center" style="width: 75px;">
                                                {{ $host->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                            @if($host->is_template)
                                            <span class="badge bg-warning text-dark text-center" style="width: 75px;">Template</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button class="btn btn-sm btn-outline-light px-3" onclick="editHost({{ json_encode($host) }})" data-bs-toggle="modal" data-bs-target="#hostModal">Edit</button>
                                            <form action="{{ route('admin.hosts.destroy', $host->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this host?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 2: PROFILES -->
            <div class="tab-pane fade" id="profiles-content" role="tabpanel" aria-labelledby="profiles-tab">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="fw-bold mb-0">Configuration Profiles</h3>
                    <button class="btn btn-primary px-4 rounded-pill fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#profileModal" onclick="resetProfileForm()">+ Add New Profile</button>
                </div>

                <div class="card overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Profile Name</th>
                                    <th>Type</th>
                                    <th>Settings Overview</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($profiles as $profile)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-white">{{ $profile->name }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary text-uppercase" style="font-size: 0.7rem;">
                                            {{ $profile->type }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small text-slate-300 font-monospace">
                                            @if($profile->type === 'tls')
                                                Security: {{ $profile->settings['security'] ?? 'tls' }} | 
                                                SNI: {{ $profile->settings['sni'] ?? 'None' }} | 
                                                ALPN: {{ $profile->settings['alpn'] ?? 'h2' }} | 
                                                Insecure: {{ !empty($profile->settings['insecure']) ? 'True' : 'False' }}
                                                @if(!empty($profile->settings['pcs']))
                                                    <br><span class="text-info">PCS: {{ substr($profile->settings['pcs'], 0, 15) }}...</span>
                                                @endif
                                            @elseif($profile->type === 'xhttp')
                                                Padding: {{ $profile->settings['padding'] ?? '100-500' }} | 
                                                no_grpc: {{ !empty($profile->settings['no_grpc_header']) ? 'True' : 'False' }} | 
                                                Post size: {{ $profile->settings['sc_max_each_post_bytes'] ?? 'Default' }} | 
                                                Interval: {{ $profile->settings['sc_min_posts_interval_ms'] ?? 'Default' }}ms
                                            @elseif($profile->type === 'xmux')
                                                Concurrency: {{ $profile->settings['xmux_max_concurrency'] ?? '16' }} | 
                                                Connections: {{ $profile->settings['xmux_max_connections'] ?? '0' }} | 
                                                Reuse: {{ $profile->settings['xmux_c_max_reuse_times'] ?? '64-128' }}
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button class="btn btn-sm btn-outline-light px-3" onclick="editProfile({{ json_encode($profile) }})" data-bs-toggle="modal" data-bs-target="#profileModal">Edit</button>
                                            <form action="{{ route('admin.profiles.destroy', $profile->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this profile?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                                @if(count($profiles) === 0)
                                <tr>
                                    <td colspan="4" class="text-center text-secondary py-4">No configuration profiles created yet. Create a profile to assign reusable TLS/XHTTP/XMUX settings to hosts.</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Host Modal -->
    <div class="modal fade" id="hostModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content shadow-lg">
                <form id="hostForm" action="{{ route('admin.hosts.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title fw-bold" id="modalTitle">Add New SubHost</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row">
                            <!-- Left Column: Core Settings -->
                            <div class="col-md-6 border-end border-secondary/30">
                                <div class="section-title mt-0">Core Identification</div>
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label small">Friendly Name</label>
                                        <input type="text" name="name" id="name" class="form-control" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Prefix</label>
                                        <input type="text" name="remark_prefix" id="remark_prefix" class="form-control" value="IDN">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Connection Type</label>
                                        <select name="type" id="type" class="form-select">
                                            <option value="direct">Direct</option>
                                            <option value="reverse">Reverse</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Security</label>
                                        <select name="security" id="security" class="form-select">
                                            <option value="tls">TLS</option>
                                            <option value="none">None</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="section-title">Main Connection (Upload)</div>
                                <div class="row g-3">
                                    <div class="col-md-9">
                                        <label class="form-label small">Address</label>
                                        <input type="text" name="address" id="address" class="form-control" placeholder="srv.example.com" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small">Port</label>
                                        <input type="number" name="port" id="port" class="form-control" value="443" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">SNI / Host Header</label>
                                        <input type="text" name="sni" id="sni" class="form-control" placeholder="Optional if using profile">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Path</label>
                                        <input type="text" name="path" id="path" class="form-control" value="/">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">ALPN</label>
                                        <input type="text" name="alpn" id="alpn" class="form-control" value="h2">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Flow</label>
                                        <input type="text" name="flow" id="flow" class="form-control" placeholder="xtls-rprx-vision">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label small">Mode</label>
                                        <select name="mode" id="mode" class="form-select">
                                            <option value="packet-up">packet-up</option>
                                            <option value="stream-up">stream-up</option>
                                            <option value="auto">auto</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="section-title">Download Connection (Split-Domain Mapping)</div>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label small text-info fw-bold">Option A: Link to Direct SubHost (Upload via Reverse / Download via Direct)</label>
                                        <select name="download_host_id" id="download_host_id" class="form-select border-info/50">
                                            <option value="">-- Do not link (Uses Option B below) --</option>
                                            @foreach($hosts as $h)
                                                <option value="{{ $h->id }}">{{ $h->name }} ({{ $h->address }}:{{ $h->port }})</option>
                                            @endforeach
                                        </select>
                                        <div class="form-text text-slate-400 small">This binds this host's client download to the direct host's parameters automatically!</div>
                                    </div>

                                    <div class="col-md-12 text-center text-slate-500 my-2">-- OR --</div>

                                    <div class="col-md-8">
                                        <label class="form-label small">Option B: Static Download Address</label>
                                        <input type="text" name="download_address" id="download_address" class="form-control" placeholder="Leave empty for single-domain">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Static DL Port</label>
                                        <input type="number" name="download_port" id="download_port" class="form-control">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label small">Static Download SNI</label>
                                        <input type="text" name="download_sni" id="download_sni" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Profiles & Advanced -->
                            <div class="col-md-6">
                                <div class="section-title mt-0">Reusable Settings Profiles</div>
                                <div class="row g-3 bg-slate-900/40 p-3 rounded-3 border border-secondary/20 mb-3">
                                    <div class="col-md-12">
                                        <label class="form-label small fw-bold">TLS Profile</label>
                                        <select name="tls_profile_id" id="tls_profile_id" class="form-select border-primary/30">
                                            <option value="">-- Direct Config (Type values below manually) --</option>
                                            @foreach($tlsProfiles as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label small fw-bold">XHTTP Profile</label>
                                        <select name="xhttp_profile_id" id="xhttp_profile_id" class="form-select border-primary/30">
                                            <option value="">-- Direct Config (Type values below manually) --</option>
                                            @foreach($xhttpProfiles as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label small fw-bold">XMUX Profile</label>
                                        <select name="xmux_profile_id" id="xmux_profile_id" class="form-select border-primary/30">
                                            <option value="">-- Direct Config (Type values below manually) --</option>
                                            @foreach($xmuxProfiles as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="section-title">Direct XHTTP Parameters (Used if no profile chosen)</div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small">Padding Range</label>
                                        <input type="text" name="padding" id="padding" class="form-control" placeholder="100-500">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Max Each Post (Bytes)</label>
                                        <input type="text" name="sc_max_each_post_bytes" id="sc_max_each_post_bytes" class="form-control" placeholder="1000000-2000000">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Min Posts Interval (ms)</label>
                                        <input type="text" name="sc_min_posts_interval_ms" id="sc_min_posts_interval_ms" class="form-control" placeholder="50-150">
                                    </div>
                                    <div class="col-md-6 d-flex align-items-end pb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="no_grpc_header" id="no_grpc_header">
                                            <label class="form-check-label small" for="no_grpc_header">No gRPC Header</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="section-title">Direct XMUX (Used if no profile chosen)</div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label small">Max Concurrency</label>
                                        <input type="number" name="xmux_max_concurrency" id="xmux_max_concurrency" class="form-control" placeholder="16">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">Max Connections</label>
                                        <input type="number" name="xmux_max_connections" id="xmux_max_connections" class="form-control" placeholder="0">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small">C Max Reuse</label>
                                        <input type="text" name="xmux_c_max_reuse_times" id="xmux_c_max_reuse_times" class="form-control" placeholder="64-128">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">H Max Request Times</label>
                                        <input type="text" name="xmux_h_max_request_times" id="xmux_h_max_request_times" class="form-control" placeholder="800-900">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">H Max Reusable Secs</label>
                                        <input type="text" name="xmux_h_max_reusable_secs" id="xmux_h_max_reusable_secs" class="form-control" placeholder="120-240">
                                    </div>
                                </div>

                                <div class="section-title">Direct TLS & Reverse proxy (Used if no profile chosen)</div>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label small">PCS (Pinned Cert SHA256)</label>
                                        <input type="text" name="pcs" id="pcs" class="form-control">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label small">Cert PEM (Chain)</label>
                                        <textarea name="cert_pem" id="cert_pem" class="form-control font-monospace" rows="2" style="font-size: 10px;"></textarea>
                                    </div>
                                    <div class="col-12 d-flex flex-wrap gap-3 mt-2">
                                        <div class="form-check">
                                            <input class="form-check-input text-info" type="checkbox" name="is_cdn" id="is_cdn">
                                            <label class="form-check-label small text-info fw-bold" for="is_cdn">Behind CDN (Skip SSL/SNI Check)</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input text-purple" type="checkbox" name="is_reverse" id="is_reverse">
                                            <label class="form-check-label small text-purple fw-bold" for="is_reverse">Enable Reverse Logic</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="section-title">Manual Overrides & Mode Status</div>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label small">Manual Extra JSON (Overrides Automated Fields)</label>
                                        <textarea name="extra_json" id="extra_json" class="form-control font-monospace" rows="2" style="font-size: 10px;"></textarea>
                                    </div>
                                    <div class="col-12 d-flex flex-wrap gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                                            <label class="form-check-label small" for="is_active">Active</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_template" id="is_template">
                                            <label class="form-check-label small" for="is_template">Global Template</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="insecure" id="insecure">
                                            <label class="form-check-label small" for="insecure">Allow Insecure</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input text-warning" type="checkbox" name="is_dev" id="is_dev">
                                            <label class="form-check-label small text-warning fw-bold" for="is_dev">Development Node (Returns only in /sub-dev)</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary px-5 fw-bold rounded-pill">Save Configuration</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Profile Modal -->
    <div class="modal fade" id="profileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content shadow-lg">
                <form id="profileForm" action="{{ route('admin.profiles.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="profileFormMethod" value="POST">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title fw-bold" id="profileModalTitle">Add New Settings Profile</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small">Profile Name</label>
                                <input type="text" name="name" id="profile_name" class="form-control" placeholder="e.g. CF Direct TLS" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Profile Type</label>
                                <select name="type" id="profile_type" class="form-select" onchange="toggleProfileFields()" required>
                                    <option value="tls">TLS/SSL Settings</option>
                                    <option value="xhttp">XHTTP Transport Settings</option>
                                    <option value="xmux">XMUX Settings</option>
                                </select>
                            </div>
                        </div>

                        <!-- PROFILE SECTION: TLS -->
                        <div id="section-profile-tls" class="profile-section-group mt-3">
                            <div class="section-title mt-0">TLS Profile Parameters</div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small">Security</label>
                                    <select name="profile_security" id="profile_security" class="form-select">
                                        <option value="tls">TLS</option>
                                        <option value="none">None</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">SNI / ServerName</label>
                                    <input type="text" name="profile_sni" id="profile_sni" class="form-control" placeholder="e.g. sni.domain.com">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">ALPN (Comma separated)</label>
                                    <input type="text" name="profile_alpn" id="profile_alpn" class="form-control" value="h2">
                                </div>
                                <div class="col-md-6 d-flex align-items-end pb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="profile_insecure" id="profile_insecure">
                                        <label class="form-check-label small" for="profile_insecure">Allow Insecure (Skip Verification)</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small">PCS (Pinned Peer Certificate SHA256)</label>
                                    <input type="text" name="profile_pcs" id="profile_pcs" class="form-control" placeholder="e.g. sha256_hash_here">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small">Cert PEM (Chain)</label>
                                    <textarea name="profile_cert_pem" id="profile_cert_pem" class="form-control font-monospace" rows="4" style="font-size: 10px;" placeholder="-----BEGIN CERTIFICATE----- ..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- PROFILE SECTION: XHTTP -->
                        <div id="section-profile-xhttp" class="profile-section-group mt-3" style="display: none;">
                            <div class="section-title mt-0">XHTTP Transport Parameters</div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small">Padding Range</label>
                                    <input type="text" name="profile_padding" id="profile_padding" class="form-control" placeholder="e.g. 100-500" value="100-500">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Max Each Post (Bytes)</label>
                                    <input type="text" name="profile_sc_max_each_post_bytes" id="profile_sc_max_each_post_bytes" class="form-control" placeholder="e.g. 1000000-2000000">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Min Posts Interval (ms)</label>
                                    <input type="text" name="profile_sc_min_posts_interval_ms" id="profile_sc_min_posts_interval_ms" class="form-control" placeholder="e.g. 50-150">
                                </div>
                                <div class="col-md-6 d-flex align-items-end pb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="profile_no_grpc_header" id="profile_no_grpc_header">
                                        <label class="form-check-label small" for="profile_no_grpc_header">No gRPC Header</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PROFILE SECTION: XMUX -->
                        <div id="section-profile-xmux" class="profile-section-group mt-3" style="display: none;">
                            <div class="section-title mt-0">XMUX Multiplexing Parameters</div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small">Max Concurrency</label>
                                    <input type="number" name="profile_xmux_max_concurrency" id="profile_xmux_max_concurrency" class="form-control" placeholder="e.g. 16" value="16">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Max Connections</label>
                                    <input type="number" name="profile_xmux_max_connections" id="profile_xmux_max_connections" class="form-control" placeholder="e.g. 0" value="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">C Max Reuse</label>
                                    <input type="text" name="profile_xmux_c_max_reuse_times" id="profile_xmux_c_max_reuse_times" class="form-control" placeholder="e.g. 64-128" value="64-128">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">H Max Request Times</label>
                                    <input type="text" name="profile_xmux_h_max_request_times" id="profile_xmux_h_max_request_times" class="form-control" placeholder="e.g. 800-900" value="800-900">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">H Max Reusable Secs</label>
                                    <input type="text" name="profile_xmux_h_max_reusable_secs" id="profile_xmux_h_max_reusable_secs" class="form-control" placeholder="e.g. 120-240" value="120-240">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary px-5 fw-bold rounded-pill">Save Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleProfileFields() {
            const type = document.getElementById('profile_type').value;
            document.querySelectorAll('.profile-section-group').forEach(el => {
                el.style.display = 'none';
            });
            if (type === 'tls') {
                document.getElementById('section-profile-tls').style.display = 'block';
            } else if (type === 'xhttp') {
                document.getElementById('section-profile-xhttp').style.display = 'block';
            } else if (type === 'xmux') {
                document.getElementById('section-profile-xmux').style.display = 'block';
            }
        }

        function resetProfileForm() {
            document.getElementById('profileForm').action = "{{ route('admin.profiles.store') }}";
            document.getElementById('profileFormMethod').value = "POST";
            document.getElementById('profileModalTitle').innerText = "Add New Settings Profile";
            document.getElementById('profileForm').reset();
            document.getElementById('profile_type').disabled = false;
            toggleProfileFields();
        }

        function editProfile(profile) {
            document.getElementById('profileForm').action = "/sub/admin/profiles/" + profile.id;
            document.getElementById('profileFormMethod').value = "PUT";
            document.getElementById('profileModalTitle').innerText = "Edit Profile: " + profile.name;
            
            document.getElementById('profile_name').value = profile.name;
            document.getElementById('profile_type').value = profile.type;
            document.getElementById('profile_type').disabled = true; // Avoid changing type on edit
            
            toggleProfileFields();

            const settings = profile.settings || {};

            if (profile.type === 'tls') {
                document.getElementById('profile_security').value = settings.security || 'tls';
                document.getElementById('profile_sni').value = settings.sni || '';
                document.getElementById('profile_alpn').value = settings.alpn || 'h2';
                document.getElementById('profile_insecure').checked = !!settings.insecure;
                document.getElementById('profile_pcs').value = settings.pcs || '';
                document.getElementById('profile_cert_pem').value = settings.cert_pem || '';
            } else if (profile.type === 'xhttp') {
                document.getElementById('profile_padding').value = settings.padding || '100-500';
                document.getElementById('profile_no_grpc_header').checked = !!settings.no_grpc_header;
                document.getElementById('profile_sc_max_each_post_bytes').value = settings.sc_max_each_post_bytes || '';
                document.getElementById('profile_sc_min_posts_interval_ms').value = settings.sc_min_posts_interval_ms || '';
            } else if (profile.type === 'xmux') {
                document.getElementById('profile_xmux_max_concurrency').value = settings.xmux_max_concurrency || '16';
                document.getElementById('profile_xmux_max_connections').value = settings.xmux_max_connections || '0';
                document.getElementById('profile_xmux_c_max_reuse_times').value = settings.xmux_c_max_reuse_times || '64-128';
                document.getElementById('profile_xmux_h_max_request_times').value = settings.xmux_h_max_request_times || '800-900';
                document.getElementById('profile_xmux_h_max_reusable_secs').value = settings.xmux_h_max_reusable_secs || '120-240';
            }
        }

        function resetForm() {
            document.getElementById('hostForm').action = "{{ route('admin.hosts.store') }}";
            document.getElementById('formMethod').value = "POST";
            document.getElementById('modalTitle').innerText = "Add New SubHost";
            document.getElementById('hostForm').reset();
            
            // Set defaults
            document.getElementById('padding').value = "100-500";
            document.getElementById('xmux_max_concurrency').value = 16;
            document.getElementById('xmux_c_max_reuse_times').value = "64-128";
            document.getElementById('xmux_h_max_request_times').value = "800-900";
            document.getElementById('xmux_h_max_reusable_secs').value = "120-240";
        }

        function editHost(host) {
            document.getElementById('hostForm').action = "/sub/admin/hosts/" + host.id;
            document.getElementById('formMethod').value = "PUT";
            document.getElementById('modalTitle').innerText = "Edit SubHost: " + host.name;
            
            // Identification
            document.getElementById('name').value = host.name;
            document.getElementById('remark_prefix').value = host.remark_prefix || 'IDN';
            document.getElementById('type').value = host.type;
            document.getElementById('security').value = host.security || 'tls';
            
            // Upload
            document.getElementById('address').value = host.address;
            document.getElementById('port').value = host.port;
            document.getElementById('sni').value = host.sni || '';
            document.getElementById('path').value = host.path || '/';
            document.getElementById('alpn').value = host.alpn || 'h2';
            document.getElementById('flow').value = host.flow || '';
            document.getElementById('mode').value = host.mode || 'packet-up';
            
            // Link Download Host
            document.getElementById('download_host_id').value = host.download_host_id || '';
            
            // Static Download
            document.getElementById('download_address').value = host.download_address || '';
            document.getElementById('download_port').value = host.download_port || '';
            document.getElementById('download_sni').value = host.download_sni || '';
            
            // Profiles
            document.getElementById('tls_profile_id').value = host.tls_profile_id || '';
            document.getElementById('xhttp_profile_id').value = host.xhttp_profile_id || '';
            document.getElementById('xmux_profile_id').value = host.xmux_profile_id || '';

            // Automated XHTTP Direct values
            document.getElementById('padding').value = host.padding || '';
            document.getElementById('sc_max_each_post_bytes').value = host.sc_max_each_post_bytes || '';
            document.getElementById('sc_min_posts_interval_ms').value = host.sc_min_posts_interval_ms || '';
            document.getElementById('no_grpc_header').checked = !!host.no_grpc_header;
            
            // XMUX Direct values
            document.getElementById('xmux_max_concurrency').value = host.xmux_max_concurrency || '';
            document.getElementById('xmux_max_connections').value = host.xmux_max_connections || '';
            document.getElementById('xmux_c_max_reuse_times').value = host.xmux_c_max_reuse_times || '';
            document.getElementById('xmux_h_max_request_times').value = host.xmux_h_max_request_times || '';
            document.getElementById('xmux_h_max_reusable_secs').value = host.xmux_h_max_reusable_secs || '';
            
            // Advanced Direct values
            document.getElementById('pcs').value = host.pcs || '';
            document.getElementById('cert_pem').value = host.cert_pem || '';
            document.getElementById('is_cdn').checked = !!host.is_cdn;
            document.getElementById('is_reverse').checked = !!host.is_reverse;
            
            // Status
            document.getElementById('is_active').checked = !!host.is_active;
            document.getElementById('is_template').checked = !!host.is_template;
            document.getElementById('insecure').checked = !!host.insecure;
            document.getElementById('is_dev').checked = !!host.is_dev;

            document.getElementById('extra_json').value = JSON.stringify(host.extra || {}, null, 2);
        }
    </script>
</body>
</html>
