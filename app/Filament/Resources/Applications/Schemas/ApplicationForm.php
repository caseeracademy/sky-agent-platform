<?php

namespace App\Filament\Resources\Applications\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\View;
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
                        \Filament\Forms\Components\Placeholder::make('documents_list')
                            ->label('Uploaded Documents')
                            ->content(function ($record) {
                                if (!$record || !$record->applicationDocuments->count()) {
                                    return 'No documents uploaded yet.';
                                }

                                $html = '<div class="space-y-3">';
                                foreach ($record->applicationDocuments as $document) {
                                    $downloadUrl = $document->download_url;
                                    $fileIcon = str_contains($document->mime_type ?? '', 'pdf') ? '📄' : '🖼️';
                                    $uploadedBy = $document->uploadedByUser->name;
                                    $uploadedAt = $document->created_at->diffForHumans();
                                    $fileSize = $document->formatted_file_size;
                                    
                                    $html .= "<div class='flex items-center justify-between p-3 bg-gray-50 rounded-lg'>";
                                    $html .= "<div class='flex items-center space-x-3'>";
                                    $html .= "<span class='text-2xl'>{$fileIcon}</span>";
                                    $html .= "<div>";
                                    $html .= "<p class='font-medium text-gray-900'>{$document->original_filename}</p>";
                                    $html .= "<p class='text-sm text-gray-500'>{$fileSize} • Uploaded {$uploadedAt} by {$uploadedBy}</p>";
                                    $html .= "</div>";
                                    $html .= "</div>";
                                    $html .= "<div>";
                                    $html .= "<a href='{$downloadUrl}' target='_blank' class='inline-flex items-center px-3 py-1 border border-blue-300 text-sm font-medium rounded text-blue-700 bg-white hover:bg-blue-50'>Download</a>";
                                    $html .= "</div>";
                                    $html .= "</div>";
                                }
                                $html .= '</div>';

                                return new \Illuminate\Support\HtmlString($html);
                            })
                            ->visible(fn ($record) => $record !== null),
                    ])
                    ->collapsible()
                    ->visible(fn ($record) => $record !== null),
            ]);
    }
}
