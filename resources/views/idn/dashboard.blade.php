<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IDN Control Plane Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f1f3f5; color: #212529; font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        .navbar { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .card { border: none; border-radius: 12px; transition: transform 0.2s; background: white; margin-bottom: 24px; }
        .card-header { background: white; border-bottom: 1px solid #e9ecef; border-radius: 12px 12px 0 0 !important; padding: 16px 20px; }
        .status-online { color: #10b981; font-weight: 600; display: inline-flex; align-items: center; }
        .status-online::before { content: ''; display: inline-block; width: 8px; height: 8px; background: #10b981; border-radius: 50%; margin-right: 8px; box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2); }
        .status-offline { color: #ef4444; font-weight: 600; display: inline-flex; align-items: center; }
        .status-offline::before { content: ''; display: inline-block; width: 8px; height: 8px; background: #ef4444; border-radius: 50%; margin-right: 8px; }
        #log-viewer { background: #1e293b; color: #e2e8f0; font-family: 'Fira Code', 'Courier New', monospace; height: 300px; overflow-y: auto; padding: 15px; border-radius: 8px; font-size: 0.85rem; line-height: 1.5; }
        .log-entry { margin-bottom: 4px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 2px; }
        .log-time { color: #94a3b8; margin-right: 8px; }
        .log-node { color: #38bdf8; font-weight: 600; margin-right: 8px; }
        .log-level-INFO { color: #10b981; }
        .log-level-ERROR { color: #ef4444; font-weight: bold; }
        .log-level-WARNING { color: #fbbf24; }
        .badge { font-weight: 500; padding: 0.5em 0.75em; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                IDN Control Plane
            </a>
            <div class="d-flex align-items-center text-light opacity-75 small">
                <span id="current-time"></span>
            </div>
        </div>
    </nav>

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <!-- Node Fleet -->
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-uppercase tracking-wider">Node Fleet Status</h6>
                        <span class="badge bg-light text-dark border">{{ count($nodes) }} Nodes</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 border-0">Node Name</th>
                                        <th class="border-0">Status</th>
                                        <th class="border-0">IP/Host</th>
                                        <th class="border-0 text-center">Sync State</th>
                                        <th class="border-0 text-end pe-4">Last Seen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($nodes as $node)
                                        @php $status = $fleetStatus[$node->name] ?? null; @endphp
                                        <tr>
                                            <td class="ps-4 fw-600">{{ $node->name }} <br><small class="text-muted text-uppercase" style="font-size: 10px">{{ $node->role }}</small></td>
                                            <td>
                                                @if($status && $status['healthy'])
                                                    <span class="status-online">ONLINE</span>
                                                @else
                                                    <span class="status-offline">OFFLINE</span>
                                                @endif
                                            </td>
                                            <td><code>{{ $node->hostname }}</code></td>
                                            <td class="text-center">
                                                @if($status)
                                                    <span class="badge {{ ($status['sync_state']['status'] ?? '') === 'SUCCESS' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} rounded-pill px-3 border">
                                                        {{ $status['sync_state']['status'] ?? 'UNKNOWN' }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-light text-muted rounded-pill px-3 border">WAITING</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-4 text-muted small">{{ $status['last_seen'] ?? 'Never' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tunnels -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-uppercase">Active Tunnels</h6>
                        <button class="btn btn-sm btn-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addTunnelModal">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg me-1" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2Z"/></svg>
                            Add Tunnel
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Tag</th>
                                        <th>Route</th>
                                        <th>Protocol</th>
                                        <th>Port</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($tunnels as $tunnel)
                                        <tr>
                                            <td class="ps-4"><code>{{ $tunnel->tag }}</code></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    {{ $tunnel->sourceNode->name }} 
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="mx-2 opacity-50" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/></svg>
                                                    {{ $tunnel->targetNode->name }}
                                                </div>
                                            </td>
                                            <td><span class="badge bg-dark rounded-pill">{{ strtoupper($tunnel->protocol) }}</span></td>
                                            <td><span class="text-primary fw-600">{{ $tunnel->port }}</span></td>
                                            <td class="text-end pe-4">
                                                <form action="{{ route('idn.tunnels.destroy', $tunnel) }}" method="POST" onsubmit="return confirm('Confirm tunnel teardown?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-link text-danger p-0 text-decoration-none fw-600">TEARDOWN</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-5 small">Network is currently silent. No active tunnels registered.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Log Viewer Sidebar -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 bg-dark text-light overflow-hidden">
                    <div class="card-header bg-dark border-secondary d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 text-light small fw-bold text-uppercase">Real-time Node Logs</h6>
                        <div class="spinner-grow spinner-grow-sm text-success" role="status"></div>
                    </div>
                    <div class="card-body p-0">
                        <div id="log-viewer">
                            <div class="text-muted small opacity-50">Initializing log stream...</div>
                        </div>
                    </div>
                </div>
                
                <div class="card shadow-sm border-0 mt-4">
                    <div class="card-header bg-white"><h6 class="mb-0 small fw-bold text-uppercase">DNS Policy Control (Technitium)</h6></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="small fw-600">Ad-Blocking & Filtering</span>
                            <form action="{{ route('idn.dns.toggle') }}" method="POST">
                                @csrf
                                <input type="hidden" name="enabled" value="true">
                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-3">ENABLE</button>
                            </form>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small text-muted">Current: Active</span>
                            <form action="{{ route('idn.dns.toggle') }}" method="POST">
                                @csrf
                                <input type="hidden" name="enabled" value="false">
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">DISABLE</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mt-4">
                    <div class="card-header bg-white"><h6 class="mb-0 small fw-bold text-uppercase">Control Shortcuts</h6></div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-secondary btn-sm text-start py-2 px-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="me-2" viewBox="0 0 16 16"><path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"/><path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A4.902 4.902 0 0 1 8 2c2.73 0 4.941 2.21 5.058 4.933a.5.5 0 0 1-1 0C11.958 4.21 10.183 3 8 3zm4.629 8.182c.917-1.111 1.482-2.5 1.482-4.032a.5.5 0 1 1 1 0c0 1.959-.726 3.748-1.928 5.112a.5.5 0 1 1-.772-.636zM8 13c1.552 0 2.94-.707 3.857-1.818a.5.5 0 1 1 .771.636A4.902 4.902 0 0 1 8 14c-2.73 0-4.941-2.21-5.058-4.933a.5.5 0 0 1 1 0C3.042 11.79 4.817 13 8 13z"/></svg>
                                Sync All Nodes
                            </button>
                            <button class="btn btn-outline-secondary btn-sm text-start py-2 px-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="me-2" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M5 6.5A1.5 1.5 0 1 1 6.5 5 1.5 1.5 0 0 1 5 6.5zM11 6.5A1.5 1.5 0 1 1 12.5 5 1.5 1.5 0 0 1 11 6.5zM3.93 11a5.505 5.505 0 0 1 8.14 0l.93-.93a6.82 6.82 0 0 0-10 0l.93.93z"/></svg>
                                Fleet Health Check
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Tunnel Modal -->
    <div class="modal fade" id="addTunnelModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('idn.tunnels.store') }}" method="POST">
                @csrf
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Provision New Tunnel</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-uppercase small fw-bold text-muted">Source Node</label>
                                <select name="source_node_id" class="form-select border-0 bg-light">
                                    @foreach($nodes as $node) <option value="{{ $node->id }}">{{ $node->name }}</option> @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-uppercase small fw-bold text-muted">Target Node</label>
                                <select name="target_node_id" class="form-select border-0 bg-light">
                                    @foreach($nodes as $node) <option value="{{ $node->id }}">{{ $node->name }}</option> @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-uppercase small fw-bold text-muted">Tunnel Tag</label>
                            <input type="text" name="tag" class="form-control border-0 bg-light" placeholder="e.g. us-bridge-01" required>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-uppercase small fw-bold text-muted">Port</label>
                                <input type="number" name="port" class="form-control border-0 bg-light" value="20000" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-uppercase small fw-bold text-muted">Protocol</label>
                                <select name="protocol" class="form-select border-0 bg-light">
                                    <option value="vless">VLESS</option>
                                    <option value="socks">SOCKS</option>
                                    <option value="vmess">VMESS</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-uppercase small fw-bold text-muted">Obfuscation</label>
                                <select class="form-select border-0 bg-light">
                                    <option>NONE</option>
                                    <option>TCP/TLS</option>
                                    <option>XHTTP</option>
                                    <option>SPLIT-HTTP</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label text-uppercase small fw-bold text-muted">Xray Configuration Template (JSON)</label>
                            <textarea name="config" class="form-control border-0 bg-light font-monospace" rows="6" style="font-size: 13px">{"settings": {"users": [{"id": "...", "flow": "xtls-rprx-vision"}]}}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-link text-decoration-none text-muted" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4 rounded-pill">Provision & Deploy</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Real-time Logs Polling
        let lastLogId = '0';
        const logViewer = document.getElementById('log-viewer');

        function fetchLogs() {
            fetch(`/idn/api/logs?last_id=${lastLogId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.logs && data.logs.length > 0) {
                        data.logs.forEach(log => {
                            const entry = document.createElement('div');
                            entry.className = 'log-entry';
                            entry.innerHTML = `
                                <span class="log-time">[${new Date(log.timestamp).toLocaleTimeString()}]</span>
                                <span class="log-node">${log.node}</span>
                                <span class="log-level-${log.level}">${log.level}</span>
                                <span class="log-msg">${log.message}</span>
                            `;
                            logViewer.appendChild(entry);
                        });
                        lastLogId = data.last_id;
                        logViewer.scrollTop = logViewer.scrollHeight;
                        
                        // Limit lines in viewer
                        while(logViewer.childNodes.length > 100) {
                            logViewer.removeChild(logViewer.firstChild);
                        }
                    }
                })
                .catch(err => console.error('Log fetch error:', err));
        }

        setInterval(fetchLogs, 2000);
        setInterval(() => {
            document.getElementById('current-time').innerText = new Date().toLocaleString();
        }, 1000);
    </script>
</body>
</html>
