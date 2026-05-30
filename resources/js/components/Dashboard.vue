<template>
  <div class="p-4 md:p-6 lg:p-8 min-h-screen bg-gray-900 text-white font-sans">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
      <h1 class="text-2xl md:text-3xl font-bold tracking-wider text-gray-100 uppercase">IDN Control Plane</h1>
      <div class="bg-gray-800 border border-gray-700 px-4 py-2 rounded-lg shadow flex gap-4 text-sm font-medium">
        <span class="text-green-400">↓ {{ rx_mbps }} Mbps</span> 
        <span class="text-gray-500">|</span>
        <span class="text-red-400">↑ {{ tx_mbps }} Mbps</span>
      </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      
      <!-- Left Column (Charts & Failovers) -->
      <div class="lg:col-span-2 space-y-6 flex flex-col">
        
        <!-- Traffic Chart -->
        <div class="bg-gray-800 rounded-xl shadow-lg border border-gray-700 overflow-hidden flex-1 min-h-[350px]">
          <div class="bg-gray-950 px-5 py-3 border-b border-gray-700 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-200">Traffic Visualization (Live)</h2>
            <div class="text-xs text-gray-500 flex items-center gap-2">
              <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
              </span>
              gRPC Sync
            </div>
          </div>
          <div class="p-4 h-[300px] w-full relative">
            <LineChart v-if="chartData.labels.length > 0" :data="chartData" :options="chartOptions" />
            <div v-else class="flex h-full items-center justify-center text-gray-500 italic">
              Awaiting gRPC traffic stats...
            </div>
          </div>
        </div>

        <!-- Node Traffic Breakdown -->
        <div class="bg-gray-800 rounded-xl shadow-lg border border-gray-700 overflow-hidden">
           <div class="bg-gray-950 px-5 py-3 border-b border-gray-700">
             <h2 class="text-lg font-semibold text-gray-200">Node Breakdown</h2>
           </div>
           <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
             <div v-for="(node, index) in traffic" :key="index" class="p-3 bg-gray-900 border border-gray-700 rounded-lg flex flex-col justify-between">
               <span class="font-bold text-gray-300 truncate">{{ node.node_name }}</span>
               <div class="flex justify-between mt-2 text-sm">
                 <span class="text-green-400">↓ {{ node.rx_mbps }}</span>
                 <span class="text-red-400">↑ {{ node.tx_mbps }}</span>
               </div>
             </div>
           </div>
        </div>
        
        <!-- Failover Notification Feed -->
        <div class="bg-gray-800 rounded-xl shadow-lg border border-gray-700 overflow-hidden">
          <div class="bg-gray-950 px-5 py-3 border-b border-gray-700 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-200">Failover Notification Feed</h2>
            <span class="text-xs bg-red-900/50 text-red-400 border border-red-800 px-2 py-0.5 rounded-full">Automated</span>
          </div>
          <div class="p-4 max-h-60 overflow-y-auto space-y-3">
            <transition-group name="list">
              <div v-for="(failover, idx) in failovers" :key="idx" class="p-3 bg-red-900/30 border border-red-800 rounded-lg flex items-start gap-3 transition-all duration-300">
                <span class="text-red-500 font-bold mt-0.5">⚠</span>
                <div>
                  <div class="text-sm font-medium text-red-200">Failover Triggered: <span class="font-bold">{{ failover.node }}</span></div>
                  <div class="text-xs text-red-400 mt-1">Traffic re-routed to backup via Control Plane at {{ failover.time }}</div>
                </div>
              </div>
            </transition-group>
            <div v-if="failovers.length === 0" class="text-sm text-gray-500 italic p-2 text-center border border-dashed border-gray-700 rounded-lg">
              No recent failovers detected. Fleet is healthy.
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column (Logs) -->
      <div class="lg:col-span-1">
        <div class="bg-gray-800 rounded-xl shadow-lg border border-gray-700 overflow-hidden h-full flex flex-col min-h-[600px]">
          <div class="bg-gray-950 px-5 py-3 border-b border-gray-700 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-200">System Logs</h2>
            <span class="flex h-3 w-3 relative">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyan-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-3 w-3 bg-cyan-500"></span>
            </span>
          </div>
          <div class="p-0 bg-black flex-1 overflow-y-auto font-mono text-xs" ref="logContainer">
            <div v-for="(log, index) in logs" :key="index" class="px-4 py-2 border-b border-gray-800 hover:bg-gray-900 transition-colors">
              <div class="text-gray-500 text-[10px] mb-1">{{ log.timestamp }}</div>
              <div>
                <span class="text-cyan-500 font-bold">[{{ log.node || log.node_name || 'sys' }}]</span> 
                <span :class="{'text-red-400': log.level === 'ERROR', 'text-yellow-400': log.level === 'WARNING', 'text-gray-300': !['ERROR', 'WARNING'].includes(log.level)}">
                   {{ log.message }}
                </span>
              </div>
            </div>
            <div v-if="logs.length === 0" class="px-4 py-6 text-gray-500 italic text-center">
              Awaiting log streams...
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch, nextTick } from 'vue';
import { useCentrifugo } from '../composables/useCentrifugo';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js';
import { Line as LineChart } from 'vue-chartjs';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
);

