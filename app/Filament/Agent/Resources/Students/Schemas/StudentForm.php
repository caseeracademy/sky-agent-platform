<?php

namespace App\Filament\Agent\Resources\Students\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('agent_id')
                    ->default(auth()->id())
                    ->required(),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g., John Smith'),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->placeholder('e.g., john.smith@email.com'),
                TextInput::make('phone_number')
                    ->tel()
                    ->maxLength(255)
                    ->placeholder('e.g., +1 (555) 123-4567'),
                Select::make('country_of_residence')
                    ->searchable()
                    ->options([
                        'Afghanistan' => 'Afghanistan',
                        'Albania' => 'Albania',
                        'Algeria' => 'Algeria',
                        'Argentina' => 'Argentina',
                        'Australia' => 'Australia',
                        'Bangladesh' => 'Bangladesh',
                        'Brazil' => 'Brazil',
                        'Canada' => 'Canada',
                        'China' => 'China',
                        'Egypt' => 'Egypt',
                        'France' => 'France',
                        'Germany' => 'Germany',
                        'India' => 'India',
                        'Indonesia' => 'Indonesia',
                        'Iran' => 'Iran',
                        'Iraq' => 'Iraq',
                        'Italy' => 'Italy',
                        'Japan' => 'Japan',
                        'Jordan' => 'Jordan',
                        'Kenya' => 'Kenya',
                        'Lebanon' => 'Lebanon',
                        'Mexico' => 'Mexico',
                        'Nigeria' => 'Nigeria',
                        'Pakistan' => 'Pakistan',
                        'Philippines' => 'Philippines',
                        'Russia' => 'Russia',
                        'Saudi Arabia' => 'Saudi Arabia',
                        'South Africa' => 'South Africa',
                        'South Korea' => 'South Korea',
                        'Syria' => 'Syria',
                        'Turkey' => 'Turkey',
                        'Ukraine' => 'Ukraine',
                        'United Kingdom' => 'United Kingdom',
                        'United States' => 'United States',
                        'Vietnam' => 'Vietnam',
                        'Other' => 'Other',
                    ])
                    ->placeholder('Select country of residence'),
                DatePicker::make('date_of_birth')
                    ->label('Date of Birth')
                    ->maxDate(now()->subYears(16))
                    ->placeholder('Select date of birth')
                    ->displayFormat('Y-m-d')
                    ->helperText('Student must be at least 16 years old'),
            ]);
    }
}
