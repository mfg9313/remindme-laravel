import Vue from 'vue';
import Router from 'vue-router';
import LoginComponent from '../components/LoginComponent.vue';
import HomeComponent from '../components/HomeComponent.vue';
import NotFoundComponent from '../components/NotFoundComponent.vue';

Vue.use(Router);

const routes = [
    { path: '/', redirect: { name: 'login' } },
    { path: '/login', name: 'login', component: LoginComponent },
    {
        path: '/home',
        name: 'home',
        component: HomeComponent,
        meta: { requiresAuth: true },
    },
    { path: '*', name: 'NotFound', component: NotFoundComponent },
];

const router = new Router({
    mode: 'history',
    routes,
});

// Navigation Guard
router.beforeEach((to, from, next) => {
    const isAuthenticated = !!localStorage.getItem('access_token');
    if (to.matched.some(record => record.meta.requiresAuth) && !isAuthenticated) {
        next({ name: 'login' });
    } else {
        next();
    }
});

export default router;
