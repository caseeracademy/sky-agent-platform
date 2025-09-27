<?php

namespace App\Filament\Agent\Resources\Applications\Pages;

use App\Filament\Agent\Resources\Applications\ApplicationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateApplication extends CreateRecord
{
    protected static string $resource = ApplicationResource::class;

    /**
     * Automatically assign agent_id before creating.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['agent_id'] = auth()->id();
        
        // Calculate commission if program is selected
        if (isset($data['program_id'])) {
            $program = \App\Models\Program::find($data['program_id']);
            if ($program) {
                $data['commission_amount'] = $program->agent_commission;
            }
        }
        
        return $data;
    }
}
