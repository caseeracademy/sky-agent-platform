<?php

namespace App\Filament\Resources\Universities\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UniversityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
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
                    ]),

                Section::make('Scholarship Requirements')
                    ->description('Set scholarship thresholds for the university contract and agent rewards')
                    ->schema([
                        Repeater::make('scholarship_requirements')
                            ->label('Degree Type Scholarship Requirements')
                            ->schema([
                                Select::make('degree_type')
                                    ->label('Degree Type')
                                    ->required()
                                    ->options([
                                        'Certificate' => 'Certificate',
                                        'Diploma' => 'Diploma',
                                        'Bachelor' => 'Bachelor',
                                        'Master' => 'Master',
                                        'PhD' => 'PhD',
                                    ])
                                    ->searchable(),
                                TextInput::make('system_threshold')
                                    ->label('Students Required for System Scholarship')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->placeholder('e.g., 4')
                                    ->helperText('How many students Sky needs to earn 1 scholarship FROM this university'),
                                TextInput::make('agent_threshold')
                                    ->label('Students Required for Agent Scholarship')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->placeholder('e.g., 5')
                                    ->helperText('How many students an agent needs to earn 1 scholarship FROM Sky'),
                            ])
                            ->columns(3)
                            ->addActionLabel('Add Degree Type')
                            ->reorderable(false)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['degree_type'] ?? null)
                            ->default([])
                            ->nullable(),
                    ])
                    ->collapsible(),
            ]);
    }
}
