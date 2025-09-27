<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'application_id',
        'user_id',
        'note',
        'status_change',
    ];

    /**
     * Get the application that owns the log.
     */
    public function application(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Get the user who made the change.
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create a log entry for application creation.
     */
    public static function logCreation(Application $application, User $user): self
    {
        return static::create([
            'application_id' => $application->id,
            'user_id' => $user->id,
            'note' => 'Application created',
            'status_change' => null,
        ]);
    }

    /**
     * Create a log entry for status change.
     */
    public static function logStatusChange(Application $application, User $user, string $oldStatus, string $newStatus, ?string $note = null): self
    {
        return static::create([
            'application_id' => $application->id,
            'user_id' => $user->id,
            'note' => $note ?? "Status changed from {$oldStatus} to {$newStatus}",
            'status_change' => "{$oldStatus} -> {$newStatus}",
        ]);
    }
}
