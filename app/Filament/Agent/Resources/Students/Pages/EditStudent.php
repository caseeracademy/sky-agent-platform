<?php

namespace App\Filament\Agent\Resources\Students\Pages;

use App\Filament\Agent\Resources\Students\StudentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStudent extends EditRecord
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [];

        // Only super_admin can delete students
        if (auth()->user()->isSuperAdmin()) {
            $actions[] = DeleteAction::make();
        }

        return $actions;
    }
}
