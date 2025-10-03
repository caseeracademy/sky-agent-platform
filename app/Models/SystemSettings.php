<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSettings extends Model
{
    protected $fillable = [
        'company_name',
        'company_email',
        'company_phone',
        'company_address',
        'company_logo_path',
    ];

    protected function casts(): array
    {
        return [];
    }

    /**
     * Get the singleton settings instance.
     */
    public static function getSettings(): self
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'company_name' => 'Sky Blue Consulting',
                'company_email' => 'info@skyblue.com',
            ]
        );
    }
}
