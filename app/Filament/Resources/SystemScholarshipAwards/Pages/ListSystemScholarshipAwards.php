<?php

namespace App\Filament\Resources\SystemScholarshipAwards\Pages;

use App\Filament\Resources\SystemScholarshipAwards\SystemScholarshipAwardResource;
use App\Models\SystemScholarshipDisplay;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Pagination\LengthAwarePaginator;

class ListSystemScholarshipAwards extends ListRecords
{
    protected static string $resource = SystemScholarshipAwardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action - system scholarships are automatically earned
        ];
    }

    /**
     * Override the table records to provide custom data for SystemScholarshipDisplay.
     */
    public function getTableRecords(): LengthAwarePaginator
    {
        // Get all system scholarship displays
        $systemScholarshipDisplays = SystemScholarshipDisplay::getAllSystemScholarships();

        $count = $systemScholarshipDisplays->count();
        $perPage = max(1, $count ?: 15); // Ensure perPage is never 0

        // Return as paginator
        return new LengthAwarePaginator(
            $systemScholarshipDisplays,
            $count,
            $perPage,
            1, // Current page
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
    }
}
