<template>
    <div class="min-h-screen bg-slate-900 text-slate-100 font-sans antialiased">
        <nav class="bg-slate-800 border-b border-slate-700 px-6 py-4 flex justify-between items-center shadow-lg">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center font-bold">IDN</div>
                <span class="text-xl font-bold tracking-tight">SubManager</span>
            </div>
            <div class="flex gap-6 text-sm font-medium text-slate-400">
                <router-link to="/sub/admin" class="hover:text-blue-400 transition-colors" active-class="text-blue-400">Hosts</router-link>
                <a href="#" class="hover:text-blue-400 transition-colors">Users</a>
                <a href="#" class="hover:text-blue-400 transition-colors">Settings</a>
            </div>
        </nav>
        
        <main class="max-w-7xl mx-auto py-8">
            <router-view></router-view>
        </main>
        
        <footer class="mt-auto py-8 text-center text-slate-500 text-xs border-t border-slate-800/50">
            &copy; 2026 Internet Delivery Network &bull; Baremetal Sub-Service v1.0
        </footer>
    </div>
</template>

<style>
/* Custom scrollbar for a more modern look */
::-webkit-scrollbar {
    width: 8px;
}
::-webkit-scrollbar-track {
    background: #0f172a;
}
::-webkit-scrollbar-thumb {
    background: #334155;
    border-radius: 4px;
}
::-webkit-scrollbar-thumb:hover {
    background: #475569;
}
</style>
