import List from './components/layout/List.vue';
import VueRouter from 'vue-router';
Vue.use(VueRouter);

export const router = new VueRouter({
    routes: [
        { path: '/foo', component: { template: '<div>foo</div>' } },
        { path: '/bar', component: List }
    ],
    mode: 'history',
});
