<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Platform;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PlatformSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            $platforms = [
                [
                    'name' => 'Instagram',
                    'icon' => 'instagram',
                    'color' => '#E4405F',
                    'description' => 'Platform berbagi foto dan video',
                    'is_active' => true
                ],
                [
                    'name' => 'Facebook',
                    'icon' => 'facebook',
                    'color' => '#1877F2',
                    'description' => 'Platform jejaring sosial',
                    'is_active' => true
                ],
                [
                    'name' => 'Twitter',
                    'icon' => 'twitter',
                    'color' => '#1DA1F2',
                    'description' => 'Platform microblogging',
                    'is_active' => true
                ],
                [
                    'name' => 'LinkedIn',
                    'icon' => 'linkedin',
                    'color' => '#0A66C2',
                    'description' => 'Platform jejaring profesional',
                    'is_active' => true
                ],
                [
                    'name' => 'YouTube',
                    'icon' => 'youtube',
                    'color' => '#FF0000',
                    'description' => 'Platform berbagi video',
                    'is_active' => true
                ],
                [
                    'name' => 'TikTok',
                    'icon' => 'tiktok',
                    'color' => '#000000',
                    'description' => 'Platform berbagi video pendek',
                    'is_active' => true
                ]
            ];

            foreach ($platforms as $platform) {
                try {
                    $slug = Str::slug($platform['name']);
                    Platform::updateOrCreate(
                        ['slug' => $slug],
                        [
                            'name' => $platform['name'],
                            'icon' => $platform['icon'],
                            'color' => $platform['color'],
                            'description' => $platform['description'],
                            'is_active' => $platform['is_active']
                        ]
                    );
                } catch (\Exception $e) {
                    Log::error("Error creating platform {$platform['name']}: " . $e->getMessage());
                    continue;
                }
            }
            
            Log::info('Platforms seeded successfully');
        } catch (\Exception $e) {
            Log::error('Error seeding platforms: ' . $e->getMessage());
        }
    }
}
