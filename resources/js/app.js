import './bootstrap';
import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import { initTheme } from './theme';

const appRoot = document.getElementById('app');
if (appRoot) {
    (async () => {
        await initTheme();
        createApp(App).use(router).mount(appRoot);
    })();
}

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();
