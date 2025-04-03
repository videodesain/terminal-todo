<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Calendar;
use App\Models\User;
use App\Models\Category;
use App\Models\Platform;
use App\Models\Team;
use Illuminate\Support\Facades\Log;

class CalendarSeeder extends Seeder
{
    public function run(): void
    {
        try {
            // Get required models
            $superAdmin = User::where('email', 'admin@example.com')->first();
            $contentCategory = Category::where('name', 'Artikel Blog')->first();
            $platform = Platform::first();
            $team = Team::first();

            if (!$superAdmin || !$contentCategory || !$platform || !$team) {
                Log::error('Required models not found for calendar seeding');
                return;
            }

            $calendars = [
                [
                    'title' => 'Artikel Blog: Tips Produktivitas',
                    'description' => 'Artikel tentang tips meningkatkan produktivitas kerja',
                    'publish_date' => now()->addDays(5),
                    'deadline' => now()->addDays(3),
                    'status' => 'planned',
                    'category_id' => $contentCategory->id,
                    'platform_id' => $platform->id,
                    'team_id' => $team->id,
                    'created_by' => $superAdmin->id
                ],
                [
                    'title' => 'Artikel Blog: Manajemen Waktu',
                    'description' => 'Artikel tentang teknik manajemen waktu yang efektif',
                    'publish_date' => now()->addDays(10),
                    'deadline' => now()->addDays(8),
                    'status' => 'planned',
                    'category_id' => $contentCategory->id,
                    'platform_id' => $platform->id,
                    'team_id' => $team->id,
                    'created_by' => $superAdmin->id
                ]
            ];

            foreach ($calendars as $calendar) {
                try {
                    Calendar::updateOrCreate(
                        ['title' => $calendar['title']],
                        $calendar
                    );
                } catch (\Exception $e) {
                    Log::error("Error creating calendar {$calendar['title']}: " . $e->getMessage());
                    continue;
                }
            }
            
            Log::info('Calendars seeded successfully');
        } catch (\Exception $e) {
            Log::error('Error seeding calendars: ' . $e->getMessage());
        }
    }
} 