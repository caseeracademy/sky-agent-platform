<?php

namespace Database\Seeders;

use App\Models\Program;
use App\Models\University;
use Illuminate\Database\Seeder;

class UniversitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample universities
        $uoft = University::firstOrCreate(
            ['name' => 'University of Toronto'],
            [
                'location' => 'Toronto, Ontario, Canada',
                'is_active' => true,
            ]
        );

        $ubc = University::firstOrCreate(
            ['name' => 'University of British Columbia'],
            [
                'location' => 'Vancouver, British Columbia, Canada',
                'is_active' => true,
            ]
        );

        $mcgill = University::firstOrCreate(
            ['name' => 'McGill University'],
            [
                'location' => 'Montreal, Quebec, Canada',
                'is_active' => true,
            ]
        );

        // Get degree IDs
        $bachelorDegree = \App\Models\Degree::where('name', 'Bachelor')->first();
        $masterDegree = \App\Models\Degree::where('name', 'Master')->first();

        // Create sample programs for University of Toronto
        Program::firstOrCreate(
            ['name' => 'Computer Science', 'university_id' => $uoft->id],
            [
                'degree_id' => $bachelorDegree->id,
                'tuition_fee' => 58000.00,
                'agent_commission' => 5800.00,
                'system_commission' => 1160.00,
                'degree_type' => 'Bachelor',
                'is_active' => true,
            ]
        );

        Program::firstOrCreate(
            ['name' => 'Business Administration (MBA)', 'university_id' => $uoft->id],
            [
                'degree_id' => $masterDegree->id,
                'tuition_fee' => 118000.00,
                'agent_commission' => 11800.00,
                'system_commission' => 2360.00,
                'degree_type' => 'Master',
                'is_active' => true,
            ]
        );

        // Get PhD degree
        $phdDegree = \App\Models\Degree::where('name', 'PhD')->first();

        // Create sample programs for UBC
        Program::firstOrCreate(
            ['name' => 'Engineering', 'university_id' => $ubc->id],
            [
                'degree_id' => $bachelorDegree->id,
                'tuition_fee' => 55000.00,
                'agent_commission' => 5500.00,
                'system_commission' => 1100.00,
                'degree_type' => 'Bachelor',
                'is_active' => true,
            ]
        );

        Program::firstOrCreate(
            ['name' => 'Medicine', 'university_id' => $ubc->id],
            [
                'degree_id' => $phdDegree->id,
                'tuition_fee' => 350000.00,
                'agent_commission' => 35000.00,
                'system_commission' => 7000.00,
                'degree_type' => 'PhD',
                'is_active' => true,
            ]
        );

        // Get Certificate degree
        $certificateDegree = \App\Models\Degree::where('name', 'Certificate')->first();

        // Create sample programs for McGill
        Program::firstOrCreate(
            ['name' => 'Arts & Sciences', 'university_id' => $mcgill->id],
            [
                'degree_id' => $bachelorDegree->id,
                'tuition_fee' => 45000.00,
                'agent_commission' => 4500.00,
                'system_commission' => 900.00,
                'degree_type' => 'Bachelor',
                'is_active' => true,
            ]
        );

        Program::firstOrCreate(
            ['name' => 'Digital Marketing Certificate', 'university_id' => $mcgill->id],
            [
                'degree_id' => $certificateDegree->id,
                'tuition_fee' => 8500.00,
                'agent_commission' => 850.00,
                'system_commission' => 170.00,
                'degree_type' => 'Certificate',
                'is_active' => true,
            ]
        );
    }
}
