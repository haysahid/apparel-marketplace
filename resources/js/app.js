import './bootstrap';
import '../css/app.css';
import 'aos/dist/aos.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { createPinia } from 'pinia'
import AOS from 'aos';

// My plugins
import formatDate from './plugins/date-formatter';
import { formatCurrency } from './plugins/number-formatter';


AOS.init();

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const pinia = createPinia()

        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(pinia);

        app.config.globalProperties.$formatDate = formatDate;
        app.config.globalProperties.$formatCurrency = formatCurrency;
        app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
