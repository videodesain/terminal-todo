<?php

namespace Database\Seeders;

use App\Models\SocialPlatform;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class SocialPlatformSeeder extends Seeder
{
    public function run(): void
    {
        try {
            $platforms = [
                [
                    'name' => 'Instagram',
                    'icon' => 'instagram',
                    'description' => 'Platform berbagi foto dan video',
                    'is_active' => true
                ],
                [
                    'name' => 'Facebook',
                    'icon' => 'facebook',
                    'description' => 'Platform jejaring sosial',
                    'is_active' => true
                ],
                [
                    'name' => 'Twitter',
                    'icon' => 'twitter',
                    'description' => 'Platform microblogging',
                    'is_active' => true
                ],
                [
                    'name' => 'TikTok',
                    'icon' => 'tiktok',
                    'description' => 'Platform berbagi video pendek',
                    'is_active' => true
                ],
                [
                    'name' => 'YouTube',
                    'icon' => 'youtube',
                    'description' => 'Platform berbagi video',
                    'is_active' => true
                ],
                [
                    'name' => 'LinkedIn',
                    'icon' => 'linkedin',
                    'description' => 'Platform jejaring profesional',
                    'is_active' => true
                ]
            ];

            foreach ($platforms as $platformData) {
                SocialPlatform::updateOrCreate(
                    ['name' => $platformData['name']],
                    [
                        'icon' => $platformData['icon'],
                        'description' => $platformData['description'],
                        'is_active' => $platformData['is_active']
                    ]
                );
            }
            
            Log::info('Social platforms seeded successfully');
        } catch (\Exception $e) {
            Log::error('Error seeding social platforms: ' . $e->getMessage());
        }
    }
} 