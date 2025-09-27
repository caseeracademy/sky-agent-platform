<?php

namespace App\Filament\Agent\Resources\AgentStaff\Pages;

use App\Filament\Agent\Resources\AgentStaff\AgentStaffResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAgentStaff extends CreateRecord
{
    protected static string $resource = AgentStaffResource::class;

    /**
     * Automatically set role and parent_agent_id before creating.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['role'] = 'agent_staff';
        $data['parent_agent_id'] = auth()->id();
        $data['is_active'] = true;
        return $data;
    }
}
