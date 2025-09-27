<?php

namespace App\Filament\Agent\Resources\AgentStaff\Pages;

use App\Filament\Agent\Resources\AgentStaff\AgentStaffResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAgentStaff extends EditRecord
{
    protected static string $resource = AgentStaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
