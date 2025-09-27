<?php

namespace App\Filament\Resources\Programs\Schemas;

use App\Models\University;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProgramForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('university_id')
                    ->label('University')
                    ->relationship('university', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->options(function () {
                        return University::active()->pluck('name', 'id');
                    }),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g., Computer Science'),
                Select::make('degree_type')
                    ->required()
                    ->options([
                        'Certificate' => 'Certificate',
                        'Diploma' => 'Diploma',
                        'Bachelor' => 'Bachelor',
                        'Master' => 'Master',
                        'PhD' => 'PhD',
                    ])
                    ->searchable()
                    ->default('Bachelor'),
                TextInput::make('tuition_fee')
                    ->label('Tuition Fee')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->minValue(0)
                    ->step(0.01)
                    ->placeholder('e.g., 25000.00'),
                TextInput::make('agent_commission')
                    ->label('Agent Commission')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->minValue(0)
                    ->step(0.01)
                    ->placeholder('e.g., 2500.00'),
                TextInput::make('system_commission')
                    ->label('System Commission')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->minValue(0)
                    ->step(0.01)
                    ->placeholder('e.g., 500.00'),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }
}
