import VueRouter from 'vue-router';
Vue.use(VueRouter);

export const router = new VueRouter({
    routes: [
        { path: '/', component: { template: '<div>music</div>' } },
        { path: '/video', component: { template: '<div>video</div>' } },
        { path: '/about', component: { template: '<div>about</div>' } },
    ],
    mode: 'history',
});
