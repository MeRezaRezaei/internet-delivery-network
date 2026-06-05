import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

export function useCentrifugo() {
    const traffic = ref([]);
    const logs = ref([]);
    let trafficInterval = null;
    let logsInterval = null;
    let lastLogId = '0';

    onMounted(() => {
        // Poll traffic every 3 seconds
        trafficInterval = setInterval(async () => {
            try {
                const res = await axios.get('/idn/api/traffic');
                if (res.data && res.data.data) {
                    // Update traffic with the array of node traffic data
                    traffic.value = res.data.data;
                }
            } catch (err) {
                console.error("Failed to fetch traffic", err);
            }
        }, 3000);

        // Poll logs every 2 seconds
        logsInterval = setInterval(async () => {
            try {
                const res = await axios.get(`/idn/api/logs?last_id=${lastLogId}`);
                if (res.data && res.data.logs) {
                    if (res.data.logs.length > 0) {
                        res.data.logs.forEach(log => logs.value.push(log));
                        // Keep logs array reasonably sized
                        if (logs.value.length > 200) {
                            logs.value = logs.value.slice(logs.value.length - 200);
                        }
                    }
                    lastLogId = res.data.last_id;
                }
            } catch (err) {
                console.error("Failed to fetch logs", err);
            }
        }, 2000);
    });

    onUnmounted(() => {
        if (trafficInterval) clearInterval(trafficInterval);
        if (logsInterval) clearInterval(logsInterval);
    });

    return {
        traffic,
        logs
    };
}
