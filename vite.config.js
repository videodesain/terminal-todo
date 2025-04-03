import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import { VitePWA } from 'vite-plugin-pwa';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            buildDirectory: 'build'
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        VitePWA({
            registerType: 'autoUpdate',
            manifest: {
                name: 'Terminal Todo',
                short_name: 'Terminal',
                theme_color: '#ffffff',
                icons: [],
                background_color: '#ffffff',
                display: 'standalone',
            },
        }),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
    optimizeDeps: {
        include: [
            '@inertiajs/vue3',
            '@meforma/vue-toaster',
            'axios',
            'vue'
        ],
        exclude: ['ziggy-js']
    },
    build: {
        outDir: 'public/build',
        assetsDir: '',
        manifest: true,
        rollupOptions: {
            input: {
                app: 'resources/js/app.js',
                style: 'resources/css/app.css'
            },
            output: {
                manualChunks: {
                    vendor: ['vue', '@inertiajs/vue3', 'axios']
                },
                assetFileNames: (assetInfo) => {
                    if (assetInfo.name.endsWith('.css')) {
                        return 'css/[name][extname]';
                    }
                    return 'assets/[name][extname]';
                },
                chunkFileNames: 'js/[name].js',
                entryFileNames: 'js/[name].js'
            }
        }
    },
    server: {
        port: 5175,
        strictPort: true,
        host: '127.0.0.1',
        hmr: {
            host: 'localhost',
            protocol: 'ws',
        },
        watch: {
            usePolling: true,
        }
    }
});
