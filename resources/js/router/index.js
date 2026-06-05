import { createRouter, createWebHistory } from 'vue-router';
import Dashboard from '../components/Dashboard.vue';

const routes = [
    {
        path: '/idn',
        name: 'Dashboard',
        component: Dashboard
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

export default router;
