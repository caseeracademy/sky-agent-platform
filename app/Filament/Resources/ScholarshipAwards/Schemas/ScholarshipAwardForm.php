<?php

namespace App\Filament\Resources\ScholarshipAwards\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ScholarshipAwardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('award_number')
                    ->required(),
                Select::make('agent_id')
                    ->relationship('agent', 'name')
                    ->required(),
                Select::make('university_id')
                    ->relationship('university', 'name')
                    ->required(),
                TextInput::make('degree_type')
                    ->required(),
                TextInput::make('qualifying_applications_count')
                    ->required()
                    ->numeric(),
                Select::make('status')
                    ->options(['pending' => 'Pending', 'approved' => 'Approved', 'paid' => 'Paid', 'cancelled' => 'Cancelled'])
                    ->default('pending')
                    ->required(),
                Textarea::make('notes')
                    ->columnSpanFull(),
                DateTimePicker::make('awarded_at')
                    ->required(),
                DateTimePicker::make('approved_at'),
                DateTimePicker::make('paid_at'),
            ]);
    }
}
