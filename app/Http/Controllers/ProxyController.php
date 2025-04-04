<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ProxyController extends Controller
{
    /**
     * Proxy untuk mengakses gambar dari domain yang terblokir CORS
     */
    public function proxyImage(Request $request)
    {
        $url = $request->query('url');
        
        if (!$url) {
            return response()->json(['error' => 'URL parameter diperlukan'], 400);
        }
        
        // Validasi URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return response()->json(['error' => 'URL tidak valid'], 400);
        }
        
        // Cek apakah cache tersedia untuk URL ini (simpan hasil untuk 1 hari)
        $cacheKey = 'proxy_image_' . md5($url);
        
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        try {
            // Tambahkan header referer untuk mengatasi pembatasan
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'Referer' => 'https://www.instagram.com/'
            ])->get($url);
            
            if ($response->successful()) {
                $contentType = $response->header('Content-Type');
                $content = $response->body();
                
                // Buat response dengan content-type yang sesuai
                $proxyResponse = response($content, 200, [
                    'Content-Type' => $contentType,
                    'Content-Length' => strlen($content),
                    'Cache-Control' => 'public, max-age=86400',
                ]);
                
                // Simpan di cache untuk mempercepat akses berikutnya
                Cache::put($cacheKey, $proxyResponse, now()->addDay());
                
                return $proxyResponse;
            } else {
                Log::error('Proxy image error: ' . $response->status() . ' for URL: ' . $url);
                return response()->json(['error' => 'Gagal mengambil gambar'], $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Proxy image exception: ' . $e->getMessage() . ' for URL: ' . $url);
            return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
        }
    }
} 