<?php

namespace App\Filament\Resources\Universities\Schemas;

use App\Helpers\CountriesHelper;
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
                        Select::make('university_type')
                            ->label('University Type')
                            ->required()
                            ->options([
                                'public' => 'Public',
                                'private' => 'Private',
                            ])
                            ->default('public'),
                        Select::make('country')
                            ->label('Country')
                            ->required()
                            ->options(CountriesHelper::getCountries())
                            ->default('Turkey')
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('city', null)),
                        Select::make('city')
                            ->label('City')
                            ->options(function (callable $get) {
                                $country = $get('country');
                                if (! $country) {
                                    return CountriesHelper::getCitiesForCountry('Turkey');
                                }

                                return CountriesHelper::getCitiesForCountry($country);
                            })
                            ->searchable()
                            ->nullable()
                            ->default('İstanbul'),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columns(2),

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
