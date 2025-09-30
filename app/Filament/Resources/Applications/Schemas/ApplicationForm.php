<?php

namespace App\Filament\Resources\Applications\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('application_number')
                    ->disabled()
                    ->label('Application Number'),
                Select::make('student_id')
                    ->disabled()
                    ->label('Student')
                    ->options(function ($record) {
                        if (! $record || ! $record->student) {
                            return [];
                        }

                        $student = $record->student;
                        // Ensure we always have a valid name
                        $name = $student->name;
                        if (! $name) {
                            $name = trim(($student->first_name ?? '').' '.($student->last_name ?? ''));
                        }
                        // Fallback to email if name is still empty
                        if (! $name) {
                            $name = $student->email ?? 'Unknown Student';
                        }

                        return [$student->id => $name];
                    }),
                Select::make('program_id')
                    ->disabled()
                    ->label('Program')
                    ->options(function ($record) {
                        if (! $record || ! $record->program) {
                            return [];
                        }

                        $program = $record->program;
                        $programName = $program->name ?? 'Unknown Program';
                        $universityName = $program->university->name ?? 'Unknown University';

                        return [$program->id => "{$programName} ({$universityName})"];
                    }),
                Select::make('agent_id')
                    ->disabled()
                    ->label('Agent')
                    ->options(function ($record) {
                        if (! $record || ! $record->agent) {
                            return [];
                        }

                        $agent = $record->agent;

                        return [$agent->id => $agent->name ?? 'Unknown Agent'];
                    }),
                Select::make('assigned_admin_id')
                    ->label('Assigned Admin')
                    ->searchable()
                    ->preload()
                    ->options(function () {
                        return \App\Models\User::where('role', 'super_admin')
                            ->get()
                            ->mapWithKeys(function ($admin) {
                                return [$admin->id => $admin->name ?? 'Unknown Admin'];
                            });
                    }),
                Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'submitted' => 'Submitted',
                        'under_review' => 'Under Review',
                        'additional_documents_required' => 'Additional Documents Required',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'enrolled' => 'Enrolled',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required()
                    ->label('Status')
                    ->reactive(),
                Textarea::make('additional_documents_request')
                    ->label('Document Request Details')
                    ->placeholder('Please specify what documents are missing. For example: "Please provide an updated transcript with final grades for semester 4" or "We need a copy of your IELTS certificate with a score of 6.5 or higher".')
                    ->rows(4)
                    ->helperText('Be specific about what documents are needed and any requirements or deadlines.')
                    ->visible(fn (callable $get) => $get('status') === 'additional_documents_required')
                    ->required(fn (callable $get) => $get('status') === 'additional_documents_required'),
                Textarea::make('notes')
                    ->disabled()
                    ->label('Agent Notes')
                    ->columnSpanFull(),
                Textarea::make('admin_notes')
                    ->label('Admin Notes')
                    ->placeholder('Add internal notes for this application...')
                    ->columnSpanFull(),
                DatePicker::make('intake_date')
                    ->disabled()
                    ->label('Preferred Intake Date'),
                TextInput::make('commission_amount')
                    ->disabled()
                    ->numeric()
                    ->prefix('$')
                    ->label('Commission Amount'),
                Toggle::make('commission_paid')
                    ->label('Commission Paid'),
                DateTimePicker::make('submitted_at')
                    ->disabled()
                    ->label('Submitted At'),
                DateTimePicker::make('reviewed_at')
                    ->label('Reviewed At'),
                DateTimePicker::make('decision_at')
                    ->label('Decision At'),

                Section::make('Application Documents')
                    ->schema([
                        Placeholder::make('documents_display')
                            ->label('')
                            ->content(fn ($record) => new \Illuminate\Support\HtmlString(
                                view('filament.components.application-documents-list-admin', [
                                    'documents' => $record?->applicationDocuments ?? collect(),
                                ])->render()
                            ))
                            ->visible(fn ($record) => $record !== null),

                        Repeater::make('new_documents')
                            ->label('Upload New Documents')
                            ->schema([
                                TextInput::make('title')
                                    ->label('Document Title')
                                    ->placeholder('e.g., Passport, Transcript, Recommendation Letter')
                                    ->required()
                                    ->maxLength(255),
                                FileUpload::make('file')
                                    ->label('File')
                                    ->required()
                                    ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                                    ->maxSize(10240) // 10MB
                                    ->disk('public')
                                    ->directory('application-documents')
                                    ->preserveFilenames(),
                            ])
                            ->columns(2)
                            ->addActionLabel('Add Document')
                            ->defaultItems(0)
                            ->reorderable(false)
                            ->collapsible()
                            ->visible(fn ($record) => $record !== null),
                    ])
                    ->description('Upload supporting documents for this application with descriptive titles')
                    ->collapsible()
                    ->visible(fn ($record) => $record !== null),
            ]);
    }
}
