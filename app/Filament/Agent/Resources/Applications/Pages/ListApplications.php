<?php

namespace App\Filament\Agent\Resources\Applications\Pages;

use App\Filament\Agent\Resources\Applications\ApplicationResource;
use Filament\Resources\Pages\ListRecords;

class ListApplications extends ListRecords
{
    protected static string $resource = ApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction removed - applications are now created through student creation workflow
        ];
    }
}