const { traffic, logs } = useCentrifugo();
const logContainer = ref(null);

const rx_mbps = computed(() => {
  return traffic.value.reduce((acc, curr) => acc + (parseFloat(curr.rx_mbps) || 0), 0).toFixed(2);
});

const tx_mbps = computed(() => {
  return traffic.value.reduce((acc, curr) => acc + (parseFloat(curr.tx_mbps) || 0), 0).toFixed(2);
});

// Failovers logic
const failovers = ref([]);

// Chart data state
const chartData = ref({
  labels: [],
  datasets: []
});

const maxDataPoints = 20;
const historyRx = ref([]);
const historyTx = ref([]);
const historyLabels = ref([]);

// We watch `traffic` array from our polling.
// Each poll updates `traffic.value` with the LATEST snapshot of all nodes.
watch(traffic, (nodes) => {
  if (!nodes || nodes.length === 0) return;
  
  // Calculate total across all nodes for this snapshot
  const currentRx = nodes.reduce((acc, n) => acc + (parseFloat(n.rx_mbps) || 0), 0);
  const currentTx = nodes.reduce((acc, n) => acc + (parseFloat(n.tx_mbps) || 0), 0);
  const timeLabel = new Date().toLocaleTimeString();

  historyLabels.value.push(timeLabel);
  historyRx.value.push(currentRx);
  historyTx.value.push(currentTx);

  if (historyLabels.value.length > maxDataPoints) {
    historyLabels.value.shift();
    historyRx.value.shift();
    historyTx.value.shift();
  }

  chartData.value = {
    labels: [...historyLabels.value],
    datasets: [
      {
        label: 'Total Downlink (Mbps)',
        data: [...historyRx.value],
        borderColor: '#10B981', // green-500
        backgroundColor: 'rgba(16, 185, 129, 0.15)',
        fill: true,
        tension: 0.4,
        pointRadius: 3
      },
      {
        label: 'Total Uplink (Mbps)',
        data: [...historyTx.value],
        borderColor: '#EF4444', // red-500
        backgroundColor: 'rgba(239, 68, 68, 0.15)',
        fill: true,
        tension: 0.4,
        pointRadius: 3
      }
    ]
  };
}, { deep: true });

// Listen for failovers in logs
watch(logs, (newLogs) => {
  if (newLogs.length === 0) return;
  const latestLog = newLogs[newLogs.length - 1];
  
  if (latestLog && latestLog.message) {
      const msg = latestLog.message.toLowerCase();
      if (msg.includes('failover') || msg.includes('offline') || msg.includes('migrating tunnel')) {
        // Prevent exact duplicates in a short time
        const exists = failovers.value.find(f => f.node === latestLog.node && f.time === new Date(latestLog.timestamp || Date.now()).toLocaleTimeString());
        
        if (!exists) {
            failovers.value.unshift({
              node: latestLog.node || 'unknown',
              time: new Date(latestLog.timestamp || Date.now()).toLocaleTimeString()
            });
            if (failovers.value.length > 5) failovers.value.pop();
        }
      }
  }
}, { deep: true });

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  interaction: {
    mode: 'index',
    intersect: false,
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: {
        color: 'rgba(55, 65, 81, 0.5)', // gray-700 with opacity
        drawBorder: false,
      },
      ticks: {
        color: '#9CA3AF', // gray-400
        callback: function(value) {
            return value + ' Mbps';
        }
      }
    },
    x: {
      grid: {
        display: false
      },
      ticks: {
        color: '#9CA3AF',
        maxTicksLimit: 6
      }
    }
  },
  plugins: {
    legend: {
      labels: {
        color: '#D1D5DB', // gray-300
        usePointStyle: true,
        boxWidth: 8
      }
    },
    tooltip: {
      backgroundColor: 'rgba(17, 24, 39, 0.9)',
      titleColor: '#F3F4F6',
      bodyColor: '#D1D5DB',
      borderColor: '#374151',
      borderWidth: 1,
      padding: 10
    }
  }
};

watch(() => logs.value.length, async () => {
  await nextTick();
  if (logContainer.value) {
    logContainer.value.scrollTop = logContainer.value.scrollHeight;
  }
});
</script>

<style scoped>
.list-enter-active,
.list-leave-active {
  transition: all 0.5s ease;
}
.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateX(-30px);
}
</style>