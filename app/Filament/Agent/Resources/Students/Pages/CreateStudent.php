<?php

namespace App\Filament\Agent\Resources\Students\Pages;

use App\Filament\Agent\Resources\Students\StudentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;
}
