import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import { VitePWA } from 'vite-plugin-pwa';
import path from 'path';
import fs from 'fs/promises';
import { existsSync } from 'fs';

// Helper untuk memastikan manifest.json selalu tersedia setelah build
function copyManifest() {
    return {
        name: 'copy-manifest',
        closeBundle: async () => {
            const srcPath = 'public/build/.vite/manifest.json';
            const destPath = 'public/build/manifest.json';
            
            try {
                if (existsSync(srcPath)) {
                    const data = await fs.readFile(srcPath, 'utf8');
                    await fs.writeFile(destPath, data);
                    console.log('✓ Manifest disalin otomatis oleh plugin Vite');
                }
            } catch (err) {
                console.error('Error saat menyalin manifest:', err);
            }
        },
    };
}

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
                name: process.env.APP_NAME || 'Terminal Todo',
                short_name: 'Terminal',
                description: 'Aplikasi pengelolaan sosial media dan todo list',
                theme_color: '#4F46E5',
                background_color: '#121212',
                display: 'standalone',
                orientation: 'portrait',
                start_url: '/',
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
                            cacheName: 'google-fonts-cache',
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
                            cacheName: 'gstatic-fonts-cache',
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
                            cacheName: 'cloudflare-cache',
                            expiration: {
                                maxEntries: 10,
                                maxAgeSeconds: 60 * 60 * 24 * 30 // <== 30 days
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
        }),
        copyManifest(),
    ],
    build: {
        outDir: 'public/build',
        assetsDir: '',
        manifest: true,
        rollupOptions: {
            input: ['resources/css/app.css', 'resources/js/app.js']
        },
        emptyOutDir: true,
        chunkSizeWarningLimit: 1000,
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
    }
});
