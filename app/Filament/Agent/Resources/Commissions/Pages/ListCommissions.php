<?php

namespace App\Filament\Agent\Resources\Commissions\Pages;

use App\Filament\Agent\Resources\Commissions\CommissionResource;
use Filament\Resources\Pages\ListRecords;

class ListCommissions extends ListRecords
{
    protected static string $resource = CommissionResource::class;

    protected function getActions(): array
    {
        return [];
    }
}
