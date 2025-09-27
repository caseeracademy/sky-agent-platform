<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ApplicationDocument extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'application_id',
        'uploaded_by_user_id',
        'original_filename',
        'disk',
        'path',
        'file_size',
        'mime_type',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    /**
     * Get the application that owns the document.
     */
    public function application(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Get the user who uploaded the document.
     */
    public function uploadedByUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    /**
     * Get the download URL for the document.
     */
    public function getDownloadUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    /**
     * Get the formatted file size.
     */
    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) {
            return 'Unknown size';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->file_size;
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if the document exists on disk.
     */
    public function exists(): bool
    {
        return Storage::disk($this->disk)->exists($this->path);
    }

    /**
     * Delete the document from storage.
     */
    public function deleteFromStorage(): bool
    {
        return Storage::disk($this->disk)->delete($this->path);
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::deleting(function (ApplicationDocument $document) {
            // Delete the file from storage when the model is deleted
            $document->deleteFromStorage();
        });
    }
}
