<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing users and data
        $agentOwner = User::where('role', 'agent_owner')->first();
        $agentStaff = User::where('role', 'agent_staff')->first();
        
        if (!$agentOwner || !$agentStaff) {
            return; // Skip if no agents exist
        }

        $students = Student::all();
        $programs = Program::all();

        if ($students->count() < 2 || $programs->count() < 1) {
            return; // Skip if insufficient data
        }

        // Create sample applications with documents
        $application1 = Application::firstOrCreate(
            [
                'agent_id' => $agentOwner->id,
                'student_id' => $students->first()->id,
                'program_id' => $programs->first()->id,
            ],
            [
                'status' => 'submitted',
                'notes' => 'Complete application with all required documents',
                'intake_date' => now()->addMonths(6),
                'submitted_at' => now()->subDays(3),
            ]
        );

        $application2 = Application::firstOrCreate(
            [
                'agent_id' => $agentStaff->id,
                'student_id' => $students->skip(1)->first()->id,
                'program_id' => $programs->first()->id,
            ],
            [
                'status' => 'under_review',
                'notes' => 'Expedited application for January intake',
                'intake_date' => now()->addMonths(4),
                'submitted_at' => now()->subDays(5),
                'reviewed_at' => now()->subDays(1),
            ]
        );

        // Calculate commissions
        $application1->calculateCommission();
        $application2->calculateCommission();

        // Create sample document records (simulating uploaded files)
        if (!$application1->applicationDocuments()->exists()) {
            ApplicationDocument::create([
                'application_id' => $application1->id,
                'uploaded_by_user_id' => $agentOwner->id,
                'original_filename' => 'transcript.pdf',
                'disk' => 'public',
                'path' => 'application-documents/transcript.pdf',
                'file_size' => 1024 * 1024, // 1MB
                'mime_type' => 'application/pdf',
            ]);

            ApplicationDocument::create([
                'application_id' => $application1->id,
                'uploaded_by_user_id' => $agentOwner->id,
                'original_filename' => 'passport.jpg',
                'disk' => 'public',
                'path' => 'application-documents/passport.jpg',
                'file_size' => 512 * 1024, // 512KB
                'mime_type' => 'image/jpeg',
            ]);
        }

        if (!$application2->applicationDocuments()->exists()) {
            ApplicationDocument::create([
                'application_id' => $application2->id,
                'uploaded_by_user_id' => $agentStaff->id,
                'original_filename' => 'diploma.pdf',
                'disk' => 'public',
                'path' => 'application-documents/diploma.pdf',
                'file_size' => 2 * 1024 * 1024, // 2MB
                'mime_type' => 'application/pdf',
            ]);

            ApplicationDocument::create([
                'application_id' => $application2->id,
                'uploaded_by_user_id' => $agentStaff->id,
                'original_filename' => 'ielts_certificate.pdf',
                'disk' => 'public',
                'path' => 'application-documents/ielts_certificate.pdf',
                'file_size' => 768 * 1024, // 768KB
                'mime_type' => 'application/pdf',
            ]);
        }
    }
}