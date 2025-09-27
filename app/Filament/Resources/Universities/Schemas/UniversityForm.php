<?php

namespace App\Filament\Resources\Universities\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UniversityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('location')
                    ->maxLength(255)
                    ->nullable()
                    ->placeholder('e.g., Toronto, Canada'),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }
}
