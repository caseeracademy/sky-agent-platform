<?php

namespace App\Filament\Resources\ScholarshipAwards\Pages;

use App\Filament\Resources\ScholarshipAwards\ScholarshipAwardResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditScholarshipAward extends EditRecord
{
    protected static string $resource = ScholarshipAwardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
