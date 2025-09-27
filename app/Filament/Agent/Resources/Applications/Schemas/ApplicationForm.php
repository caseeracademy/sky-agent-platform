<?php

namespace App\Filament\Agent\Resources\Applications\Schemas;

use App\Models\Program;
use App\Models\Student;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\View;
use Filament\Schemas\Schema;

class ApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('agent_id')
                    ->default(auth()->id())
                    ->required(),
                TextInput::make('application_number')
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder('Will be auto-generated'),
                Select::make('student_id')
                    ->label('Student')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->options(function () {
                        return Student::where('agent_id', auth()->id())
                            ->pluck('name', 'id');
                    })
                    ->createOptionForm([
                        TextInput::make('name')->required(),
                        TextInput::make('email')->email()->required(),
                    ]),
                Select::make('program_id')
                    ->label('Program')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->options(function () {
                        return Program::with('university')
                            ->active()
                            ->get()
                            ->mapWithKeys(function ($program) {
                                return [$program->id => "{$program->name} ({$program->university->name})"];
                            });
                    })
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $program = Program::find($state);
                            if ($program) {
                                $set('commission_amount', $program->agent_commission);
                            }
                        }
                    }),
                DatePicker::make('intake_date')
                    ->label('Preferred Intake Date')
                    ->minDate(now()),
                Textarea::make('notes')
                    ->label('Additional Notes')
                    ->placeholder('Any additional information about the application...')
                    ->rows(3)
                    ->columnSpanFull(),
                TextInput::make('commission_amount')
                    ->label('Commission Amount')
                    ->numeric()
                    ->prefix('$')
                    ->disabled()
                    ->dehydrated()
                    ->helperText('Automatically calculated based on program'),
                
                Section::make('Documents')
                    ->schema([
                        FileUpload::make('document_uploads')
                            ->label('Upload Supporting Documents')
                            ->multiple()
                            ->disk('public')
                            ->directory('application-documents')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'])
                            ->maxSize(10240) // 10MB max
                            ->helperText('Accepted: PDF, JPG, PNG. Max size: 10MB per file.')
                            ->dehydrated(false)
                            ->visible(fn ($record) => $record !== null), // Only show on edit
                    ])
                    ->collapsible()
                    ->visible(fn ($record) => $record !== null), // Only show on edit
            ]);
    }
}
