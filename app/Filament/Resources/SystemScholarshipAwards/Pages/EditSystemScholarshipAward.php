<?php

namespace App\Filament\Resources\SystemScholarshipAwards\Pages;

use App\Filament\Resources\SystemScholarshipAwards\SystemScholarshipAwardResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSystemScholarshipAward extends EditRecord
{
    protected static string $resource = SystemScholarshipAwardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
