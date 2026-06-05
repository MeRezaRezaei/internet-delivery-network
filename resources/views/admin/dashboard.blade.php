<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - IDN Sub Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #0f172a; color: #f1f5f9; font-family: 'Inter', sans-serif; }
        .navbar { background-color: #1e293b; border-bottom: 1px solid #334155; }
        .card { background-color: #1e293b; border: 1px solid #334155; border-radius: 0.75rem; }
        .table { color: #f1f5f9; }
        .table-hover tbody tr:hover { background-color: #334155; color: #f1f5f9; }
        .modal-content { background-color: #1e293b; color: #f1f5f9; border: 1px solid #334155; }
        .form-control, .form-select { background-color: #0f172a; border-color: #334155; color: #f1f5f9; }
        .form-control:focus, .form-select:focus { background-color: #0f172a; border-color: #3b82f6; color: #f1f5f9; box-shadow: none; }
        .badge-active { background-color: #065f46; color: #34d399; }
        .badge-inactive { background-color: #7f1d1d; color: #f87171; }
        .btn-close { filter: invert(1); }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold" href="#">IDN <span class="text-primary">Admin</span></a>
            <div class="d-flex">
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">SubHost Infrastructure</h2>
            <button class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#hostModal" onclick="resetForm()">+ Add New Host</button>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Address:Port</th>
                            <th>Status</th>
                            <th>Template</th>
                            <th>XHTTP Logic</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($hosts as $host)
                        <tr class="align-middle">
                            <td>
                                <div class="fw-bold">{{ $host->name }}</div>
                                <div class="text-secondary small">{{ $host->remark_prefix }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $host->type == 'reverse' ? 'bg-purple-900 text-purple-200' : 'bg-blue-900 text-blue-200' }} text-uppercase">
                                    {{ $host->type }}
                                </span>
                            </td>
                            <td>
                                <div>{{ $host->address }}:{{ $host->port }}</div>
                                @if($host->download_address)
                                <div class="text-info small">DL: {{ $host->download_address }}:{{ $host->download_port ?? $host->port }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $host->is_active ? 'badge-active' : 'badge-inactive' }}">
                                    {{ $host->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                @if($host->is_template)
                                <span class="badge bg-warning text-dark">Template</span>
                                @else
                                <span class="text-secondary">-</span>
                                @endif
                            </td>
                            <td>
                                @if($host->is_reverse)
                                <span class="text-purple-400 small">Reverse (PCS)</span>
                                @else
                                <span class="text-blue-400 small">Direct</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-light" onclick="editHost({{ json_encode($host) }})" data-bs-toggle="modal" data-bs-target="#hostModal">Edit</button>
                                    <form action="{{ route('admin.hosts.destroy', $host->id) }}" method="POST" onsubmit="return confirm('Delete this host?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Del</button>
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

    <!-- Host Modal -->
    <div class="modal fade" id="hostModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content shadow-lg">
                <form id="hostForm" action="{{ route('admin.hosts.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title fw-bold" id="modalTitle">Add New SubHost</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small">Friendly Name</label>
                                <input type="text" name="name" id="name" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Type</label>
                                <select name="type" id="type" class="form-select">
                                    <option value="direct">Direct</option>
                                    <option value="reverse">Reverse</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Prefix</label>
                                <input type="text" name="remark_prefix" id="remark_prefix" class="form-control" value="IDN">
                            </div>

                            <div class="col-md-8">
                                <label class="form-label small">Address (Upload)</label>
                                <input type="text" name="address" id="address" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Port</label>
                                <input type="number" name="port" id="port" class="form-control" value="443" required>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label small">Download Address (Optional)</label>
                                <input type="text" name="download_address" id="download_address" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Download Port</label>
                                <input type="number" name="download_port" id="download_port" class="form-control">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small">SNI</label>
                                <input type="text" name="sni" id="sni" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Path</label>
                                <input type="text" name="path" id="path" class="form-control" value="/">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">PCS (Pinned Cert SHA256)</label>
                                <input type="text" name="pcs" id="pcs" class="form-control">
                            </div>

                            <div class="col-12">
                                <label class="form-label small">Cert PEM (For Reverse Proxy)</label>
                                <textarea name="cert_pem" id="cert_pem" class="form-control font-monospace" rows="3" style="font-size: 10px;"></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label small">Extra JSON Overrides</label>
                                <textarea name="extra_json" id="extra_json" class="form-control font-monospace" rows="4" style="font-size: 10px;"></textarea>
                            </div>

                            <div class="col-12 d-flex gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                                    <label class="form-check-label small" for="is_active">Active</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_template" id="is_template">
                                    <label class="form-check-label small" for="is_template">Global Template</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_reverse" id="is_reverse">
                                    <label class="form-check-label small" for="is_reverse">Enable Reverse Logic</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="insecure" id="insecure">
                                    <label class="form-check-label small" for="insecure">Allow Insecure</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function resetForm() {
            document.getElementById('hostForm').action = "{{ route('admin.hosts.store') }}";
            document.getElementById('formMethod').value = "POST";
            document.getElementById('modalTitle').innerText = "Add New SubHost";
            document.getElementById('hostForm').reset();
            document.getElementById('extra_json').value = JSON.stringify({
                xmux: { maxConcurrency: "8-16", cMaxReuseTimes: "10-20" }
            }, null, 2);
        }

        function editHost(host) {
            document.getElementById('hostForm').action = "/sub/admin/hosts/" + host.id;
            document.getElementById('formMethod').value = "PUT";
            document.getElementById('modalTitle').innerText = "Edit SubHost: " + host.name;
            
            document.getElementById('name').value = host.name;
            document.getElementById('address').value = host.address;
            document.getElementById('port').value = host.port;
            document.getElementById('type').value = host.type;
            document.getElementById('remark_prefix').value = host.remark_prefix;
            document.getElementById('download_address').value = host.download_address || '';
            document.getElementById('download_port').value = host.download_port || '';
            document.getElementById('sni').value = host.sni || '';
            document.getElementById('path').value = host.path || '/';
            document.getElementById('pcs').value = host.pcs || '';
            document.getElementById('cert_pem').value = host.cert_pem || '';
            document.getElementById('extra_json').value = JSON.stringify(host.extra || {}, null, 2);
            
            document.getElementById('is_active').checked = host.is_active;
            document.getElementById('is_template').checked = host.is_template;
            document.getElementById('is_reverse').checked = host.is_reverse;
            document.getElementById('insecure').checked = host.insecure;
        }
    </script>
</body>
</html>
