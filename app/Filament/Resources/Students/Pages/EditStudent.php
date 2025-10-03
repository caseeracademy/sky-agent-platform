<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\Schemas\StudentEditForm;
use App\Filament\Resources\Students\StudentResource;
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
        return [
            DeleteAction::make(),
        ];
    }
}
