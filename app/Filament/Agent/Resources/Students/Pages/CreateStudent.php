<?php

namespace App\Filament\Agent\Resources\Students\Pages;

use App\Filament\Agent\Resources\Students\StudentResource;
use App\Models\Application;
use App\Models\StudentDocument;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    protected function afterCreate(): void
    {
        $student = $this->record;
        $data = $this->data;

        // Create student documents from uploaded files
        $this->createStudentDocuments($student, $data);

        // Create application if university and program are selected
        if (isset($data['university_id']) && isset($data['program_id'])) {
            $this->createApplication($student, $data);
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
            if (isset($data[$fileField]) && is_array($data[$fileField]) && ! empty($data[$fileField])) {
                $filePath = $data[$fileField][0]; // Get first uploaded file

                if (Storage::disk('public')->exists($filePath)) {
                    $fileInfo = pathinfo($filePath);

                    StudentDocument::create([
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

    protected function createApplication($student, $data): void
    {
        Application::create([
            'application_number' => 'APP-'.str_pad(Application::count() + 1, 6, '0', STR_PAD_LEFT),
            'student_id' => $student->id,
            'program_id' => $data['program_id'],
            'agent_id' => auth()->id(),
            'status' => 'pending',
        ]);
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
}
