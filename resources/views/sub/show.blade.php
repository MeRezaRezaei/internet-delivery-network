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
<body class="p-4 md:p-10 pb-20">
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-8 bg-slate-800/50 p-6 rounded-2xl border border-slate-700/50 backdrop-blur-sm">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center font-bold text-xl shadow-lg shadow-blue-500/20">IDN</div>
                <div>
                    <h1 class="text-2xl font-bold text-white leading-none mb-1">IDN Premium</h1>
                    <p class="text-slate-400 text-sm font-medium">{{ $user->username }}</p>
                </div>
            </div>
            <div class="text-right">
                <span class="inline-block bg-green-500/10 text-green-400 px-4 py-1.5 rounded-full text-xs font-bold border border-green-500/20 uppercase tracking-widest">{{ $user->status }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="card bg-slate-800/40 border-slate-700/50 p-6 text-center">
                <p class="text-slate-500 text-[10px] uppercase font-bold tracking-[0.2em] mb-2">Usage</p>
                <p class="text-3xl font-black text-white">{{ round($user->used_traffic / (1024*1024*1024), 2) }}<span class="text-sm font-normal text-slate-500 ml-1">GB</span></p>
            </div>
            <div class="card bg-slate-800/40 border-slate-700/50 p-6 text-center">
                <p class="text-slate-500 text-[10px] uppercase font-bold tracking-[0.2em] mb-2">Limit</p>
                <p class="text-3xl font-black text-white">{{ $user->data_limit ? round($user->data_limit / (1024*1024*1024), 2) : '∞' }}<span class="text-sm font-normal text-slate-500 ml-1">GB</span></p>
            </div>
            <div class="card bg-slate-800/40 border-slate-700/50 p-6 text-center">
                <p class="text-slate-500 text-[10px] uppercase font-bold tracking-[0.2em] mb-2">Expiry</p>
                <p class="text-xl font-black text-white">{{ $user->expire ? date('Y-m-d', $user->expire) : 'Never' }}</p>
            </div>
        </div>

        <div class="card bg-slate-800/40 border-slate-700/50 mb-8 overflow-hidden">
            <div class="p-6">
                <h2 class="text-lg font-bold mb-4 flex items-center gap-2 text-blue-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                    Universal Subscription
                </h2>
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1 relative">
                        <input id="subLink" type="text" readonly value="{{ $subLink }}" class="w-full bg-slate-900/50 border border-slate-700/80 rounded-xl px-4 py-3 text-sm text-slate-300 focus:outline-none focus:border-blue-500/50 transition-colors font-mono">
                    </div>
                    <button onclick="copySub()" class="bg-blue-600 hover:bg-blue-500 active:scale-95 text-white px-8 py-3 rounded-xl font-bold transition-all shadow-lg shadow-blue-600/20">Copy URL</button>
                </div>
                <p class="text-[10px] text-slate-500 mt-4 uppercase font-medium tracking-wider">Compatible with: v2rayNG, v2rayN, Streisand, Shadowrocket, Clash Meta</p>
            </div>
        </div>

        <div class="space-y-6">
            <h2 class="text-xl font-black px-1 flex items-center justify-between">
                <span>Direct Node Access</span>
                <span class="text-xs font-normal text-slate-500 uppercase tracking-widest">{{ count($uris) }} Nodes Active</span>
            </h2>
            
            @foreach($uris as $uri)
                @php
                    $remark = urldecode(explode('#', $uri)[1] ?? 'Server');
                    $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&bgcolor=0f172a&color=ffffff&data=" . urlencode($uri);
                @endphp
                <div class="card bg-slate-800/40 border-slate-700/50 group transition-all hover:bg-slate-800/60 overflow-hidden">
                    <div class="flex flex-col sm:flex-row gap-6 p-6">
                        <div class="w-full sm:w-32 h-32 bg-slate-900 rounded-xl border border-slate-700/50 flex items-center justify-center p-2 relative">
                            <img src="{{ $qrUrl }}" alt="QR" class="w-full h-full rounded-lg">
                            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-[1px] opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center rounded-xl pointer-events-none">
                                <span class="text-[8px] font-black uppercase tracking-[0.2em] text-white">Scan to Import</span>
                            </div>
                        </div>
                        
                        <div class="flex-1 flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="font-black text-lg text-white group-hover:text-blue-400 transition-colors">{{ $remark }}</h3>
                                    <span class="text-[10px] bg-blue-500/10 text-blue-400 px-2 py-0.5 rounded border border-blue-500/20 font-bold tracking-tighter uppercase">VLESS XHTTP</span>
                                </div>
                                <p class="text-xs text-slate-500 font-mono mb-4 line-clamp-1 opacity-60">{{ $uri }}</p>
                            </div>
                            
                            <div class="flex flex-wrap gap-2">
                                <button onclick="copyText('{{ $uri }}')" class="bg-slate-700/50 hover:bg-slate-700 text-slate-200 px-4 py-2 rounded-lg text-xs font-bold transition-all border border-slate-600/50">Copy URI</button>
                                <a href="{{ $uri }}" class="bg-blue-600/10 hover:bg-blue-600/20 text-blue-400 px-4 py-2 rounded-lg text-xs font-bold transition-all border border-blue-500/20">Direct Import</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <footer class="mt-20 text-center text-slate-600 text-xs font-medium uppercase tracking-[0.3em]">
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
