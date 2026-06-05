<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - IDN Sub Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #0f172a; color: #f1f5f9; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background-color: #1e293b; border: 1px solid #334155; border-radius: 1rem; padding: 2.5rem; width: 100%; max-width: 400px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); }
        .form-control { background-color: #0f172a; border-color: #334155; color: #f1f5f9; }
        .form-control:focus { background-color: #0f172a; border-color: #3b82f6; color: #f1f5f9; box-shadow: none; }
        .btn-primary { background-color: #2563eb; border: none; font-weight: bold; }
        .btn-primary:hover { background-color: #1d4ed8; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <h2 class="fw-bold text-primary">IDN Admin</h2>
            <p class="text-secondary small uppercase tracking-wider">Subscription Infrastructure</p>
        </div>

        @if($errors->has('login'))
            <div class="alert alert-danger py-2 small mb-4">
                {{ $errors->first('login') }}
            </div>
        @endif

        <form action="/sub/admin/login" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label small text-secondary">Username</label>
                <input type="text" name="username" class="form-control" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label small text-secondary">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2">Login to Dashboard</button>
        </form>
    </div>
</body>
</html>
