<template>
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Host Manager (XHTTP/VLESS)</h1>
            <button @click="openModal()" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded">Add Host</button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="host in hosts" :key="host.id" :class="host.is_template ? 'border-yellow-500/50 bg-yellow-500/5' : 'border-slate-700 bg-slate-800'" class="p-4 rounded-xl shadow-lg border transition-all hover:scale-[1.02]">
                <div class="flex justify-between items-start mb-2">
                    <div class="flex flex-col">
                        <h3 class="font-bold text-lg text-blue-400 leading-tight">{{ host.name }}</h3>
                        <div class="flex gap-1 mt-1">
                            <span v-if="host.is_template" class="text-[8px] bg-yellow-600/20 text-yellow-500 font-bold uppercase tracking-widest px-1.5 py-0.5 rounded border border-yellow-500/30">Template</span>
                            <span v-if="host.is_reverse" class="text-[8px] bg-purple-600/20 text-purple-400 font-bold uppercase tracking-widest px-1.5 py-0.5 rounded border border-purple-500/30">Reverse</span>
                        </div>
                    </div>
                    <span :class="host.is_active ? 'bg-green-900/50 text-green-300 border-green-700/50' : 'bg-red-900/50 text-red-300 border-red-700/50'" class="text-[10px] px-2 py-0.5 rounded border font-bold uppercase tracking-tighter">
                        {{ host.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <p class="text-sm text-slate-400 mb-1">Upload: <span class="text-slate-200">{{ host.address }}:{{ host.port }}</span></p>
                <p v-if="host.download_address" class="text-sm text-slate-400 mb-1">Download: <span class="text-slate-200">{{ host.download_address }}:{{ host.download_port || host.port }}</span></p>
                <p class="text-sm text-slate-400 mb-4 font-mono text-[10px]">Path: <span class="text-slate-300">{{ host.path }}</span></p>
                
                <div class="flex gap-2">
                    <button @click="editHost(host)" class="flex-1 bg-slate-700 hover:bg-slate-600 py-1.5 rounded text-xs font-bold transition-colors">Edit</button>
                    <button @click="deleteHost(host.id)" class="flex-1 bg-red-900/30 hover:bg-red-900/50 py-1.5 rounded text-xs text-red-200 font-bold border border-red-900/50 transition-colors">Delete</button>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black/80 flex items-center justify-center p-4 z-50 backdrop-blur-sm">
            <div class="bg-slate-800 w-full max-w-3xl rounded-2xl shadow-2xl border border-slate-700 overflow-hidden max-h-[95vh] flex flex-col">
                <div class="p-6 border-b border-slate-700 flex justify-between items-center">
                    <h2 class="text-xl font-bold">{{ editingId ? 'Edit Host' : 'Add New Host' }}</h2>
                    <button @click="showModal = false" class="text-slate-400 hover:text-white text-2xl font-light">&times;</button>
                </div>
                <div class="p-6 overflow-y-auto space-y-6">
                    <!-- Basic Info -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Name</label>
                            <input v-model="form.name" type="text" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:border-blue-500/50 focus:outline-none transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Type</label>
                            <select v-model="form.type" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:border-blue-500/50 focus:outline-none transition-colors">
                                <option value="direct">Direct</option>
                                <option value="reverse">Reverse</option>
                            </select>
                        </div>
                    </div>

                    <!-- Upload / Main Connection -->
                    <div class="bg-slate-900/30 p-4 rounded-xl border border-slate-700/50">
                        <h3 class="text-xs font-black text-blue-500 uppercase tracking-[0.2em] mb-4">Main Connection (Upload)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="lg:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Address</label>
                                <input v-model="form.address" type="text" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Port</label>
                                <input v-model.number="form.port" type="number" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Path</label>
                                <input v-model="form.path" type="text" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">SNI / Host</label>
                                <input v-model="form.sni" type="text" placeholder="Same as address" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">PCS (Pinned Cert SHA256)</label>
                                <input v-model="form.pcs" type="text" placeholder="For Reverse Proxies" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm">
                            </div>
                        </div>
                    </div>

                    <!-- Download Connection (Split-Domain) -->
                    <div class="bg-slate-900/30 p-4 rounded-xl border border-slate-700/50">
                        <h3 class="text-xs font-black text-green-500 uppercase tracking-[0.2em] mb-4">Download Connection (Split-Domain)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Download Address</label>
                                <input v-model="form.download_address" type="text" placeholder="Leave empty for single-domain" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Download Port</label>
                                <input v-model.number="form.download_port" type="number" placeholder="Defaults to main port" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Download SNI</label>
                            <input v-model="form.download_sni" type="text" placeholder="Same as download address" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm">
                        </div>
                    </div>

                    <!-- Certificates (Reverse Proxy Only) -->
                    <div v-if="form.is_reverse" class="bg-slate-900/30 p-4 rounded-xl border border-slate-700/50">
                        <h3 class="text-xs font-black text-purple-500 uppercase tracking-[0.2em] mb-4">Reverse Proxy Certificates (PEM)</h3>
                        <textarea v-model="form.cert_pem" rows="4" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white font-mono text-[10px] focus:border-purple-500/50 focus:outline-none" placeholder="-----BEGIN CERTIFICATE-----..."></textarea>
                    </div>

                    <!-- Settings & Extra -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Remark Prefix</label>
                            <input v-model="form.remark_prefix" type="text" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-white text-sm">
                        </div>
                        <div class="flex items-center gap-4 pt-6">
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" v-model="form.is_reverse" class="w-5 h-5 bg-slate-900 rounded-lg border-slate-700 text-purple-600 focus:ring-purple-500/20">
                                <span class="text-sm font-bold text-purple-400">Reverse Proxy</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" v-model="form.is_template" class="w-5 h-5 bg-slate-900 rounded-lg border-slate-700 text-yellow-500 focus:ring-yellow-500/20">
                                <span class="text-sm font-bold text-yellow-500">Template</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" v-model="form.is_active" class="w-5 h-5 bg-slate-900 rounded-lg border-slate-700 text-green-600 focus:ring-green-500/20">
                                <span class="text-sm font-bold text-green-500">Active</span>
                            </label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Extra JSON (Advanced Overrides)</label>
                        <textarea v-model="extraRaw" rows="4" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white font-mono text-[10px] focus:border-blue-500/50 focus:outline-none transition-colors" placeholder='{"headers": {"User-Agent": "..."}}'></textarea>
                        <p v-if="jsonError" class="text-red-400 text-[10px] mt-1 font-bold uppercase tracking-tight">Invalid JSON: {{ jsonError }}</p>
                    </div>
                </div>
                <div class="p-6 border-t border-slate-700 bg-slate-800/50 flex gap-3">
                    <button @click="saveHost()" class="flex-2 bg-blue-600 hover:bg-blue-500 active:scale-95 text-white py-3 rounded-xl font-bold transition-all shadow-lg shadow-blue-600/20">Save Configuration</button>
                    <button @click="showModal = false" class="flex-1 bg-slate-700 hover:bg-slate-600 py-3 rounded-xl font-bold transition-all">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';

const hosts = ref([]);
const marzbanHosts = ref([]);
const showModal = ref(false);
const editingId = ref(null);
const extraRaw = ref('{}');
const jsonError = ref('');

const form = ref({
    name: '', address: '', port: 2096, sni: '', host: '', path: '/',
    mode: 'packet-up', security: 'tls', insecure: false, alpn: 'h2',
    extra: {}, type: 'direct', is_active: true, is_template: false, remark_prefix: 'IDN',
    download_address: '', download_port: null, download_sni: '', is_reverse: false,
    cert_pem: '', pcs: ''
});

onMounted(() => {
    fetchHosts();
});

const fetchHosts = async () => {
    const response = await axios.get('/sub/admin/hosts');
    hosts.value = response.data.hosts;
    marzbanHosts.value = response.data.marzban_hosts;
};

const openModal = () => {
    editingId.value = null;
    extraRaw.value = JSON.stringify({
        headers: { "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36" },
        xPaddingBytes: "100-500",
        noGRPCHeader: false,
        scMaxEachPostBytes: "1000000-2000000",
        scMinPostsIntervalMs: "50-150",
        xmux: { maxConcurrency: "8-16", maxConnections: 0, cMaxReuseTimes: "10-20", hMaxRequestTimes: "100-300", hMaxReusableSecs: "120-240", hKeepAlivePeriod: 0 }
    }, null, 2);
    
    form.value = {
        name: '', address: '', port: 2096, sni: '', host: '', path: '/',
        mode: 'packet-up', security: 'tls', insecure: true, alpn: 'h2',
        extra: {}, type: 'direct', is_active: true, is_template: false, remark_prefix: 'IDN',
        download_address: '', download_port: null, download_sni: '', is_reverse: false,
        cert_pem: '', pcs: ''
    };
    showModal.value = true;
};

const editHost = (host) => {
    editingId.value = host.id;
    form.value = { ...host };
    extraRaw.value = JSON.stringify(host.extra || {}, null, 2);
    showModal.value = true;
};

const deleteHost = async (id) => {
    if (confirm('Delete this host?')) {
        await axios.delete('/sub/admin/hosts/' + id);
        fetchHosts();
    }
};

const saveHost = async () => {
    try {
        const extra = JSON.parse(extraRaw.value);
        form.value.extra = extra;
        form.value.host = form.value.sni; // Sync host with sni for simplicity
        
        if (editingId.value) {
            await axios.put('/sub/admin/hosts/' + editingId.value, form.value);
        } else {
            await axios.post('/sub/admin/hosts', form.value);
        }
        
        showModal.value = false;
        fetchHosts();
    } catch (e) {
        if (e instanceof SyntaxError) {
            jsonError.value = e.message;
        } else {
            alert('Error saving host: ' + (e.response?.data?.message || e.message));
        }
    }
};

watch(extraRaw, (val) => {
    try {
        JSON.parse(val);
        jsonError.value = '';
    } catch (e) {
        jsonError.value = e.message;
    }
});
</script>
