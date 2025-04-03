<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            // Kategori untuk Task
            $taskCategories = [
                [
                    'name' => 'Development',
                    'type' => 'task',
                    'color' => '#3B82F6', // Blue
                    'description' => 'Tugas terkait pengembangan aplikasi',
                    'is_active' => true
                ],
                [
                    'name' => 'Design',
                    'type' => 'task',
                    'color' => '#8B5CF6', // Purple
                    'description' => 'Tugas terkait desain UI/UX',
                    'is_active' => true
                ],
                [
                    'name' => 'Marketing',
                    'type' => 'task',
                    'color' => '#10B981', // Green
                    'description' => 'Tugas terkait pemasaran dan promosi',
                    'is_active' => true
                ],
                [
                    'name' => 'Content',
                    'type' => 'task',
                    'color' => '#F59E0B', // Yellow
                    'description' => 'Tugas terkait pembuatan konten',
                    'is_active' => true
                ],
                [
                    'name' => 'Research',
                    'type' => 'task',
                    'color' => '#EC4899', // Pink
                    'description' => 'Tugas terkait penelitian dan analisis',
                    'is_active' => true
                ],
                [
                    'name' => 'Meeting',
                    'type' => 'task',
                    'color' => '#6B7280', // Gray
                    'description' => 'Tugas terkait rapat dan koordinasi',
                    'is_active' => true
                ],
            ];

            // Kategori untuk Konten
            $contentCategories = [
                [
                    'name' => 'Artikel',
                    'type' => 'content',
                    'color' => '#3B82F6', // Blue
                    'description' => 'Konten artikel blog atau website',
                    'is_active' => true
                ],
                [
                    'name' => 'Social Media',
                    'type' => 'content',
                    'color' => '#8B5CF6', // Purple
                    'description' => 'Konten untuk media sosial',
                    'is_active' => true
                ],
                [
                    'name' => 'Video',
                    'type' => 'content',
                    'color' => '#10B981', // Green
                    'description' => 'Konten video',
                    'is_active' => true
                ],
                [
                    'name' => 'Infografis',
                    'type' => 'content',
                    'color' => '#F59E0B', // Yellow
                    'description' => 'Konten infografis',
                    'is_active' => true
                ],
                [
                    'name' => 'Newsletter',
                    'type' => 'content',
                    'color' => '#EC4899', // Pink
                    'description' => 'Konten newsletter',
                    'is_active' => true
                ],
            ];

            // Insert task categories
            foreach ($taskCategories as $category) {
                try {
                    $slug = Str::slug($category['name']);
                    Category::updateOrCreate(
                        ['slug' => $slug],
                        [
                            'name' => $category['name'],
                            'type' => $category['type'],
                            'color' => $category['color'],
                            'description' => $category['description'],
                            'is_active' => $category['is_active']
                        ]
                    );
                } catch (\Exception $e) {
                    Log::error("Error creating category {$category['name']}: " . $e->getMessage());
                    continue;
                }
            }
            
            // Insert content categories
            foreach ($contentCategories as $category) {
                try {
                    $slug = Str::slug($category['name']);
                    Category::updateOrCreate(
                        ['slug' => $slug],
                        [
                            'name' => $category['name'],
                            'type' => $category['type'],
                            'color' => $category['color'],
                            'description' => $category['description'],
                            'is_active' => $category['is_active']
                        ]
                    );
                } catch (\Exception $e) {
                    Log::error("Error creating category {$category['name']}: " . $e->getMessage());
                    continue;
                }
            }
            
            Log::info('Categories seeded successfully');
        } catch (\Exception $e) {
            Log::error('Error seeding categories: ' . $e->getMessage());
        }
    }
}
