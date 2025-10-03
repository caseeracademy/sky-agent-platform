<?php

namespace App\Filament\Resources\ScholarshipAwards\Pages;

use App\Filament\Resources\ScholarshipAwards\ScholarshipAwardResource;
use Filament\Resources\Pages\ListRecords;

class ListScholarshipAwards extends ListRecords
{
    protected static string $resource = ScholarshipAwardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Create action removed - scholarships are automatically earned by agents
        ];
    }
}
