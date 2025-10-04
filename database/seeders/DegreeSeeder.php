<?php

namespace Database\Seeders;

use App\Models\Degree;
use Illuminate\Database\Seeder;

class DegreeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $degrees = [
            [
                'name' => 'Bachelor',
                'slug' => 'bachelor',
                'description' => 'Undergraduate degree program',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Master with Thesis',
                'slug' => 'master-with-thesis',
                'description' => 'Graduate degree program with thesis requirement',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Master without Thesis',
                'slug' => 'master-without-thesis',
                'description' => 'Graduate degree program without thesis requirement',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Diploma',
                'slug' => 'diploma',
                'description' => 'Diploma program',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'PhD',
                'slug' => 'phd',
                'description' => 'Doctor of Philosophy program',
                'sort_order' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($degrees as $degreeData) {
            Degree::updateOrCreate(
                ['slug' => $degreeData['slug']],
                $degreeData
            );
        }
    }
}