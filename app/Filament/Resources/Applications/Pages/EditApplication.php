<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Filament\Resources\Applications\ApplicationResource;
use App\Models\ApplicationDocument;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditApplication extends EditRecord
{
    protected static string $resource = ApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Handle new documents upload
        if (isset($data['new_documents']) && is_array($data['new_documents'])) {
            foreach ($data['new_documents'] as $documentData) {
                if (! empty($documentData['file']) && ! empty($documentData['title'])) {
                    // Get file info
                    $filePath = $documentData['file'];
                    $fileName = basename($filePath);

                    // Get file details from storage
                    $fileSize = Storage::disk('public')->size($filePath);
                    $mimeType = Storage::disk('public')->mimeType($filePath);

                    // Create document record
                    ApplicationDocument::create([
                        'application_id' => $this->record->id,
                        'uploaded_by_user_id' => auth()->id(),
                        'title' => $documentData['title'],
                        'original_filename' => $fileName,
                        'disk' => 'public',
                        'path' => $filePath,
                        'file_size' => $fileSize,
                        'mime_type' => $mimeType,
                    ]);
                }
            }

            // Remove new_documents from data to prevent saving to applications table
            unset($data['new_documents']);
        }

        return $data;
    }
}
