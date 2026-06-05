<template>
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Host Manager (XHTTP/VLESS)</h1>
            <button @click="openModal()" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded">Add Host</button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="host in hosts" :key="host.id" class="bg-slate-800 p-4 rounded-lg shadow-lg border border-slate-700">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="font-bold text-lg text-blue-400">{{ host.name }}</h3>
                    <span :class="host.is_active ? 'bg-green-900 text-green-300' : 'bg-red-900 text-red-300'" class="text-xs px-2 py-1 rounded">
                        {{ host.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <p class="text-sm text-slate-400 mb-1">Address: <span class="text-slate-200">{{ host.address }}:{{ host.port }}</span></p>
                <p class="text-sm text-slate-400 mb-1">Type: <span class="text-slate-200 uppercase">{{ host.type }}</span></p>
                <p class="text-sm text-slate-400 mb-4">Path: <span class="text-slate-200">{{ host.path }}</span></p>
                
                <div class="flex gap-2">
                    <button @click="editHost(host)" class="flex-1 bg-slate-700 hover:bg-slate-600 py-1 rounded text-sm">Edit</button>
                    <button @click="deleteHost(host.id)" class="flex-1 bg-red-900/50 hover:bg-red-900 py-1 rounded text-sm text-red-200">Delete</button>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black/80 flex items-center justify-center p-4 z-50">
            <div class="bg-slate-800 w-full max-w-2xl rounded-xl shadow-2xl border border-slate-700 overflow-hidden max-h-[90vh] flex flex-col">
                <div class="p-4 border-b border-slate-700 flex justify-between items-center">
                    <h2 class="text-xl font-bold">{{ editingId ? 'Edit Host' : 'Add New Host' }}</h2>
                    <button @click="showModal = false" class="text-slate-400 hover:text-white">&times;</button>
                </div>
                <div class="p-6 overflow-y-auto">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm text-slate-400 mb-1">Name</label>
                            <input v-model="form.name" type="text" class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-white">
                        </div>
                        <div>
                            <label class="block text-sm text-slate-400 mb-1">Type</label>
                            <select v-model="form.type" class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-white">
                                <option value="direct">Direct</option>
                                <option value="reverse">Reverse</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm text-slate-400 mb-1">Address</label>
                            <input v-model="form.address" type="text" class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-white">
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm text-slate-400 mb-1">Port</label>
                            <input v-model.number="form.port" type="number" class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm text-slate-400 mb-1">SNI / Host</label>
                            <input v-model="form.sni" type="text" placeholder="Same as address if empty" class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-white">
                        </div>
                        <div>
                            <label class="block text-sm text-slate-400 mb-1">Path</label>
                            <input v-model="form.path" type="text" class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-white">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm text-slate-400 mb-1">Extra JSON (Settings)</label>
                        <textarea v-model="extraRaw" rows="8" class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-white font-mono text-xs" placeholder='{"headers": {"User-Agent": "..."}}'></textarea>
                        <p v-if="jsonError" class="text-red-400 text-xs mt-1">Invalid JSON: {{ jsonError }}</p>
                    </div>

                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" v-model="form.insecure" class="w-4 h-4 bg-slate-900 rounded border-slate-700">
                            <span class="text-sm">Allow Insecure</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" v-model="form.is_active" class="w-4 h-4 bg-slate-900 rounded border-slate-700">
                            <span class="text-sm">Active</span>
                        </label>
                    </div>
                </div>
                <div class="p-4 border-t border-slate-700 flex gap-3">
                    <button @click="saveHost()" class="flex-1 bg-blue-600 hover:bg-blue-700 py-2 rounded font-bold">Save Host</button>
                    <button @click="showModal = false" class="flex-1 bg-slate-700 hover:bg-slate-600 py-2 rounded">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';

const hosts = ref([]);
const showModal = ref(false);
const editingId = ref(null);
const extraRaw = ref('{}');
const jsonError = ref('');

const form = ref({
    name: '',
    address: '',
    port: 2096,
    sni: '',
    host: '',
    path: '/',
    mode: 'packet-up',
    security: 'tls',
    insecure: false,
    alpn: 'h2',
    extra: {},
    type: 'direct',
    is_active: true,
    remark_prefix: 'IDN'
});

onMounted(() => {
    fetchHosts();
});

const fetchHosts = async () => {
    const response = await axios.get('/sub/admin/hosts');
    hosts.value = response.data;
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
        extra: {}, type: 'direct', is_active: true, remark_prefix: 'IDN'
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
