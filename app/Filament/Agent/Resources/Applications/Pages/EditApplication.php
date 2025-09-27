<?php

namespace App\Filament\Agent\Resources\Applications\Pages;

use App\Filament\Agent\Resources\Applications\ApplicationResource;
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

    protected function afterSave(): void
    {
        // Handle document uploads if any
        $uploads = $this->data['document_uploads'] ?? [];
        
        if (!empty($uploads)) {
            foreach ($uploads as $upload) {
                if (is_string($upload) && Storage::disk('public')->exists($upload)) {
                    ApplicationDocument::create([
                        'application_id' => $this->record->id,
                        'uploaded_by_user_id' => auth()->id(),
                        'original_filename' => basename($upload),
                        'disk' => 'public',
                        'path' => $upload,
                        'file_size' => Storage::disk('public')->size($upload),
                        'mime_type' => Storage::disk('public')->mimeType($upload),
                    ]);
                }
            }
        }
    }
}
