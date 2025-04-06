<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title inertia>{{ config('app.name', 'Terminal') }}</title>

    <!-- PWA Meta Tags -->
    <meta name="application-name" content="{{ config('app.name', 'Terminal') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'Terminal') }}">
    <meta name="description" content="Aplikasi pengelolaan sosial media dan todo list">
    <meta name="theme-color" content="#4F46E5">
    <meta name="format-detection" content="telephone=no">
    <meta name="mobile-web-app-capable" content="yes">
    
    <!-- PWA manifest -->
    <link rel="manifest" href="/manifest.json">
    
    <!-- PWA Icons -->
    <link rel="apple-touch-icon" href="/icons/pwa-192x192.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/icons/pwa-192x192.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/icons/pwa-192x192.png">
    <link rel="apple-touch-icon" sizes="167x167" href="/icons/pwa-192x192.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Dynamic Favicon -->
    @if(Cache::has('website_settings') && !empty(Cache::get('website_settings')['site_favicon']))
        <link rel="icon" href="{{ Storage::url(Cache::get('website_settings')['site_favicon']) }}" type="image/x-icon"/>
    @endif

    @routes(['verify' => true])
    
    <!-- Vite Assets with Fallback -->
    @php
    $manifestPath = public_path('build/manifest.json');
    $altManifestPath = public_path('build/.vite/manifest.json');
    
    // Cek keberadaan manifest.json
    $hasManifest = file_exists($manifestPath);
    $hasAltManifest = file_exists($altManifestPath);
    
    // Jika manifest tidak ada di tempat standar tetapi ada di .vite folder, salin ke lokasi standar
    if (!$hasManifest && $hasAltManifest) {
        try {
            if (!is_dir(dirname($manifestPath))) {
                mkdir(dirname($manifestPath), 0755, true);
            }
            copy($altManifestPath, $manifestPath);
            $hasManifest = true;
        } catch (\Exception $e) {
            // Log error
            \Log::error('Gagal menyalin manifest: ' . $e->getMessage());
        }
    }
    
    // Cek apakah build berhasil dengan mencari file CSS dan JS
    $cssExists = !empty(glob(public_path('build') . '/*.css'));
    $jsExists = !empty(glob(public_path('build') . '/*.js'));
    $buildExists = $hasManifest || ($cssExists && $jsExists);
    @endphp

    @if ($buildExists)
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        @php
        // Cari file CSS & JS terbaru sebagai fallback
        $cssFiles = glob(public_path('build') . '/*.css');
        $jsFiles = glob(public_path('build') . '/*.js');
        
        $cssFile = '';
        $jsFile = '';
        
        // Dapatkan file terbaru berdasarkan waktu modifikasi
        if (!empty($cssFiles)) {
            usort($cssFiles, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            $cssFile = basename($cssFiles[0]);
        }
        
        if (!empty($jsFiles)) {
            usort($jsFiles, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            // Filter hanya file JS yang bukan workbox
            foreach ($jsFiles as $file) {
                if (strpos(basename($file), 'workbox-') === false) {
                    $jsFile = basename($file);
                    break;
                }
            }
        }
        @endphp
        
        @if($cssFile)
            <link rel="stylesheet" href="{{ asset('build/' . $cssFile) }}">
        @endif
        @if($jsFile)
            <script type="module" src="{{ asset('build/' . $jsFile) }}"></script>
        @endif
    @endif
    
    @inertiaHead
</head>
<body class="font-sans antialiased h-full bg-light-bg dark:bg-dark-bg text-light-text dark:text-dark-text">
    @inertia
    
    <!-- Hidden logout form for PWA reset -->
    <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">
        @csrf
    </form>
</body>
</html>