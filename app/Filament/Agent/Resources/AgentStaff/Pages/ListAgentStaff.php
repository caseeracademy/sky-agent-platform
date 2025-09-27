<?php

namespace App\Filament\Agent\Resources\AgentStaff\Pages;

use App\Filament\Agent\Resources\AgentStaff\AgentStaffResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAgentStaff extends ListRecords
{
    protected static string $resource = AgentStaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
