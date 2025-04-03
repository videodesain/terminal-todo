<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        try {
            // Get the super admin user
            $superAdmin = User::where('email', 'admin@example.com')->first();
            
            if (!$superAdmin) {
                Log::error('Super admin user not found');
                return;
            }

            $teams = [
                [
                    'name' => 'Tim Konten',
                    'description' => 'Tim yang bertanggung jawab untuk pembuatan konten',
                    'is_active' => true,
                    'color' => '#3B82F6',
                    'created_by' => $superAdmin->id
                ],
                [
                    'name' => 'Tim Desain',
                    'description' => 'Tim yang bertanggung jawab untuk desain visual',
                    'is_active' => true,
                    'color' => '#8B5CF6',
                    'created_by' => $superAdmin->id
                ],
                [
                    'name' => 'Tim Marketing',
                    'description' => 'Tim yang bertanggung jawab untuk pemasaran',
                    'is_active' => true,
                    'color' => '#EC4899',
                    'created_by' => $superAdmin->id
                ],
                [
                    'name' => 'Tim Editorial',
                    'description' => 'Tim yang bertanggung jawab untuk editorial',
                    'is_active' => true,
                    'color' => '#10B981',
                    'created_by' => $superAdmin->id
                ]
            ];

            foreach ($teams as $team) {
                try {
                    $newTeam = Team::updateOrCreate(
                        ['name' => $team['name']],
                        [
                            'description' => $team['description'],
                            'is_active' => $team['is_active'],
                            'color' => $team['color'],
                            'created_by' => $team['created_by']
                        ]
                    );

                    // Add creator as team leader if not already added
                    if (!$newTeam->users()->where('user_id', $team['created_by'])->exists()) {
                        $newTeam->users()->attach($team['created_by'], ['role' => 'leader']);
                    }
                } catch (\Exception $e) {
                    Log::error("Error creating team {$team['name']}: " . $e->getMessage());
                    continue;
                }
            }
            
            Log::info('Teams seeded successfully');
        } catch (\Exception $e) {
            Log::error('Error seeding teams: ' . $e->getMessage());
        }
    }
} 