<template>
  <div class="dashboard-container p-4">
    <div class="header d-flex justify-content-between align-items-center mb-4">
      <h4 class="mb-0 fw-bold text-uppercase tracking-wider">IDN Control Plane Dashboard</h4>
      <span class="badge bg-light text-dark border">
        ↓ {{ rx_mbps }} Mbps | ↑ {{ tx_mbps }} Mbps
      </span>
    </div>

    <div class="row">
      <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
          <div class="card-header bg-dark text-white">
            <h6 class="mb-0">Traffic Metrics</h6>
          </div>
          <div class="card-body bg-dark text-white p-3">
            <div class="traffic-stats d-flex gap-3">
              <div v-for="(node, index) in traffic" :key="index" class="p-2 border border-secondary rounded">
                <strong>{{ node.node_name }}</strong>: 
                <span class="text-success">↓ {{ node.rx_mbps }}</span> / 
                <span class="text-danger">↑ {{ node.tx_mbps }}</span>
              </div>
              <div v-if="traffic.length === 0" class="text-muted">No real-time traffic data yet...</div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card shadow-sm mb-4">
          <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">System Logs (Live)</h6>
            <span class="badge bg-danger pulse-indicator">Live</span>
          </div>
          <div class="card-body bg-black p-0 log-viewer" ref="logContainer">
            <div v-for="(log, index) in logs" :key="index" class="log-entry px-3 py-1 font-monospace small border-bottom border-secondary">
              <span class="text-secondary">[{{ log.timestamp }}]</span> 
              <span class="text-info">[{{ log.node_name }}]</span>: 
              <span class="text-white">{{ log.message }}</span>
            </div>
            <div v-if="logs.length === 0" class="px-3 py-2 text-muted">Awaiting log streams...</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch, nextTick } from 'vue';
import { useCentrifugo } from '../composables/useCentrifugo';

const { traffic, logs } = useCentrifugo();
const logContainer = ref(null);

const rx_mbps = computed(() => {
  return traffic.value.reduce((acc, curr) => acc + (parseFloat(curr.rx_mbps) || 0), 0).toFixed(2);
});

const tx_mbps = computed(() => {
  return traffic.value.reduce((acc, curr) => acc + (parseFloat(curr.tx_mbps) || 0), 0).toFixed(2);
});

watch(() => logs.value.length, async () => {
  await nextTick();
  if (logContainer.value) {
    logContainer.value.scrollTop = logContainer.value.scrollHeight;
  }
});
</script>

<style scoped>
.log-viewer {
  height: 400px;
  overflow-y: auto;
}
.log-entry:hover {
  background-color: #1a1a1a;
}
.pulse-indicator {
  animation: pulse 2s infinite;
}
@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.5; }
  100% { opacity: 1; }
}
</style>
