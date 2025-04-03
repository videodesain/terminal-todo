import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
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
                short_name: 'Terminal Todo',
                theme_color: '#ffffff',
                icons: [
                    {
                        src: '/img/icons/android-chrome-192x192.png',
                        sizes: '192x192',
                        type: 'image/png',
                    },
                    {
                        src: '/img/icons/android-chrome-512x512.png',
                        sizes: '512x512',
                        type: 'image/png',
                    },
                ],
            },
            workbox: {
                globPatterns: ['**/*.{js,css,html,ico,png,svg,woff2}']
            }
        })
    ],
    resolve: {
        alias: {
            '@': '/resources/js'
        }
    },
    build: {
        outDir: 'public/build',
        emptyOutDir: true,
        manifest: true,
        chunkSizeWarningLimit: 1024,
        rollupOptions: {
            external: [
                'fsevents',
                /^node:/,
                'crypto',
                'fs',
                'path',
                'url',
                'module',
                'os'
            ],
            output: {
                manualChunks: {
                    vendor: ['vue', '@inertiajs/vue3']
                }
            }
        },
        target: 'esnext',
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true,
                drop_debugger: true
            }
        }
    },
    optimizeDeps: {
        include: ['vue', '@inertiajs/vue3']
    }
});
