<?php

namespace App\Filament\Agent\Resources\Scholarships\Pages;

use App\Filament\Agent\Resources\Scholarships\ScholarshipResource;
use App\Models\ScholarshipDisplay;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Pagination\LengthAwarePaginator;

class ListScholarships extends ListRecords
{
    protected static string $resource = ScholarshipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action - scholarships are automatically awarded
        ];
    }

    /**
     * Override the table records to provide custom data for ScholarshipDisplay.
     */
    public function getTableRecords(): LengthAwarePaginator
    {
        // Get all scholarship displays for the current agent
        $scholarshipDisplays = ScholarshipDisplay::getAllForAgent(auth()->id());

        $count = $scholarshipDisplays->count();
        $perPage = max(1, $count ?: 15); // Ensure perPage is never 0

        // Return as paginator
        return new LengthAwarePaginator(
            $scholarshipDisplays,
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
