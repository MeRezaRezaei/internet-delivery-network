import { ref, onMounted, onUnmounted } from 'vue';
import { Centrifuge } from 'centrifuge';

export function useCentrifugo() {
    const traffic = ref([]);
    const logs = ref([]);
    let centrifuge = null;

    onMounted(() => {
        centrifuge = new Centrifuge('ws://localhost:8001/connection/websocket');

        const trafficSub = centrifuge.newSubscription('traffic');
        trafficSub.on('publication', function (ctx) {
            traffic.value.push(ctx.data);
        });
        trafficSub.subscribe();

        const logsSub = centrifuge.newSubscription('logs');
        logsSub.on('publication', function (ctx) {
            logs.value.push(ctx.data);
        });
        logsSub.subscribe();

        centrifuge.connect();
    });

    onUnmounted(() => {
        if (centrifuge) {
            centrifuge.disconnect();
        }
    });

    return {
        traffic,
        logs
    };
}
