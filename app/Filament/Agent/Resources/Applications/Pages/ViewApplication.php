<?php

namespace App\Filament\Agent\Resources\Applications\Pages;

use App\Filament\Agent\Resources\Applications\ApplicationResource;
use App\Models\ApplicationDocument;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class ViewApplication extends ViewRecord
{
    protected static string $resource = ApplicationResource::class;

    protected static ?string $title = 'Application Details';

    public $replacingDocumentId = null;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                // Single fullwidth tabs card
                Tabs::make('Application Details')
                    ->columnSpanFull()
                    ->contained(false)
                    ->tabs([
                        Tab::make('Application Overview')
                            ->schema([
                                // Warning panel for additional documents required - FIRST SECTION
                                Section::make('Document Request')
                                    ->schema([
                                        Placeholder::make('additional_documents_warning')
                                            ->label('')
                                            ->content(function ($record) {
                                                if ($record->status !== 'additional_documents_required' || ! $record->additional_documents_request) {
                                                    return '';
                                                }

                                                return new \Illuminate\Support\HtmlString(
                                                    view('filament.components.additional-documents-warning-simple', [
                                                        'request' => $record->additional_documents_request,
                                                    ])->render()
                                                );
                                            })
                                            ->visible(fn ($record) => $record->status === 'additional_documents_required' && $record->additional_documents_request),
                                    ])
                                    ->visible(fn ($record) => $record->status === 'additional_documents_required' && $record->additional_documents_request),

                                Section::make('Basic Information')
                                    ->schema([
                                        Placeholder::make('application_number')
                                            ->label('Application #')
                                            ->content(fn ($record) => $record->application_number),
                                        Placeholder::make('status')
                                            ->label('Status')
                                            ->content(function ($record) {
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'submitted' => 'info',
                                                    'under_review' => 'warning',
                                                    'additional_documents_required' => 'danger',
                                                    'approved' => 'success',
                                                    'rejected' => 'danger',
                                                    'enrolled' => 'success',
                                                    'cancelled' => 'gray',
                                                ];
                                                $color = $statusColors[$record->status] ?? 'gray';
                                                $label = ucfirst(str_replace('_', ' ', $record->status));

                                                return "<span class=\"fi-badge fi-color-{$color} fi-size-md inline-flex items-center justify-center gap-x-1 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset\">{$label}</span>";
                                            })
                                            ->html(),
                                        Placeholder::make('submitted_status')
                                            ->label('Submission Status')
                                            ->content(function ($record) {
                                                if ($record->submitted_at) {
                                                    return 'Submitted on '.$record->submitted_at->format('M j, Y g:i A');
                                                }

                                                return 'Not yet submitted';
                                            }),
                                    ])
                                    ->columns(3),

                                Section::make('Program & Commission Details')
                                    ->schema([
                                        Placeholder::make('university_name')
                                            ->label('University')
                                            ->content(fn ($record) => $record->program->university->name),
                                        Placeholder::make('program_name')
                                            ->label('Program')
                                            ->content(fn ($record) => $record->program->name),
                                        Placeholder::make('tuition_fee')
                                            ->label('Tuition Fee')
                                            ->content(fn ($record) => '$'.number_format($record->program->tuition_fee ?? 0, 2)),
                                        Placeholder::make('commission_amount')
                                            ->label('Your Commission')
                                            ->content(fn ($record) => '<span class="text-lg font-bold text-green-600">$'.number_format($record->commission_amount ?? 0, 2).'</span>')
                                            ->html(),
                                    ])
                                    ->columns(2),

                                Section::make('Timeline')
                                    ->schema([
                                        Placeholder::make('created_at')
                                            ->label('Created')
                                            ->content(fn ($record) => $record->created_at->format('M j, Y g:i A')),
                                        Placeholder::make('submitted_at')
                                            ->label('Submitted')
                                            ->content(fn ($record) => $record->submitted_at ? $record->submitted_at->format('M j, Y g:i A') : 'Not submitted'),
                                        Placeholder::make('reviewed_at')
                                            ->label('Review Started')
                                            ->content(fn ($record) => $record->reviewed_at ? $record->reviewed_at->format('M j, Y g:i A') : 'Not reviewed'),
                                        Placeholder::make('decision_at')
                                            ->label('Decision Made')
                                            ->content(fn ($record) => $record->decision_at ? $record->decision_at->format('M j, Y g:i A') : 'No decision yet'),
                                    ])
                                    ->columns(2)
                                    ->collapsible(),
                            ]),

                        Tab::make('Student Information')
                            ->schema([
                                Section::make('Personal Details')
                                    ->schema([
                                        Placeholder::make('student_info_display')
                                            ->label('')
                                            ->content(fn ($record) => new \Illuminate\Support\HtmlString(
                                                view('filament.components.application-student-info', [
                                                    'student' => $record->student,
                                                    'studentId' => $record->student_id,
                                                ])->render()
                                            )),
                                    ]),
                            ]),

                        Tab::make('Commission')
                            ->schema([
                                Section::make('Commission Details')
                                    ->schema([
                                        Placeholder::make('commission_amount')
                                            ->label('Commission Amount')
                                            ->content(fn ($record) => $record->commission_amount ? '$'.number_format($record->commission_amount, 2) : 'Not calculated'),
                                        Placeholder::make('commission_status')
                                            ->label('Commission Status')
                                            ->content(function ($record) {
                                                if (! $record->commission_amount) {
                                                    return '<span class="fi-badge fi-color-gray fi-size-md inline-flex items-center justify-center gap-x-1 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset">Not Calculated</span>';
                                                }

                                                $color = $record->commission_paid ? 'success' : 'warning';
                                                $label = $record->commission_paid ? 'Paid' : 'Pending';

                                                return "<span class=\"fi-badge fi-color-{$color} fi-size-md inline-flex items-center justify-center gap-x-1 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset\">{$label}</span>";
                                            })
                                            ->html(),
                                        Placeholder::make('commission_rate')
                                            ->label('Commission Rate')
                                            ->content(function ($record) {
                                                if ($record->program && $record->program->tuition_fee && $record->commission_amount) {
                                                    $rate = ($record->commission_amount / $record->program->tuition_fee) * 100;

                                                    return number_format($rate, 2).'%';
                                                }

                                                return 'N/A';
                                            }),
                                        Placeholder::make('tuition_fee')
                                            ->label('Total Tuition Fee')
                                            ->content(fn ($record) => $record->program->tuition_fee ? '$'.number_format($record->program->tuition_fee, 2) : 'N/A'),
                                    ])
                                    ->columns(2),

                                Section::make('Earning Information')
                                    ->schema([
                                        Placeholder::make('payment_status')
                                            ->label('Payment Status')
                                            ->content(function ($record) {
                                                if ($record->status === 'approved' && $record->commission_amount && ! $record->commission_paid) {
                                                    return '<span class="text-success-600 font-medium">🎉 Commission earned! Payment pending</span>';
                                                } elseif ($record->commission_paid) {
                                                    return '<span class="text-success-600 font-medium">✅ Commission paid</span>';
                                                } elseif ($record->status === 'submitted' || $record->status === 'under_review') {
                                                    return '<span class="text-blue-600 font-medium">⏳ Application under review</span>';
                                                } else {
                                                    return '<span class="text-gray-600">Commission not yet earned</span>';
                                                }
                                            })
                                            ->html(),
                                        Placeholder::make('commission_notes')
                                            ->label('Commission Notes')
                                            ->content(fn ($record) => $record->commission ? $record->commission->notes ?? 'No notes' : 'Commission will be calculated upon approval'),
                                    ])
                                    ->columns(1),
                            ]),

                        Tab::make('Agent Info')
                            ->schema([
                                Section::make('Your Agent Details')
                                    ->schema([
                                        Placeholder::make('agent_name')
                                            ->label('Agent Name')
                                            ->content(fn ($record) => $record->agent->name),
                                        Placeholder::make('agent_email')
                                            ->label('Agent Email')
                                            ->content(fn ($record) => $record->agent->email),
                                        Placeholder::make('agent_role')
                                            ->label('Agent Role')
                                            ->content(fn ($record) => ucfirst(str_replace('_', ' ', $record->agent->role))),
                                        Placeholder::make('agent_status')
                                            ->label('Account Status')
                                            ->content(function ($record) {
                                                $color = $record->agent->is_active ? 'success' : 'danger';
                                                $label = $record->agent->is_active ? 'Active' : 'Inactive';

                                                return "<span class=\"fi-badge fi-color-{$color} fi-size-md inline-flex items-center justify-center gap-x-1 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset\">{$label}</span>";
                                            })
                                            ->html(),
                                    ])
                                    ->columns(2),

                                Section::make('Performance Summary')
                                    ->schema([
                                        Placeholder::make('total_applications')
                                            ->label('Total Applications')
                                            ->content(function ($record) {
                                                $count = \App\Models\Application::where('agent_id', $record->agent_id)->count();

                                                return $count.' application'.($count !== 1 ? 's' : '');
                                            }),
                                        Placeholder::make('approved_applications')
                                            ->label('Approved Applications')
                                            ->content(function ($record) {
                                                $count = \App\Models\Application::where('agent_id', $record->agent_id)
                                                    ->where('status', 'approved')
                                                    ->count();

                                                return $count.' approved';
                                            }),
                                        Placeholder::make('pending_applications')
                                            ->label('Pending Applications')
                                            ->content(function ($record) {
                                                $count = \App\Models\Application::where('agent_id', $record->agent_id)
                                                    ->whereIn('status', ['pending', 'submitted', 'under_review'])
                                                    ->count();

                                                return $count.' pending';
                                            }),
                                        Placeholder::make('total_commission')
                                            ->label('Total Commission Earned')
                                            ->content(function ($record) {
                                                $total = \App\Models\Application::where('agent_id', $record->agent_id)
                                                    ->where('commission_paid', true)
                                                    ->sum('commission_amount');

                                                return '$'.number_format($total, 2);
                                            }),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make('Document Review')
                            ->schema([
                                Section::make('Uploaded Documents')
                                    ->schema([
                                        Placeholder::make('documents_display')
                                            ->label('')
                                            ->content(fn ($record) => new \Illuminate\Support\HtmlString(
                                                view('filament.components.application-documents-list', [
                                                    'documents' => $record->applicationDocuments,
                                                ])->render()
                                            )),
                                    ])
                                    ->headerActions([
                                        Action::make('uploadDocument')
                                            ->label('Upload Document')
                                            ->icon('heroicon-o-arrow-up-tray')
                                            ->color('primary')
                                            ->modalHeading('Upload Supporting Document')
                                            ->modalDescription('Upload a document to support this application. Please provide a clear title so administrators can easily identify the document.')
                                            ->modalWidth('2xl')
                                            ->form([
                                                TextInput::make('title')
                                                    ->label('Document Title')
                                                    ->placeholder('e.g., Passport Copy, Academic Transcript, Bank Statement, Recommendation Letter')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->helperText('Provide a clear, descriptive title for this document'),
                                                FileUpload::make('file')
                                                    ->label('Select File')
                                                    ->required()
                                                    ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                                                    ->maxSize(10240) // 10MB
                                                    ->disk('public')
                                                    ->directory('application-documents')
                                                    ->preserveFilenames()
                                                    ->helperText('Accepted formats: PDF, Images (JPG, PNG), Word documents. Max size: 10MB'),
                                            ])
                                            ->action(function (array $data, ViewApplication $livewire): void {
                                                $record = $livewire->getRecord();

                                                // Get file info
                                                $filePath = $data['file'];
                                                $fileName = basename($filePath);

                                                // Get file details from storage
                                                $fileSize = Storage::disk('public')->size($filePath);
                                                $mimeType = Storage::disk('public')->mimeType($filePath);

                                                // Create document record
                                                ApplicationDocument::create([
                                                    'application_id' => $record->id,
                                                    'uploaded_by_user_id' => auth()->id(),
                                                    'title' => $data['title'],
                                                    'original_filename' => $fileName,
                                                    'disk' => 'public',
                                                    'path' => $filePath,
                                                    'file_size' => $fileSize,
                                                    'mime_type' => $mimeType,
                                                ]);

                                                Notification::make()
                                                    ->title('Document uploaded successfully')
                                                    ->body('The document "'.$data['title'].'" has been uploaded to this application.')
                                                    ->success()
                                                    ->send();

                                                // Refresh the page to show the new document
                                                $livewire->dispatch('$refresh');
                                            })
                                            ->successNotificationTitle('Document uploaded!'),
                                    ]),
                            ]),

                        Tab::make('Application Timeline')
                            ->schema([
                                Section::make('Application Activity')
                                    ->schema([
                                        Placeholder::make('timeline_display')
                                            ->label('')
                                            ->content(function ($record) {
                                                return new \Illuminate\Support\HtmlString(
                                                    view('filament.components.application-timeline', [
                                                        'logs' => $record->applicationLogs()
                                                            ->with('user')
                                                            ->orderBy('created_at', 'desc')
                                                            ->get(),
                                                    ])->render()
                                                );
                                            }),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        $actions = [];

        // Only show edit action if application can be edited
        if ($this->getRecord()->canBeEdited()) {
            $actions[] = EditAction::make();
        }

        return $actions;
    }

    public function handleReplaceDocument($documentId): void
    {
        $this->replacingDocumentId = $documentId;
        $this->mountAction('replaceDocumentModal');
    }

    public function replaceDocumentModalAction(): Action
    {
        return Action::make('replaceDocumentModal')
            ->modalHeading(function () {
                $document = ApplicationDocument::find($this->replacingDocumentId);

                return 'Replace: '.($document?->title ?? 'Document');
            })
            ->modalDescription('Upload a new file to replace the current document. The title and other details will remain the same.')
            ->modalWidth('xl')
            ->form([
                FileUpload::make('file')
                    ->label('Select New File')
                    ->required()
                    ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                    ->maxSize(10240)
                    ->disk('public')
                    ->directory('application-documents')
                    ->preserveFilenames()
                    ->helperText('Accepted formats: PDF, Images (JPG, PNG), Word documents. Max size: 10MB'),
            ])
            ->action(function (array $data): void {
                $document = ApplicationDocument::find($this->replacingDocumentId);

                if (! $document || $document->application_id !== $this->record->id) {
                    Notification::make()
                        ->title('Document not found')
                        ->danger()
                        ->send();

                    return;
                }

                // Delete old file
                if (Storage::disk($document->disk)->exists($document->path)) {
                    Storage::disk($document->disk)->delete($document->path);
                }

                // Get new file info
                $filePath = $data['file'];
                $fileName = basename($filePath);
                $fileSize = Storage::disk('public')->size($filePath);
                $mimeType = Storage::disk('public')->mimeType($filePath);

                // Update document record
                $document->update([
                    'original_filename' => $fileName,
                    'path' => $filePath,
                    'file_size' => $fileSize,
                    'mime_type' => $mimeType,
                    'uploaded_by_user_id' => auth()->id(),
                ]);

                Notification::make()
                    ->title('Document replaced successfully')
                    ->body('The file has been replaced while keeping the same title.')
                    ->success()
                    ->send();

                $this->replacingDocumentId = null;
            })
            ->successNotificationTitle('Document replaced!');
    }

    protected function getActions(): array
    {
        return [
            $this->replaceDocumentModalAction(),
        ];
    }
}
