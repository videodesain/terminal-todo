<?php

return [
    'paths' => [
        'build' => 'build',
        'manifest' => public_path('build/manifest.json'),
        'webmanifest' => public_path('build/manifest.webmanifest'),
    ],
    'dev_server' => [
        'enabled' => env('VITE_DEV_SERVER_ENABLED', false),
        'host' => env('VITE_DEV_SERVER_HOST', 'localhost'),
        'port' => env('VITE_DEV_SERVER_PORT', 5173),
    ],
    'manifest_key' => env('VITE_MANIFEST_KEY', 'terminal-todo'),
]; 