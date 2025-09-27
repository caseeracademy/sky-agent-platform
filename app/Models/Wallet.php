<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wallet extends Model
{
    protected $fillable = [
        'agent_id',
        'available_balance',
        'pending_balance',
    ];

    protected $casts = [
        'available_balance' => 'decimal:2',
        'pending_balance' => 'decimal:2',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
