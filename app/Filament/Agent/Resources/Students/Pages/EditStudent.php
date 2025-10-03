<?php

namespace App\Filament\Agent\Resources\Students\Pages;

use App\Filament\Agent\Resources\Students\Schemas\StudentEditForm;
use App\Filament\Agent\Resources\Students\StudentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

class EditStudent extends EditRecord
{
    protected static string $resource = StudentResource::class;

    public function form(Schema $schema): Schema
    {
        return StudentEditForm::configure($schema);
    }

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
