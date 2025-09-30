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
use Illuminate\Support\Str;

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
                                        Placeholder::make('student_name')
                                            ->label('Student Name')
                                            ->content(fn ($record) => $record->student->name),
                                        Placeholder::make('student_email')
                                            ->label('Email')
                                            ->content(fn ($record) => $record->student->email),
                                        Placeholder::make('student_phone')
                                            ->label('Phone')
                                            ->content(fn ($record) => $record->student->phone ?? 'Not provided'),
                                        Placeholder::make('student_nationality')
                                            ->label('Nationality')
                                            ->content(fn ($record) => $record->student->nationality ?? 'Not provided'),
                                        Placeholder::make('student_dob')
                                            ->label('Date of Birth')
                                            ->content(fn ($record) => $record->student->date_of_birth ? $record->student->date_of_birth->format('M j, Y') : 'Not provided'),
                                        Placeholder::make('student_gender')
                                            ->label('Gender')
                                            ->content(fn ($record) => $record->student->gender ? ucfirst($record->student->gender) : 'Not provided'),
                                    ])
                                    ->columns(3),
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
                                        Placeholder::make('timeline_cards')
                                            ->label('')
                                            ->content(function ($record) {
                                                $logs = $record->applicationLogs()
                                                    ->with('user')
                                                    ->orderBy('created_at', 'desc')
                                                    ->get();

                                                if ($logs->isEmpty()) {
                                                    return '<div class="text-center py-12 text-gray-500">
                                                        <div class="text-lg font-medium">No activity recorded</div>
                                                        <div class="text-sm">Activity will appear here as you progress the application.</div>
                                                    </div>';
                                                }

                                                $html = '<div class="space-y-6">';
                                                foreach ($logs as $log) {
                                                    $userName = $log->user->name ?? 'System';
                                                    $userRole = $log->user ? Str::of($log->user->role)->headline() : 'System';
                                                    $timestamp = $log->created_at->format('M j, Y g:i A');
                                                    $timeAgo = $log->created_at->diffForHumans();

                                                    $statusData = match (true) {
                                                        str_contains(strtolower($log->note), 'created') => ['NEW APPLICATION', 'bg-sky-100 text-sky-800', 'bg-sky-500', 'bg-sky-100 text-sky-800'],
                                                        str_contains(strtolower($log->note), 'submitted') => ['SUBMITTED', 'bg-blue-100 text-blue-800', 'bg-blue-500', 'bg-blue-100 text-blue-800'],
                                                        str_contains(strtolower($log->note), 'approved') => ['OFFER SENT', 'bg-amber-100 text-amber-800', 'bg-amber-500', 'bg-amber-100 text-amber-800'],
                                                        str_contains(strtolower($log->note), 'rejected') => ['DECLINED', 'bg-rose-100 text-rose-800', 'bg-rose-500', 'bg-rose-100 text-rose-800'],
                                                        str_contains(strtolower($log->note), 'under review') => ['IN REVIEW', 'bg-indigo-100 text-indigo-800', 'bg-indigo-500', 'bg-indigo-100 text-indigo-800'],
                                                        str_contains(strtolower($log->note), 'document') => ['DOCUMENT UPDATE', 'bg-violet-100 text-violet-800', 'bg-violet-500', 'bg-violet-100 text-violet-800'],
                                                        default => ['UPDATED', 'bg-slate-100 text-slate-800', 'bg-slate-500', 'bg-slate-100 text-slate-800'],
                                                    };

                                                    [$statusLabel, $badgeClasses, $dotClasses, $cardAccent] = $statusData;

                                                    $html .= '<div class="grid gap-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                                                        <div class="flex items-center justify-between">
                                                            <div class="flex items-center space-x-3">
                                                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-inner">
                                                                    <div class="h-3.5 w-3.5 rounded-full '.$dotClasses.'"></div>
                                                                </div>
                                                                <div>
                                                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold '.$badgeClasses.'">'.e($statusLabel).'</span>
                                                                    <p class="mt-1 text-sm font-medium text-gray-900">'.e($log->note).'</p>
                                                                </div>
                                                            </div>
                                                            <div class="text-right text-sm text-gray-500">
                                                                <p>'.e($timestamp).'</p>
                                                                <p>'.e($timeAgo).'</p>
                                                            </div>
                                                        </div>

                                                        <div class="grid gap-3 rounded-xl bg-gray-50 p-4 text-sm text-gray-600">
                                                            <div class="flex items-start">
                                                                <span class="mt-1 flex h-6 w-6 items-center justify-center rounded-full '.$cardAccent.'">
                                                                    <svg class="h-3 w-3 text-current" viewBox="0 0 20 20" fill="currentColor">
                                                                        <path fill-rule="evenodd" d="M10 3a1 1 0 01.894.553l6 12A1 1 0 0116 17H4a1 1 0 01-.894-1.447l6-12A1 1 0 0110 3zm0 3a1 1 0 100 2 1 1 0 000-2zm-1 5a1 1 0 112 0v2a1 1 0 11-2 0v-2z" clip-rule="evenodd"></path>
                                                                    </svg>
                                                                </span>
                                                                <div class="ml-3">
                                                                    <p class="font-semibold text-gray-900">Log Notes</p>
                                                                    <p class="text-gray-600">'.e(Str::of($log->note)->ucfirst()).'</p>
                                                                </div>
                                                            </div>

                                                            <div class="flex items-start">
                                                                <span class="mt-1 flex h-6 w-6 items-center justify-center rounded-full '.$cardAccent.'">
                                                                    <svg class="h-3 w-3 text-current" viewBox="0 0 20 20" fill="currentColor">
                                                                        <path fill-rule="evenodd" d="M6 3a1 1 0 00-.894.553L2.382 9H17.618l-2.724-5.447A1 1 0 0014 3H6zm2 10a2 2 0 104 0V9H8v4z" clip-rule="evenodd"></path>
                                                                    </svg>
                                                                </span>
                                                                <div class="ml-3">
                                                                    <p class="font-semibold text-gray-900">Status Change</p>
                                                                    <p class="text-gray-600">'.($log->status_change ? e(Str::of($log->status_change)->headline()) : 'No status change recorded.').'</p>
                                                                </div>
                                                            </div>

                                                            <div class="flex items-start">
                                                                <span class="mt-1 flex h-6 w-6 items-center justify-center rounded-full '.$cardAccent.'">
                                                                    <svg class="h-3 w-3 text-current" viewBox="0 0 20 20" fill="currentColor">
                                                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                                                    </svg>
                                                                </span>
                                                                <div class="ml-3">
                                                                    <p class="font-semibold text-gray-900">Performed By</p>
                                                                    <p class="text-gray-600">'.e($userName).' <span class="text-xs text-gray-400">('.e(Str::headline($userRole)).')</span></p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>';
                                                }

                                                $html .= '</div>';

                                                return $html;
                                            })
                                            ->html(),
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
