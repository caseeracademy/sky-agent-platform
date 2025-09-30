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
                    ->relationship('student', 'name')
                    ->disabled()
                    ->label('Student'),
                Select::make('program_id')
                    ->relationship('program', 'name')
                    ->disabled()
                    ->label('Program'),
                Select::make('agent_id')
                    ->relationship('agent', 'name')
                    ->disabled()
                    ->label('Agent'),
                Select::make('assigned_admin_id')
                    ->relationship('assignedAdmin', 'name')
                    ->label('Assigned Admin')
                    ->searchable()
                    ->preload(),
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
                    ->label('Status'),
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
