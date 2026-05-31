<template>
  <div class="bg-gray-800 rounded-xl shadow-lg border border-gray-700 overflow-hidden">
    <div class="bg-gray-950 px-5 py-3 border-b border-gray-700 flex justify-between items-center">
      <h2 class="text-lg font-semibold text-gray-200">Tunnel Management</h2>
      <button @click="showCreateModal = true" class="bg-blue-600 hover:bg-blue-500 text-white px-3 py-1 rounded text-sm font-medium transition-colors">
        + New Tunnel
      </div>
    </div>

    <div class="p-4">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-300">
          <thead class="bg-gray-900 text-gray-400 uppercase text-xs">
            <tr>
              <th class="px-4 py-2">Tag</th>
              <th class="px-4 py-2">Nodes</th>
              <th class="px-4 py-2">Port/Protocol</th>
              <th class="px-4 py-2">Transport</th>
              <th class="px-4 py-2">Status</th>
              <th class="px-4 py-2">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-700">
            <tr v-for="tunnel in tunnels" :key="tunnel.id" class="hover:bg-gray-750 transition-colors">
              <td class="px-4 py-3 font-mono text-xs">{{ tunnel.tag }}</td>
              <td class="px-4 py-3">
                <span class="text-blue-400">{{ tunnel.source_node_name }}</span> 
                → 
                <span class="text-purple-400">{{ tunnel.target_node_name }}</span>
              </td>
              <td class="px-4 py-3">
                <span class="bg-gray-700 px-2 py-0.5 rounded text-[10px]">{{ tunnel.port }}</span>
                <span class="ml-2">{{ tunnel.protocol }}</span>
              </td>
              <td class="px-4 py-3">
                <span class="text-gray-400">{{ tunnel.transport_type || 'tcp' }}</span>
              </td>
              <td class="px-4 py-3">
                <span :class="tunnel.is_active ? 'text-green-500' : 'text-red-500'">
                  ● {{ tunnel.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-4 py-3 flex gap-3">
                <button @click="verifyTunnel(tunnel)" :disabled="verifyingId === tunnel.id" class="text-blue-400 hover:text-blue-300 disabled:opacity-50">
                  {{ verifyingId === tunnel.id ? '...' : 'Verify' }}
                </button>
                <button @click="deleteTunnel(tunnel.id)" class="text-red-400 hover:text-red-300">Delete</button>
              </td>
            </tr>
            <tr v-if="tunnels.length === 0">
              <td colspan="6" class="px-4 py-8 text-center text-gray-500 italic">No tunnels configured.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Modal (Simplified for brevity) -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
      <div class="bg-gray-800 border border-gray-700 rounded-xl shadow-2xl w-full max-w-lg overflow-hidden">
        <div class="bg-gray-950 px-6 py-4 border-b border-gray-700 flex justify-between items-center">
          <h3 class="text-xl font-bold">Create New Tunnel</h3>
          <button @click="showCreateModal = false" class="text-gray-400 hover:text-white">✕</button>
        </div>
        
        <form @submit.prevent="createTunnel" class="p-6 space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Source Node</label>
              <select v-model="form.source_node_id" class="w-full bg-gray-900 border border-gray-700 rounded p-2 text-sm">
                <option v-for="node in nodes" :key="node.id" :value="node.id">{{ node.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Target Node</label>
              <select v-model="form.target_node_id" class="w-full bg-gray-900 border border-gray-700 rounded p-2 text-sm">
                <option v-for="node in nodes" :key="node.id" :value="node.id">{{ node.name }}</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Port</label>
              <input v-model="form.port" type="number" class="w-full bg-gray-900 border border-gray-700 rounded p-2 text-sm">
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Protocol</label>
              <select v-model="form.protocol" class="w-full bg-gray-900 border border-gray-700 rounded p-2 text-sm">
                <option value="vless">VLESS</option>
                <option value="trojan">Trojan</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Transport</label>
            <select v-model="form.transport" class="w-full bg-gray-900 border border-gray-700 rounded p-2 text-sm">
              <option value="tcp">TCP (Direct)</option>
              <option value="xhttp">XHTTP (Modern)</option>
              <option value="splithttp">Split-HTTP (CDN Optimized)</option>
              <option value="httpupgrade">HTTP Upgrade</option>
              <option value="grpc">gRPC</option>
            </select>
          </div>

          <div v-if="['xhttp', 'splithttp', 'httpupgrade'].includes(form.transport)" class="p-3 bg-gray-900/50 rounded-lg border border-gray-700 space-y-3">
             <div>
               <label class="block text-[10px] font-bold text-gray-600 uppercase mb-1">Path</label>
               <input v-model="form.transport_params.path" type="text" class="w-full bg-gray-800 border border-gray-700 rounded p-1.5 text-xs font-mono">
             </div>
             <div v-if="form.transport === 'splithttp' || form.transport === 'httpupgrade'">
               <label class="block text-[10px] font-bold text-gray-600 uppercase mb-1">Host</label>
               <input v-model="form.transport_params.host" type="text" class="w-full bg-gray-800 border border-gray-700 rounded p-1.5 text-xs font-mono">
             </div>
          </div>

          <div class="pt-4 border-t border-gray-700 flex justify-end gap-3">
            <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-sm text-gray-400 hover:text-white">Cancel</button>
            <button type="submit" :disabled="loading" class="bg-blue-600 hover:bg-blue-500 px-6 py-2 rounded text-sm font-bold transition-colors disabled:opacity-50">
              {{ loading ? 'Provisioning...' : 'Provision Tunnel' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Verification Modal -->
    <div v-if="showVerifyModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
      <div class="bg-gray-800 border border-gray-700 rounded-xl shadow-2xl w-full max-w-lg overflow-hidden">
        <div class="bg-gray-950 px-6 py-4 border-b border-gray-700 flex justify-between items-center">
          <h3 class="text-xl font-bold text-gray-200">Tunnel Verification</h3>
          <button @click="showVerifyModal = false" class="text-gray-400 hover:text-white">✕</button>
        </div>
        
        <div class="p-6 space-y-4">
          <div v-if="verificationResult">
             <div class="flex justify-between items-center p-3 bg-gray-900 rounded-lg border border-gray-700">
               <span class="text-sm font-medium">Source Node Config</span>
               <span :class="verificationResult.source.success ? 'text-green-500' : 'text-red-500'" class="text-xs font-bold">
                 {{ verificationResult.source.success ? '✓ VALID' : '✗ INVALID' }}
               </span>
             </div>
             <div v-if="!verificationResult.source.success" class="text-[10px] text-red-400 font-mono bg-red-950/30 p-2 rounded border border-red-900/50 max-h-32 overflow-y-auto">
                {{ verificationResult.source.output }}
             </div>

             <div class="flex justify-between items-center p-3 bg-gray-900 rounded-lg border border-gray-700">
               <span class="text-sm font-medium">Target Node Config</span>
               <span :class="verificationResult.target.success ? 'text-green-500' : 'text-red-500'" class="text-xs font-bold">
                 {{ verificationResult.target.success ? '✓ VALID' : '✗ INVALID' }}
               </span>
             </div>
             <div v-if="!verificationResult.target.success" class="text-[10px] text-red-400 font-mono bg-red-950/30 p-2 rounded border border-red-900/50 max-h-32 overflow-y-auto">
                {{ verificationResult.target.output }}
             </div>

             <div class="flex justify-between items-center p-3 bg-gray-900 rounded-lg border border-gray-700">
               <span class="text-sm font-medium">Target Reachability</span>
               <span :class="verificationResult.reachability ? 'text-green-500' : 'text-red-500'" class="text-xs font-bold">
                 {{ verificationResult.reachability ? '✓ REACHABLE' : '✗ UNREACHABLE' }}
               </span>
             </div>
          </div>
        </div>
        
        <div class="bg-gray-950 px-6 py-4 border-t border-gray-700 flex justify-end">
          <button @click="showVerifyModal = false" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2 rounded text-sm font-bold transition-colors">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const nodes = ref([]);
const tunnels = ref([]);
const showCreateModal = ref(false);
const loading = ref(false);

const verificationResult = ref(null);
const showVerifyModal = ref(false);
const verifyingId = ref(null);

const form = ref({
  source_node_id: null,
  target_node_id: null,
  port: 443,
  protocol: 'vless',
  transport: 'tcp',
  transport_params: {
    path: '/',
    host: '',
    mode: 'streaming'
  }
});

const fetchData = async () => {
  try {
    const res = await axios.get('/idn/api/tunnels');
    tunnels.value = res.data.tunnels;
    nodes.value = res.data.nodes;
  } catch (err) {
    console.error("Failed to fetch tunnel data", err);
  }
};

const createTunnel = async () => {
  loading.value = true;
  try {
    await axios.post('/idn/tunnels', {
        ...form.value,
        tag: `tunnel-${Math.random().toString(36).substring(7)}`
    });
    showCreateModal.value = false;
    await fetchData();
  } catch (err) {
    alert("Provisioning failed: " + (err.response?.data?.message || err.message));
  } finally {
    loading.value = false;
  }
};

const verifyTunnel = async (tunnel) => {
  verifyingId.value = tunnel.id;
  try {
    const res = await axios.post(`/idn/tunnels/${tunnel.id}/verify`);
    verificationResult.value = res.data.results;
    showVerifyModal.value = true;
  } catch (err) {
    alert("Verification failed: " + (err.response?.data?.message || err.message));
  } finally {
    verifyingId.value = null;
  }
};

const deleteTunnel = async (id) => {
  if (!confirm("Are you sure? This will remove the tunnel and its configuration from the node.")) return;
  try {
    await axios.delete(`/idn/tunnels/${id}`);
    await fetchData();
  } catch (err) {
    console.error("Delete failed", err);
  }
};

onMounted(fetchData);
</script>
