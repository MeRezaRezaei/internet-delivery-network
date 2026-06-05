<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription - {{ $user->username }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #0f172a; color: #f1f5f9; font-family: 'Inter', sans-serif; }
        .card { background-color: #1e293b; border: 1px solid #334155; border-radius: 0.75rem; padding: 1.5rem; }
    </style>
</head>
<body class="p-4 md:p-10">
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold text-blue-400">IDN Premium</h1>
            <span class="bg-green-900 text-green-300 px-3 py-1 rounded-full text-sm font-medium">{{ ucfirst($user->status) }}</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <div class="card text-center">
                <p class="text-slate-400 text-sm mb-1 uppercase tracking-wider">Used Traffic</p>
                <p class="text-2xl font-bold">{{ round($user->used_traffic / (1024*1024*1024), 2) }} GB</p>
            </div>
            <div class="card text-center">
                <p class="text-slate-400 text-sm mb-1 uppercase tracking-wider">Total Limit</p>
                <p class="text-2xl font-bold">{{ $user->data_limit ? round($user->data_limit / (1024*1024*1024), 2) . ' GB' : 'Unlimited' }}</p>
            </div>
        </div>

        <div class="card mb-8">
            <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
                Subscription Link
            </h2>
            <div class="flex gap-2">
                <input id="subLink" type="text" readonly value="{{ url()->current() }}" class="flex-1 bg-slate-900 border border-slate-700 rounded px-4 py-2 text-sm text-slate-300 focus:outline-none">
                <button onclick="copySub()" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded font-bold transition-colors">Copy</button>
            </div>
            <p class="text-xs text-slate-500 mt-2">Import this link into v2rayN, v2rayNG, or Streisand.</p>
        </div>

        <div class="space-y-4">
            <h2 class="text-xl font-bold px-1">Available Servers ({{ count($uris) }})</h2>
            @foreach($uris as $uri)
                <div class="card">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="font-bold text-slate-200">{{ urldecode(explode('#', $uri)[1] ?? 'Server') }}</h3>
                            <p class="text-xs text-slate-500 uppercase tracking-tighter">VLESS + XHTTP</p>
                        </div>
                        <button onclick="copyText('{{ $uri }}')" class="text-blue-400 hover:text-blue-300 text-sm font-medium">Copy URI</button>
                    </div>
                    <code class="block bg-slate-900 p-2 rounded text-[10px] text-slate-400 break-all overflow-hidden line-clamp-1 border border-slate-800">
                        {{ $uri }}
                    </code>
                </div>
            @endforeach
        </div>

        <footer class="mt-12 text-center text-slate-600 text-sm">
            &copy; 2026 Internet Delivery Network
        </footer>
    </div>

    <script>
        function copySub() {
            const copyText = document.getElementById("subLink");
            copyText.select();
            document.execCommand("copy");
            alert("Subscription link copied!");
        }
        function copyText(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert("URI copied!");
            });
        }
    </script>
</body>
</html>
