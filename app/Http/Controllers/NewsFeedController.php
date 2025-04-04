<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NewsFeed;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use App\Services\ArticleCrawlerService;
use Embed\Embed;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNewsFeedRequest;
use App\Http\Requests\UpdateNewsFeedRequest;

class NewsFeedController extends Controller
{
    use AuthorizesRequests;

    protected $crawler;
    protected $embed;

    public function __construct(ArticleCrawlerService $crawler)
    {
        $this->crawler = $crawler;
        $this->embed = new Embed();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $feeds = NewsFeed::with(['user' => function($query) {
            $query->select('id', 'name', 'profile_photo_path');
        }])
        ->latest()
        ->paginate(9);

        return Inertia::render('NewsFeed/Index', [
            'feeds' => $feeds
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('NewsFeed/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:news,video,social_media,image',
            'url' => 'required_unless:type,image|url|nullable',
            'image' => 'required_if:type,image|image|max:5120|nullable', // max 5MB
            'title' => 'required_if:type,image|nullable|string|max:250',
            'description' => 'nullable|string|max:1000',
            'meta_data' => 'nullable'
        ]);

        try {
            $data = [
                'user_id' => auth()->id(),
                'type' => $request->type,
                'url' => null,
                'title' => $request->title,
                'description' => $request->description
            ];

            if ($request->type === NewsFeed::TYPE_IMAGE) {
                // Handle uploaded image
                $newsFeed = NewsFeed::create($data);
                
                if ($request->hasFile('image')) {
                    $path = $request->file('image')->store('images/feeds', 'public');
                    $newsFeed->update([
                        'image_url' => $path,
                        'meta_data' => [
                            'type' => 'image',
                            'original_filename' => $request->file('image')->getClientOriginalName(),
                            'file_size' => $request->file('image')->getSize(),
                            'dimensions' => getimagesize($request->file('image')->getRealPath()),
                            'mime_type' => $request->file('image')->getMimeType(),
                            'is_uploaded' => true
                        ]
                    ]);
                }
            } else if ($request->type === 'social_media') {
                // Handle social media content
                try {
                    $embed = new Embed();
                    $info = $embed->get($request->url);
                    
                    // Get the general data
                    $platform = $this->detectPlatform($request->url);
                    $embedUrl = null;
                    
                    // Extract embed URL from HTML
                    if ($info->code) {
                        preg_match('/src=["\']([^"\']+)["\']/', $info->code, $matches);
                        $embedUrl = $matches[1] ?? null;
                    }

                    // Fallback untuk Instagram yang memerlukan format khusus
                    if ($platform === 'instagram' && !$embedUrl) {
                        preg_match('/\/(p|reel|tv)\/([^\/\?]+)/', $request->url, $matches);
                        if (isset($matches[2])) {
                            $embedUrl = "https://www.instagram.com/p/{$matches[2]}/embed/";
                        }
                    }

                    // Jika masih tidak ada embed URL untuk Instagram, gunakan URL asli
                    if ($platform === 'instagram' && !$embedUrl) {
                        $embedUrl = $request->url;
                    }

                    // Handle YouTube dan YouTube Shorts
                    if (($platform === 'youtube_shorts' || $platform === 'youtube') && !$embedUrl) {
                        $videoId = null;
                        
                        // Extract video ID from YouTube URL
                        if (strpos($request->url, 'youtube.com/shorts/') !== false) {
                            preg_match('/\/shorts\/([^\/\?]+)/', $request->url, $matches);
                            $videoId = $matches[1] ?? null;
                        } else if (strpos($request->url, 'youtube.com/watch?v=') !== false) {
                            preg_match('/v=([^&]+)/', $request->url, $matches);
                            $videoId = $matches[1] ?? null;
                        } else if (strpos($request->url, 'youtu.be/') !== false) {
                            preg_match('/youtu\.be\/([^\/\?]+)/', $request->url, $matches);
                            $videoId = $matches[1] ?? null;
                        }
                        
                        if ($videoId) {
                            // Format yang didukung untuk embed YouTube
                            $embedUrl = "https://www.youtube.com/embed/{$videoId}";
                            
                            // Jika YouTube Shorts, tambahkan parameter khusus
                            if ($platform === 'youtube_shorts') {
                                $embedUrl .= "?loop=1&rel=0&showinfo=0";
                            }
                        }
                    }

                    // Gunakan judul dari request, BUKAN dari Embed info
                    $title = $request->title ?? '';
                    $description = $request->description ?? '';
                    
                    // Potong title dan description agar tidak melebihi panjang kolom di database
                    $title = substr($title, 0, 250);
                    $description = substr($description, 0, 1000);

                    // Khusus untuk TikTok, gunakan format embed yang berbeda
                    if ($platform === 'tiktok') {
                        $tikTokData = $this->processTikTokUrl($request->url);
                        
                        if ($tikTokData) {
                            $embedUrl = $tikTokData['embed_url'];
                            $blockquoteHtml = $tikTokData['blockquote_html'];
                            $videoId = $tikTokData['video_id'];
                            $username = $tikTokData['username'];
                            
                            $data = array_merge($data, [
                                'url' => $request->url,
                                'title' => $title,
                                'description' => $description,
                                'site_name' => $info->providerName ?: 'TikTok',
                                'meta_data' => [
                                    'platform' => $platform,
                                    'author_name' => $info->authorName ?: $username,
                                    'provider_name' => $info->providerName ?: 'TikTok',
                                    'thumbnail_url' => $info->image,
                                    'html' => $blockquoteHtml,
                                    'embed_url' => $embedUrl,
                                    'original_title' => $info->title ? substr($info->title, 0, 250) : null,
                                    'video_id' => $videoId,
                                    'username' => $username
                                ]
                            ]);
                        } else {
                            // Fallback ke metode biasa jika gagal extract ID
                            $data = array_merge($data, [
                                'url' => $request->url,
                                'title' => $title,
                                'description' => $description,
                                'site_name' => $info->providerName ?: 'TikTok',
                                'meta_data' => [
                                    'platform' => $platform,
                                    'author_name' => $info->authorName,
                                    'provider_name' => $info->providerName ?: 'TikTok',
                                    'thumbnail_url' => $info->image,
                                    'html' => $info->code,
                                    'embed_url' => null,
                                    'original_title' => $info->title ? substr($info->title, 0, 250) : null,
                                ]
                            ]);
                        }
                    } else {
                        // Handling platform lain seperti biasa
                        $data = array_merge($data, [
                            'url' => $request->url,
                            'title' => $title,
                            'description' => $description,
                            'site_name' => $info->providerName ?: 'Instagram',
                            'meta_data' => [
                                'platform' => $platform,
                                'author_name' => $info->authorName,
                                'provider_name' => $info->providerName,
                                'thumbnail_url' => $info->image,
                                'html' => $info->code,
                                'embed_url' => $embedUrl,
                                'original_title' => $info->title ? substr($info->title, 0, 250) : null,
                            ]
                        ]);
                    }

                    \Log::info('Social media data: ', $data);
                    $newsFeed = NewsFeed::create($data);
                    
                    return redirect()->route('news-feeds.index')
                        ->with('message', 'Feed media sosial berhasil ditambahkan!');
                } catch (\Exception $e) {
                    \Log::error('Error creating social media feed: ' . $e->getMessage());
                    return back()->withErrors(['url' => 'Terjadi kesalahan saat memproses URL media sosial: ' . $e->getMessage()]);
                }
            } else {
                // Handle URL-based content (news, video)
                $metadata = NewsFeed::fetchMetaData($request->url, $request->type);
                
                if (!$metadata) {
                    return back()->withErrors(['url' => 'Tidak dapat mengambil metadata dari URL tersebut']);
                }

                $data = array_merge($data, [
                    'url' => $request->url,
                    'title' => $metadata['title'],
                    'description' => $metadata['description'],
                    'image_url' => $metadata['image_url'],
                    'video_url' => $metadata['video_url'] ?? null,
                    'site_name' => $metadata['site_name'],
                    'meta_data' => $metadata['meta_data']
                ]);

                $newsFeed = NewsFeed::create($data);
            }

            return redirect()->route('news-feeds.index')
                ->with('message', 'Feed berhasil ditambahkan!');

        } catch (\Exception $e) {
            \Log::error('Error creating news feed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menambahkan feed.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(NewsFeed $newsFeed)
    {
        $newsFeed->load(['user' => function($query) {
            $query->select('id', 'name', 'profile_photo_path');
        }]);

        return Inertia::render('NewsFeed/Show', [
            'feed' => $newsFeed
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(NewsFeed $newsFeed)
    {
        $this->authorize('update', $newsFeed);

        return Inertia::render('NewsFeed/Edit', [
            'feed' => $newsFeed
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, NewsFeed $newsFeed)
    {
        $this->authorize('update', $newsFeed);

        $request->validate([
            'type' => 'required|in:news,video,social_media,image',
            'url' => 'required_unless:type,image|url|nullable',
            'image' => 'nullable|image|max:5120', // max 5MB
            'title' => 'required_if:type,image|nullable|string|max:250',
            'description' => 'nullable|string|max:1000',
            'meta_data' => 'nullable'
        ]);

        try {
            if ($request->type === NewsFeed::TYPE_IMAGE) {
                $data = [
                    'type' => $request->type,
                    'title' => $request->title,
                    'description' => $request->description
                ];

                if ($request->hasFile('image')) {
                    $newsFeed->uploadImage($request->file('image'));
                }

                $newsFeed->update($data);
            } else if ($request->type === 'social_media') {
                // Untuk media sosial, gunakan title dan description dari request, 
                // bukan dari metadata yang diambil dari URL
                $data = [
                    'type' => $request->type,
                    'url' => $request->url,
                    'title' => $request->title,
                    'description' => $request->description,
                    'meta_data' => $request->meta_data
                ];
                
                // Jika URL berubah, perbarui metadata tapi pertahankan title dan description dari form
                if ($request->url !== $newsFeed->url) {
                    try {
                        $embed = new Embed();
                        $info = $embed->get($request->url);
                        
                        // Deteksi platform
                        $platform = $this->detectPlatform($request->url);
                        $embedUrl = null;
                        
                        // Handle TikTok secara khusus
                        if ($platform === 'tiktok') {
                            $tikTokData = $this->processTikTokUrl($request->url);
                            
                            if ($tikTokData) {
                                // Update meta_data tapi pertahankan title dan description
                                $data['meta_data'] = array_merge($request->meta_data ?: [], [
                                    'platform' => $platform,
                                    'author_name' => $info->authorName ?: $tikTokData['username'],
                                    'provider_name' => $info->providerName ?: 'TikTok',
                                    'thumbnail_url' => $info->image,
                                    'html' => $tikTokData['blockquote_html'],
                                    'embed_url' => $tikTokData['embed_url'],
                                    'original_title' => $info->title ? substr($info->title, 0, 250) : null,
                                    'video_id' => $tikTokData['video_id'],
                                    'username' => $tikTokData['username']
                                ]);
                                
                                $data['site_name'] = $info->providerName ?: 'TikTok';
                            }
                        }
                        
                        // Extract embed URL from HTML
                        if ($info->code) {
                            preg_match('/src=["\']([^"\']+)["\']/', $info->code, $matches);
                            $embedUrl = $matches[1] ?? null;
                        }
                        
                        // Fallback untuk Instagram yang memerlukan format khusus
                        if ($platform === 'instagram' && !$embedUrl) {
                            preg_match('/\/(p|reel|tv)\/([^\/\?]+)/', $request->url, $matches);
                            if (isset($matches[2])) {
                                $embedUrl = "https://www.instagram.com/p/{$matches[2]}/embed/";
                            }
                        }
                        
                        // Update meta_data tapi pertahankan title dan description
                        $data['meta_data'] = array_merge($request->meta_data ?: [], [
                            'platform' => $platform,
                            'author_name' => $info->authorName,
                            'provider_name' => $info->providerName,
                            'thumbnail_url' => $info->image,
                            'html' => $info->code,
                            'embed_url' => $embedUrl,
                            'original_title' => $info->title ? substr($info->title, 0, 250) : null,
                        ]);
                        
                        $data['site_name'] = $info->providerName ?: 'Instagram';
                    } catch (\Exception $e) {
                        \Log::error('Error updating social media metadata: ' . $e->getMessage());
                        // Jika gagal, gunakan meta_data yang ada
                    }
                }
                
                $newsFeed->update($data);
            } else {
                // Jika URL berubah, update metadata
                if ($request->url !== $newsFeed->url) {
                    $metadata = NewsFeed::fetchMetaData($request->url, $request->type);
                    
                    if (!$metadata) {
                        return back()->withErrors(['url' => 'Tidak dapat mengambil metadata dari URL tersebut']);
                    }

                    $data = array_merge([
                        'type' => $request->type,
                        'url' => $request->url
                    ], $metadata);

                    $newsFeed->update($data);
                } else {
                    // Jika hanya tipe yang berubah
                    $newsFeed->update([
                        'type' => $request->type
                    ]);
                }
            }

            return redirect()->route('news-feeds.index')
                ->with('message', 'Feed berhasil diperbarui!');

        } catch (\Exception $e) {
            \Log::error('Error updating news feed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan saat memperbarui feed.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NewsFeed $newsFeed)
    {
        $this->authorize('delete', $newsFeed);

        $newsFeed->delete();

        return redirect()->route('news-feeds.index')
            ->with('message', 'Feed berhasil dihapus!');
    }

    public function preview(Request $request)
    {
        $url = $request->input('url');
        $type = $request->input('type');

        if ($type === 'social_media') {
            return $this->getSocialMediaPreview($url);
        }

        // Existing preview logic for other types
        try {
            $metadata = $this->crawler->scrape($url);
            return response()->json($metadata);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    protected function getSocialMediaPreview($url)
    {
        try {
            $embed = new Embed();
            $info = $embed->get($url);
            
            // Deteksi platform atau default ke nilai kosong jika error
            try {
                $platform = $this->detectPlatform($url);
            } catch (\Exception $e) {
                \Log::warning('Error detecting platform: ' . $e->getMessage());
                $platform = '';
                
                // Coba deteksi platform dari URL lagi dengan cara yang lebih sederhana
                if (str_contains($url, 'instagram')) {
                    $platform = 'instagram';
                } elseif (str_contains($url, 'facebook')) {
                    $platform = 'facebook';
                } elseif (str_contains($url, 'twitter') || str_contains($url, 'x.com')) {
                    $platform = 'twitter';
                } elseif (str_contains($url, 'tiktok')) {
                    $platform = 'tiktok';
                } elseif (str_contains($url, 'youtube.com/shorts') || str_contains($url, 'youtu.be/shorts')) {
                    $platform = 'youtube_shorts';
                } elseif (str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')) {
                    $platform = 'youtube';
                }
            }
            
            // Get the general data
            $authorName = $info->authorName;
            
            // Tidak menggunakan caption sebagai judul, simpan caption sebagai original_title saja
            $data = [
                'platform' => $platform,
                'title' => '', // Kosongkan judul
                'description' => $info->description ? substr($info->description, 0, 1000) : null,
                'author_name' => $authorName,
                'provider_name' => $info->providerName,
                'thumbnail_url' => $info->image,
                'html' => $info->code,
                'embed_url' => null,
                'original_title' => $info->title ? substr($info->title, 0, 250) : null, // Simpan judul asli
            ];

            // Tambahkan warning khusus untuk facebook_share
            if ($platform === 'facebook_share') {
                $data['platform'] = 'facebook'; // Tetap gunakan platform facebook
                $data['share_format'] = true; // Tambahkan flag khusus
                $data['warning'] = 'Format URL share Facebook ini mungkin tidak dapat ditampilkan melalui embed.';
            }

            // Extract embed URL from HTML
            if ($info->code) {
                preg_match('/src=["\']([^"\']+)["\']/', $info->code, $matches);
                $data['embed_url'] = $matches[1] ?? null;
            }

            // Fallback untuk Instagram yang memerlukan format khusus
            if ($data['platform'] === 'instagram' && !$data['embed_url']) {
                preg_match('/\/(p|reel|tv)\/([^\/\?]+)/', $url, $matches);
                if (isset($matches[2])) {
                    $data['embed_url'] = "https://www.instagram.com/p/{$matches[2]}/embed/";
                }
            }

            // Jika masih tidak ada embed URL untuk Instagram, gunakan URL asli
            if ($data['platform'] === 'instagram' && !$data['embed_url']) {
                $data['embed_url'] = $url;
            }

            // Handle Threads
            if ($data['platform'] === 'threads') {
                try {
                    // Coba ambil data dari API oEmbed Threads terlebih dahulu
                    \Log::info('Fetching Threads oEmbed data for: ' . $url);
                    
                    // Gunakan pendekatan yang lebih aman
                    $threadId = null;
                    
                    // Ekstrak ID posting dari URL Threads
                    if (preg_match('/threads\.net\/(?:@)?([^\/]+)\/post\/([^\/\?]+)/', $url, $matches)) {
                        $threadId = $matches[2];
                    } 
                    // Format alternatif p/postid
                    else if (preg_match('/threads\.net\/(p|post)\/([^\/\?]+)/', $url, $matches)) {
                        $threadId = $matches[2];
                    }
                    // Format alternatif t/postid
                    else if (preg_match('/threads\.net\/t\/([^\/\?]+)/', $url, $matches)) {
                        $threadId = $matches[1];
                    }
                    
                    if ($threadId) {
                        \Log::info('Threads ID extracted: ' . $threadId);
                        
                        // Format yang didukung untuk embed Threads
                        $data['embed_url'] = "https://www.threads.net/embed/post/{$threadId}?width=550";
                        
                        // Set provider name dan platform
                        $data['provider_name'] = 'Threads';
                        $data['platform'] = 'threads';
                        
                        // Jika tidak ada gambar thumbnail, gunakan gambar default untuk Threads
                        if (empty($data['thumbnail_url'])) {
                            $data['thumbnail_url'] = 'https://static.xx.fbcdn.net/rsrc.php/v3/y-/r/z5Z8VSqrb99.png';
                        }
                        
                        // Jika title kosong, buat title sederhana
                        if (empty($data['title'])) {
                            $data['title'] = 'Threads post';
                        }
                        
                        // Tambahkan juga author_name jika diketahui dari URL
                        if (isset($matches[1]) && !preg_match('/^(p|post|t)$/', $matches[1])) {
                            $data['author_name'] = $matches[1];
                        }
                    } else {
                        \Log::warning('Tidak dapat mengekstrak ID dari URL Threads: ' . $url);
                        $data['embed_url'] = null;
                    }
                    
                    \Log::info('Threads data processed (simple approach):', $data);
                } catch (\Exception $e) {
                    \Log::error('Error getting Threads data: ' . $e->getMessage());
                    
                    // Fallback ke value default jika ada error
                    $data['embed_url'] = null;
                    $data['provider_name'] = 'Threads';
                    $data['platform'] = 'threads';
                    $data['thumbnail_url'] = 'https://static.xx.fbcdn.net/rsrc.php/v3/y-/r/z5Z8VSqrb99.png';
                }
            }

            // Handle YouTube Shorts
            if (($data['platform'] === 'youtube_shorts' || $data['platform'] === 'youtube') && !$data['embed_url']) {
                $videoId = null;
                
                // Extract video ID from YouTube URL
                if (strpos($url, 'youtube.com/shorts/') !== false) {
                    preg_match('/\/shorts\/([^\/\?]+)/', $url, $matches);
                    $videoId = $matches[1] ?? null;
                } else if (strpos($url, 'youtube.com/watch?v=') !== false) {
                    preg_match('/v=([^&]+)/', $url, $matches);
                    $videoId = $matches[1] ?? null;
                } else if (strpos($url, 'youtu.be/') !== false) {
                    preg_match('/youtu\.be\/([^\/\?]+)/', $url, $matches);
                    $videoId = $matches[1] ?? null;
                }
                
                if ($videoId) {
                    // Format yang didukung untuk embed YouTube
                    $data['embed_url'] = "https://www.youtube.com/embed/{$videoId}";
                    
                    // Jika YouTube Shorts, tambahkan parameter khusus
                    if ($data['platform'] === 'youtube_shorts') {
                        $data['embed_url'] .= "?loop=1&rel=0&showinfo=0";
                    }
                }
            }
            
            // Khusus untuk TikTok
            if ($data['platform'] === 'tiktok') {
                $tikTokData = $this->processTikTokUrl($url);
                
                if ($tikTokData) {
                    $data['embed_url'] = $tikTokData['embed_url'];
                    $data['html'] = $tikTokData['blockquote_html'];
                    $data['video_id'] = $tikTokData['video_id'];
                    $data['username'] = $tikTokData['username'];
                    
                    // Jika tidak ada author_name, gunakan username
                    if (empty($data['author_name'])) {
                        $data['author_name'] = $tikTokData['username'];
                    }
                }
            }
            
            // Handle Twitter
            if ($data['platform'] === 'twitter') {
                try {
                    \Log::info('Mengekstrak metadata Twitter untuk URL: ' . $url);
                    
                    // Ekstrak Tweet ID dari URL
                    preg_match('/\/status\/(\d+)/', $url, $matches);
                    $tweetId = $matches[1] ?? null;
                    
                    if ($tweetId) {
                        \Log::info('Twitter ID ditemukan: ' . $tweetId);
                        $data['tweet_id'] = $tweetId;
                    }
                    
                    // Cari thumbnail dari berbagai sumber
                    $possibleImageKeys = [
                        'image', 'twitter:image', 'twitter:image:src', 'og:image',
                        'twitter:card:image', 'twitter:player:image'
                    ];
                    
                    // Tambahkan thumbnail yang lebih baik
                    foreach ($possibleImageKeys as $key) {
                        $imageUrl = $info->$key;
                        if ($imageUrl) {
                            $data['thumbnail_url'] = $imageUrl;
                            $data['twitter_image'] = $imageUrl; // Simpan sebagai alternatif
                            \Log::info('Twitter image ditemukan dari key ' . $key . ': ' . $imageUrl);
                            break;
                        }
                    }
                    
                    // Jika tidak ada thumbnail, coba cari di HTML
                    if (empty($data['thumbnail_url']) && $info->code) {
                        // Cari URL gambar di dalam HTML
                        preg_match_all('/<img[^>]+src="([^"]+)"[^>]*>/i', $info->code, $matches);
                        
                        if (isset($matches[1]) && !empty($matches[1])) {
                            foreach ($matches[1] as $imgSrc) {
                                if (strpos($imgSrc, 'twimg.com') !== false || 
                                    strpos($imgSrc, 'twitter.com') !== false ||
                                    strpos($imgSrc, 'pbs.twimg') !== false) {
                                    $data['thumbnail_url'] = $imgSrc;
                                    $data['twitter_image'] = $imgSrc;
                                    \Log::info('Twitter image ditemukan dari HTML: ' . $imgSrc);
                                    break;
                                }
                            }
                        }
                    }
                    
                    // Coba ambil dari text jika ada URL gambar
                    if (empty($data['thumbnail_url']) && !empty($info->description)) {
                        preg_match('/(https?:\/\/[^\s]+\.(?:jpg|jpeg|png|gif))/i', $info->description, $matches);
                        if (isset($matches[1])) {
                            $data['thumbnail_url'] = $matches[1];
                            $data['twitter_image'] = $matches[1];
                            \Log::info('Twitter image ditemukan dari deskripsi: ' . $matches[1]);
                        }
                    }
                    
                    // Jika masih kosong, gunakan image default Twitter
                    if (empty($data['thumbnail_url'])) {
                        $data['thumbnail_url'] = 'https://abs.twimg.com/responsive-web/client-web/icon-default.522d363a.png';
                        $data['twitter_image'] = 'https://abs.twimg.com/responsive-web/client-web/icon-default.522d363a.png';
                        \Log::info('Menggunakan default Twitter image');
                    }
                    
                    // Ekstrak username dari URL
                    preg_match('/(twitter\.com|x\.com)\/([^\/]+)/', $url, $matches);
                    if (isset($matches[2]) && $matches[2] !== 'status') {
                        $data['author_username'] = $matches[2];
                        // Jika tidak ada author_name, gunakan username
                        if (empty($data['author_name'])) {
                            $data['author_name'] = '@' . $matches[2];
                        }
                    }
                    
                    \Log::info('Twitter data processed:', $data);
                } catch (\Exception $e) {
                    \Log::error('Error processing Twitter data: ' . $e->getMessage());
                }
            }

            \Log::info('Social media preview data: ', $data);

            return response()->json($data);
        } catch (\Exception $e) {
            \Log::error('Error getting social media preview: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate judul yang lebih ringkas untuk feed media sosial
     */
    protected function generateShortTitle($platform, $authorName, $originalTitle)
    {
        // Jika tidak ada judul asli, gunakan platform dan author
        if (empty($originalTitle)) {
            return $authorName ? "Post {$platform} dari {$authorName}" : "Post {$platform}";
        }
        
        // Jika judulnya mengandung username pengarang, gunakan itu
        if ($authorName && str_contains($originalTitle, $authorName)) {
            return "Post {$platform} dari {$authorName}";
        }
        
        // Jika judul terlalu panjang, potong sampai spasi terdekat
        if (strlen($originalTitle) > 70) {
            $shortTitle = substr($originalTitle, 0, 67);
            // Cari spasi terakhir untuk memotong pada kata yang utuh
            $lastSpace = strrpos($shortTitle, ' ');
            if ($lastSpace !== false) {
                $shortTitle = substr($shortTitle, 0, $lastSpace);
            }
            return $shortTitle . '...';
        }
        
        return $originalTitle;
    }

    protected function detectPlatform($url)
    {
        $urlParts = parse_url($url);
        
        if (!isset($urlParts['host'])) {
            throw new \Exception('URL tidak valid');
        }
        
        $host = $urlParts['host'] ?? '';
        $path = $urlParts['path'] ?? '';

        if (str_contains($host, 'instagram.com')) return 'instagram';
        if (str_contains($host, 'threads.net')) return 'threads';
        if (str_contains($host, 'facebook.com')) {
            // Deteksi format URL share Facebook
            if (str_contains($path, '/share/p/')) {
                return 'facebook_share';
            }
            return 'facebook';
        }
        if (str_contains($host, 'twitter.com') || str_contains($host, 'x.com')) return 'twitter';
        if (str_contains($host, 'tiktok.com')) return 'tiktok';
        if (str_contains($host, 'youtube.com') && str_contains($path, '/shorts/')) return 'youtube_shorts';
        if (str_contains($host, 'youtu.be') && str_contains($path, '/shorts/')) return 'youtube_shorts';

        // Jika URL tidak cocok dengan platform yang didukung, default ke instagram jika URL mengandung kata instagram
        if (str_contains($url, 'instagram')) return 'instagram';
        if (str_contains($url, 'threads')) return 'threads';
        
        // Tambahkan pendeteksian youtube shorts dari path
        if (str_contains($host, 'youtube.com') || str_contains($host, 'youtu.be')) {
            if (preg_match('/(\/shorts\/[a-zA-Z0-9_-]+)/', $url)) {
                return 'youtube_shorts';
            }
            return 'youtube'; // Platform baru untuk video YouTube biasa
        }

        throw new \Exception('Platform media sosial tidak didukung');
    }

    protected function getDefaultProfilePhotoUrl($name)
    {
        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&color=7F9CF5&background=EBF4FF';
    }

    protected function processTikTokUrl($url)
    {
        // Extract username dan video ID dari URL TikTok
        if (preg_match('/tiktok\.com\/@([^\/]+)\/video\/(\d+)/', $url, $matches)) {
            $username = $matches[1];
            $videoId = $matches[2];
            
            return [
                'username' => $username,
                'video_id' => $videoId,
                'embed_url' => "https://www.tiktok.com/embed/v2/{$videoId}",
                'blockquote_html' => '<blockquote class="tiktok-embed" cite="' . $url . '" ' .
                    'data-video-id="' . $videoId . '" ' .
                    'style="max-width: 605px; min-width: 325px;">' .
                    '<section></section>' .
                    '</blockquote>' .
                    '<script async src="https://www.tiktok.com/embed.js"></script>'
            ];
        }
        
        // Coba format alternatif jika tidak cocok dengan format standar
        if (preg_match('/tiktok\.com\/video\/(\d+)/', $url, $matches)) {
            $videoId = $matches[1];
            
            return [
                'username' => null,
                'video_id' => $videoId,
                'embed_url' => "https://www.tiktok.com/embed/v2/{$videoId}",
                'blockquote_html' => '<blockquote class="tiktok-embed" cite="' . $url . '" ' .
                    'data-video-id="' . $videoId . '" ' .
                    'style="max-width: 605px; min-width: 325px;">' .
                    '<section></section>' .
                    '</blockquote>' .
                    '<script async src="https://www.tiktok.com/embed.js"></script>'
            ];
        }
        
        return null;
    }
}
