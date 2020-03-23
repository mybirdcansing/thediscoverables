"use strict"
import regeneratorRuntime from "regenerator-runtime";

import router from './router';
import store from './store/store';
import App from './components/App.vue';

document.addEventListener("touchstart", event => {
    if(event.touches.length > 1) {
        event.preventDefault();
        event.stopPropagation(); // maybe useless
    }
}, {passive: false});

new Vue({
    router,
    store,
    el: '#app',
    render: h => h(App)
});
