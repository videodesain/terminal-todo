import "./bootstrap";
import "../css/app.css";

import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import axios from 'axios';
import Toaster from "@meforma/vue-toaster";
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';

// Setup Axios CSRF token
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Interceptor untuk refresh halaman jika mendapat 419
axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 419) {
            window.location.reload();
            return Promise.reject(error);
        }
        return Promise.reject(error);
    }
);

// Inisialisasi Dark Mode
if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark')
} else {
    document.documentElement.classList.remove('dark')
}

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

// Pastikan window.route tersedia
if (typeof window.route === 'undefined') {
    console.error('Ziggy routes are not loaded. Make sure @routes is included in your blade template.');
}

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: async (name) => {
        try {
            const page = await resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue'));
            return page;
        } catch (error) {
            console.error(`Failed to load page component: ${name}`, error);
            throw error;
        }
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        
        // Install plugins
        app.use(plugin);
        app.use(Toaster, {
            position: 'top-right',
            duration: 3000
        });

        // Add global mixin for route function
        app.mixin({
            methods: {
                route: (...args) => {
                    if (typeof window.route === 'undefined') {
                        console.error('Route function not found');
                        return '#';
                    }
                    return window.route(...args);
                }
            }
        });

        // Mount the app
        app.mount(el);

        return app;
    },
    progress: {
        color: "#4B5563",
    },
});
