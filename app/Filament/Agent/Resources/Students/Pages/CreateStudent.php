<?php

namespace App\Filament\Agent\Resources\Students\Pages;

use App\Filament\Agent\Resources\Students\StudentResource;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\StudentDocument;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    protected array $fileData = []; // Property to store file data

    protected array $applicationData = []; // Property to store application data

    protected ?Application $createdApplication = null; // Property to track created application

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Store file data and application data in separate properties so they're available in afterCreate
        $this->fileData = [
            'passport_file' => $data['passport_file'] ?? null,
            'diploma_file' => $data['diploma_file'] ?? null,
            'transcript_file' => $data['transcript_file'] ?? null,
        ];

        $this->applicationData = [
            'university_id' => $data['university_id'] ?? null,
            'program_id' => $data['program_id'] ?? null,
        ];

        // Remove file fields from data so they don't try to save to the database
        unset($data['passport_file'], $data['diploma_file'], $data['transcript_file']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $student = $this->record;
        $data = $this->data;
        $fileData = $this->fileData ?? [];
        $applicationData = $this->applicationData ?? [];

        // Create student documents from uploaded files
        $this->createStudentDocuments($student, $fileData);

        // Create application if university and program are selected
        if (! empty($applicationData['university_id']) && ! empty($applicationData['program_id'])) {
            $this->createApplication($student, $fileData, $applicationData);
        }

        // Generate full name from separate fields and store in 'name' field for backward compatibility
        $this->updateStudentName($student, $data);
    }

    protected function createStudentDocuments($student, $data): void
    {
        $documentTypes = [
            'passport_file' => 'passport',
            'diploma_file' => 'diploma',
            'transcript_file' => 'transcript',
        ];

        foreach ($documentTypes as $fileField => $documentType) {
            if (isset($data[$fileField]) && ! empty($data[$fileField])) {
                $filePath = null;

                // Handle different file upload formats
                if (is_array($data[$fileField])) {
                    // Array format - get first file if it exists
                    $filePath = (! empty($data[$fileField]) && isset($data[$fileField][0])) ? $data[$fileField][0] : null;
                } elseif (is_string($data[$fileField])) {
                    // String format - direct file path
                    $filePath = $data[$fileField];
                }

                if ($filePath && Storage::disk('public')->exists($filePath)) {
                    $fileInfo = pathinfo($filePath);

                    $document = StudentDocument::create([
                        'student_id' => $student->id,
                        'uploaded_by' => auth()->id(),
                        'name' => ucfirst($documentType).' Document',
                        'type' => $documentType,
                        'file_path' => $filePath,
                        'file_name' => $fileInfo['basename'],
                        'mime_type' => Storage::disk('public')->mimeType($filePath),
                        'file_size' => Storage::disk('public')->size($filePath),
                    ]);
                }
            }
        }
    }

    protected function createApplication($student, $fileData, $applicationData): void
    {
        $application = Application::create([
            'application_number' => 'APP-'.str_pad(Application::count() + 1, 6, '0', STR_PAD_LEFT),
            'student_id' => $student->id,
            'program_id' => $applicationData['program_id'],
            'agent_id' => auth()->id(),
            'status' => 'needs_review',
            'commission_type' => null,
            'needs_review' => true,
            'submitted_at' => now(),
        ]);

        // Store the created application for redirect
        $this->createdApplication = $application;

        // Also create application documents from the uploaded files
        $this->createApplicationDocuments($application, $fileData);
    }

    protected function createApplicationDocuments($application, $data): void
    {
        $documentTypes = [
            'passport_file' => 'Passport Document',
            'diploma_file' => 'Diploma Document',
            'transcript_file' => 'Transcript Document',
        ];

        foreach ($documentTypes as $fileField => $title) {
            if (isset($data[$fileField]) && ! empty($data[$fileField])) {
                $filePath = null;

                // Handle different file upload formats
                if (is_array($data[$fileField])) {
                    $filePath = (! empty($data[$fileField]) && isset($data[$fileField][0])) ? $data[$fileField][0] : null;
                } elseif (is_string($data[$fileField])) {
                    $filePath = $data[$fileField];
                }

                if ($filePath && Storage::disk('public')->exists($filePath)) {
                    ApplicationDocument::create([
                        'application_id' => $application->id,
                        'uploaded_by_user_id' => auth()->id(),
                        'title' => $title,
                        'original_filename' => basename($filePath),
                        'disk' => 'public',
                        'path' => $filePath,
                        'file_size' => Storage::disk('public')->size($filePath),
                        'mime_type' => Storage::disk('public')->mimeType($filePath),
                    ]);
                }
            }
        }
    }

    protected function updateStudentName($student, $data): void
    {
        $fullName = '';

        if (isset($data['first_name']) && isset($data['last_name'])) {
            $fullName = $data['first_name'];
            if (! empty($data['middle_name'])) {
                $fullName .= ' '.$data['middle_name'];
            }
            $fullName .= ' '.$data['last_name'];
        }

        if ($fullName) {
            $student->update(['name' => $fullName]);
        }
    }

    protected function getRedirectUrl(): string
    {
        // If an application was created, redirect to the application details page
        if ($this->createdApplication) {
            return \App\Filament\Agent\Resources\Applications\ApplicationResource::getUrl('view', ['record' => $this->createdApplication->id]);
        }

        // Otherwise, redirect to the student details page (default behavior)
        return \App\Filament\Agent\Resources\Students\StudentResource::getUrl('view', ['record' => $this->record->id]);
    }
}
