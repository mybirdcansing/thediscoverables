"use strict"
import "./main.css";

import {router} from './router';
import {store} from './store/store';
import App from './components/App.vue';


new Vue({
    router,
    store,
    el: '#app',
    render: h => h(App)
});
