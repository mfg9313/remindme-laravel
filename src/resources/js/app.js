import './bootstrap';
import Vue from 'vue';
import App from './App.vue';
import router from './router';
import apiClient from './plugins/axios';

Vue.prototype.$api = apiClient;

new Vue({
    router,
    render: h => h(App),
}).$mount('#app');
