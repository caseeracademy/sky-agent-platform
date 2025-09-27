<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class StudentDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'name',
        'type',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'description',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getDownloadUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }

    public function getFormattedFileSizeAttribute(): string
    {
        $size = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $size >= 1024 && $i < 3; $i++) {
            $size /= 1024;
        }
        
        return round($size, 2) . ' ' . $units[$i];
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'passport' => 'heroicon-o-identification',
            'certificate' => 'heroicon-o-academic-cap',
            'transcript' => 'heroicon-o-document-text',
            'photo' => 'heroicon-o-photo',
            'visa' => 'heroicon-o-globe-alt',
            'language_test' => 'heroicon-o-language',
            default => 'heroicon-o-document',
        };
    }

    public static function getDocumentTypes(): array
    {
        return [
            'passport' => 'Passport',
            'certificate' => 'Certificate/Diploma',
            'transcript' => 'Academic Transcript',
            'photo' => 'Photo/ID',
            'visa' => 'Visa Document',
            'language_test' => 'Language Test Result',
            'other' => 'Other Document',
        ];
    }
}
