import { createRouter, createWebHistory } from 'vue-router';
import HostManager from '../components/HostManager.vue';

const routes = [
    {
        path: '/sub/admin',
        name: 'HostManager',
        component: HostManager
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

export default router;
