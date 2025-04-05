import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import { VitePWA } from 'vite-plugin-pwa';
import path from 'path';

// Definisikan versi aplikasi - increment ketika ada update
const APP_VERSION = '1.0.0';

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
            includeAssets: ['favicon.ico', 'robots.txt'],
            manifest: {
                name: 'Terminal Todo App',
                short_name: 'Terminal',
                description: 'Aplikasi pengelolaan sosial media dan todo list',
                theme_color: '#4F46E5',
                background_color: '#121212',
                display: 'standalone',
                orientation: 'portrait',
                start_url: '/',
                version: APP_VERSION,
                icons: [
                    {
                        src: '/icons/pwa-192x192.png',
                        sizes: '192x192',
                        type: 'image/png'
                    },
                    {
                        src: '/icons/pwa-512x512.png',
                        sizes: '512x512',
                        type: 'image/png'
                    },
                    {
                        src: '/icons/pwa-512x512.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'any maskable'
                    }
                ],
            },
            workbox: {
                globPatterns: ['**/*.{js,css,html,ico,png,svg,webp,jpg,jpeg}'],
                runtimeCaching: [
                    {
                        urlPattern: /^https:\/\/fonts\.googleapis\.com\/.*/i,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: `google-fonts-cache-${APP_VERSION}`,
                            expiration: {
                                maxEntries: 10,
                                maxAgeSeconds: 60 * 60 * 24 * 365 // <== 365 days
                            },
                            cacheableResponse: {
                                statuses: [0, 200]
                            }
                        }
                    },
                    {
                        urlPattern: /^https:\/\/fonts\.gstatic\.com\/.*/i,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: `gstatic-fonts-cache-${APP_VERSION}`,
                            expiration: {
                                maxEntries: 10,
                                maxAgeSeconds: 60 * 60 * 24 * 365 // <== 365 days
                            },
                            cacheableResponse: {
                                statuses: [0, 200]
                            }
                        }
                    },
                    {
                        urlPattern: /^https:\/\/cdnjs\.cloudflare\.com\/.*/i,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: `cloudflare-cache-${APP_VERSION}`,
                            expiration: {
                                maxEntries: 10,
                                maxAgeSeconds: 60 * 60 * 24 * 30 // <== 30 days
                            },
                            cacheableResponse: {
                                statuses: [0, 200]
                            }
                        }
                    },
                    // Tambahan: Caching API requests
                    {
                        urlPattern: /\/api\/(?!auth)/i, // Semua API kecuali auth
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: `api-cache-${APP_VERSION}`,
                            expiration: {
                                maxEntries: 50,
                                maxAgeSeconds: 60 * 5 // 5 menit
                            },
                            cacheableResponse: {
                                statuses: [0, 200]
                            }
                        }
                    }
                ]
            },
            devOptions: {
                enabled: true,
                type: 'module',
            },
            // Tambahkan strategi untuk memastikan service worker terupdate
            strategies: 'injectManifest',
            injectManifest: {
                injectionPoint: undefined,
                rollupFormat: 'iife',
            }
        }),
    ],
    build: {
        outDir: 'public/build',
        assetsDir: '',
        manifest: 'manifest.json',
        rollupOptions: {
            input: ['resources/css/app.css', 'resources/js/app.js']
        },
        write: true,
        chunkSizeWarningLimit: 1600,
        emptyOutDir: true
    },
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
        ]
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
    },
    // Tambahkan konfigurasi untuk mendukung Vite v5+
    define: {
        __APP_VERSION__: JSON.stringify(APP_VERSION)
    },
});
