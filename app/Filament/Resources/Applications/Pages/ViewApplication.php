<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Filament\Resources\Applications\ApplicationResource;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ViewApplication extends ViewRecord
{
    protected static string $resource = ApplicationResource::class;

    protected static ?string $title = 'Application Hub';

    protected function getHeaderActions(): array
    {
        $application = $this->getRecord();
        $statusService = app(\App\Services\ApplicationStatusService::class);
        $userRole = auth()->user()->role ?? 'guest';
        $availableActions = $statusService->getAvailableActions($application, $userRole);

        $actions = [];

        foreach ($availableActions as $actionData) {
            $status = $actionData['status'];
            $label = $actionData['label'];
            $color = $actionData['color'];
            $requiresInput = $actionData['requires_input'];

            $action = Action::make($status)
                ->label($label)
                ->color($color);

            // Special handling for offer_received - requires offer letter upload
            if ($status === 'offer_received') {
                $action->form([
                    \Filament\Forms\Components\FileUpload::make('offer_letter')
                        ->label('📄 Upload Offer Letter')
                        ->required()
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'])
                        ->maxSize(10240) // 10MB
                        ->disk('public')
                        ->directory('offer-letters')
                        ->preserveFilenames()
                        ->helperText('Upload the official offer letter from the university. Accepted formats: PDF, Images (JPG, PNG). Max size: 10MB'),
                    Textarea::make('offer_details')
                        ->label('Offer Details (Optional)')
                        ->placeholder('Add any additional notes about the offer (deadline, conditions, scholarship amount, etc.)...')
                        ->rows(4)
                        ->helperText('These notes will be visible to the agent.'),
                ])
                    ->modalHeading('📄 Upload Offer Letter')
                    ->modalDescription('Upload the official offer letter that the student received from the university.')
                    ->modalSubmitActionLabel('Upload & Mark as Offer Received')
                    ->action(function (array $data) use ($status) {
                        $this->uploadOfferLetterAndChangeStatus($status, $data);
                    });
            }
            // If requires input for other statuses, add modal with form
            elseif ($requiresInput || $status === 'additional_documents_needed') {
                $action->form([
                    Textarea::make('note')
                        ->label('Document Request Details')
                        ->placeholder('Describe which documents are needed and why...')
                        ->required()
                        ->rows(6)
                        ->helperText('💡 Be specific about what documents are needed. The agent will see this message.'),
                ])
                    ->action(function (array $data) use ($status) {
                        $this->changeApplicationStatusWithNote($status, $data['note'] ?? null);
                    });
            } else {
                // Direct action without modal
                $action->requiresConfirmation($status === 'approved')
                    ->action(function () use ($status) {
                        $this->changeApplicationStatus($status);
                    });
            }

            $actions[] = $action;
        }

        return $actions;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                // Single fullwidth tabs card
                Tabs::make('Application Hub')
                    ->columnSpanFull()
                    ->contained(false)
                    ->tabs([
                        Tab::make('Application Overview')
                            ->schema([
                                // Commission Type Selector (only if needs_review AND commission_type is NULL)
                                Section::make('Initial Review Required')
                                    ->schema([
                                        Placeholder::make('commission_type_selector')
                                            ->label('')
                                            ->content(fn ($record) => new \Illuminate\Support\HtmlString(
                                                view('filament.components.commission-type-selector', [
                                                    'application' => $record,
                                                ])->render()
                                            )),
                                    ])
                                    ->visible(fn ($record) => $record->status === 'needs_review' && $record->commission_type === null),

                                // Status Actions now in header - showing current status only
                                Section::make('Current Status')
                                    ->schema([
                                        Placeholder::make('current_status_display')
                                            ->label('Application Status')
                                            ->content(function ($record) {
                                                $statusService = app(\App\Services\ApplicationStatusService::class);
                                                $allStatuses = \App\Services\ApplicationStatusService::getAllStatuses();
                                                $currentStatus = $allStatuses[$record->status] ?? ['label' => $record->status, 'color' => 'gray'];

                                                $colorMap = [
                                                    'success' => 'text-green-600 bg-green-100',
                                                    'danger' => 'text-red-600 bg-red-100',
                                                    'warning' => 'text-yellow-600 bg-yellow-100',
                                                    'info' => 'text-blue-600 bg-blue-100',
                                                    'gray' => 'text-gray-600 bg-gray-100',
                                                ];

                                                $colorClass = $colorMap[$currentStatus['color']] ?? $colorMap['gray'];

                                                return new \Illuminate\Support\HtmlString(
                                                    '<div class="flex items-center gap-3">
                                                        <span class="text-sm font-semibold">Current Status:</span>
                                                        <span class="px-4 py-2 rounded-full font-bold text-lg '.$colorClass.'">
                                                            '.$currentStatus['label'].'
                                                        </span>
                                                        <p class="text-sm text-gray-500">Use the action buttons in the page header to change the application status.</p>
                                                    </div>'
                                                );
                                            })
                                            ->columnSpanFull(),
                                    ])
                                    ->visible(fn ($record) => $record->commission_type !== null),

                                Section::make('Basic Information')
                                    ->schema([
                                        Placeholder::make('application_number')
                                            ->label('Application #')
                                            ->content(fn ($record) => $record->application_number),
                                        Placeholder::make('status')
                                            ->label('Status')
                                            ->content(function ($record) {
                                                $statusColors = [
                                                    'needs_review' => 'warning',
                                                    'pending' => 'warning',
                                                    'submitted' => 'info',
                                                    'under_review' => 'warning',
                                                    'additional_documents_needed' => 'danger',
                                                    'waiting_to_apply' => 'warning',
                                                    'applied' => 'info',
                                                    'offer_received' => 'success',
                                                    'payment_pending' => 'warning',
                                                    'payment_approval' => 'info',
                                                    'ready_for_approval' => 'info',
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
                                        Placeholder::make('agent_name')
                                            ->label('Agent')
                                            ->content(fn ($record) => $record->agent->name),
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
                                            ->label('Commission Amount')
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
                                                    'isAdmin' => true,
                                                ])->render()
                                            )),
                                    ]),
                            ]),

                        Tab::make('Document Review')
                            ->schema([
                                Section::make('Uploaded Documents')
                                    ->schema([
                                        Placeholder::make('documents_list')
                                            ->label('Documents')
                                            ->content(fn ($record) => new \Illuminate\Support\HtmlString(
                                                view('filament.components.application-documents-list-admin', [
                                                    'documents' => $record->applicationDocuments ?? collect(),
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

                                Section::make('Payment Information')
                                    ->schema([
                                        Placeholder::make('payment_status')
                                            ->label('Payment Status')
                                            ->content(function ($record) {
                                                if ($record->status === 'approved' && $record->commission_amount && ! $record->commission_paid) {
                                                    return '<span class="text-warning-600 font-medium">Commission eligible - pending payment</span>';
                                                } elseif ($record->commission_paid) {
                                                    return '<span class="text-success-600 font-medium">Commission paid</span>';
                                                } else {
                                                    return '<span class="text-gray-600">Commission not yet eligible</span>';
                                                }
                                            })
                                            ->html(),
                                        Placeholder::make('commission_notes')
                                            ->label('Commission Notes')
                                            ->content(fn ($record) => $record->commission ? $record->commission->notes ?? 'No notes' : 'No commission record yet'),
                                    ])
                                    ->columns(1)
                                    ->visible(fn ($record) => $record->status === 'approved' || $record->commission_paid),
                            ]),

                        Tab::make('Agent Info')
                            ->schema([
                                Section::make('Agent Details')
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
                                            ->label('Agent Status')
                                            ->content(function ($record) {
                                                $color = $record->agent->is_active ? 'success' : 'danger';
                                                $label = $record->agent->is_active ? 'Active' : 'Inactive';

                                                return "<span class=\"fi-badge fi-color-{$color} fi-size-md inline-flex items-center justify-center gap-x-1 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset\">{$label}</span>";
                                            })
                                            ->html(),
                                    ])
                                    ->columns(2),

                                Section::make('Agency Information')
                                    ->schema([
                                        Placeholder::make('agency_info')
                                            ->label('Agency Type')
                                            ->content(function ($record) {
                                                if ($record->agent->role === 'agent_owner') {
                                                    return 'Agency Owner';
                                                } elseif ($record->agent->role === 'agent_staff' && $record->agent->parent_agent_id) {
                                                    $parentAgent = \App\Models\User::find($record->agent->parent_agent_id);

                                                    return $parentAgent ? "Staff member under: {$parentAgent->name}" : 'Staff member';
                                                }

                                                return 'Independent Agent';
                                            }),
                                        Placeholder::make('agent_joined')
                                            ->label('Agent Since')
                                            ->content(fn ($record) => $record->agent->created_at->format('M j, Y')),
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
                                    ])
                                    ->columns(2),

                                Section::make('Contact Information')
                                    ->schema([
                                        Placeholder::make('agent_contact')
                                            ->label('Contact Details')
                                            ->content(function ($record) {
                                                $contact = [];
                                                if ($record->agent->phone) {
                                                    $contact[] = "Phone: {$record->agent->phone}";
                                                }
                                                if ($record->agent->address) {
                                                    $contact[] = "Address: {$record->agent->address}";
                                                }

                                                return ! empty($contact) ? implode('<br>', $contact) : 'No additional contact information';
                                            })
                                            ->html(),
                                    ])
                                    ->columns(1)
                                    ->collapsible(),
                            ]),

                        Tab::make('Audit Log')
                            ->schema([
                                Section::make('Application History')
                                    ->schema([
                                        Placeholder::make('audit_log_timeline')
                                            ->label('')
                                            ->content(function ($record) {
                                                $logs = $record->applicationLogs()
                                                    ->with('user')
                                                    ->orderBy('created_at', 'desc')
                                                    ->get();

                                                if ($logs->isEmpty()) {
                                                    return '<div class="text-center py-12 text-gray-500">
                                                        <div class="text-lg font-medium">No activity recorded</div>
                                                        <div class="text-sm">No activity has been recorded for this application yet.</div>
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
                                                        str_contains(strtolower($log->note), 'approved') => ['APPROVED', 'bg-emerald-100 text-emerald-800', 'bg-emerald-500', 'bg-emerald-100 text-emerald-800'],
                                                        str_contains(strtolower($log->note), 'rejected') => ['REJECTED', 'bg-rose-100 text-rose-800', 'bg-rose-500', 'bg-rose-100 text-rose-800'],
                                                        str_contains(strtolower($log->note), 'under review') => ['IN REVIEW', 'bg-amber-100 text-amber-800', 'bg-amber-500', 'bg-amber-100 text-amber-800'],
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

    /**
     * Handle status change from beautiful status buttons.
     */
    public function changeApplicationStatus(string $newStatus, ?string $reason = null, ?array $data = null): void
    {
        $statusService = app(\App\Services\ApplicationStatusService::class);
        $application = $this->getRecord();

        // Validate transition
        if (! $statusService->canTransitionTo($application, $newStatus)) {
            Notification::make()
                ->title('Invalid Status Transition')
                ->body('This status change is not allowed.')
                ->danger()
                ->send();

            return;
        }

        // Perform transition
        $success = $statusService->transitionTo($application, $newStatus, $reason, $data);

        if ($success) {
            Notification::make()
                ->title('✅ Status Updated')
                ->body('Application status updated successfully! Refreshing page...')
                ->success()
                ->duration(2000)
                ->send();

            // Redirect to refresh page and show new status immediately
            $this->redirect($this->getResource()::getUrl('view', ['record' => $application->id]));
        } else {
            Notification::make()
                ->title('❌ Update Failed')
                ->body('Failed to update application status.')
                ->danger()
                ->send();
        }
    }

    /**
     * Listen for events from components.
     */
    protected function getListeners(): array
    {
        return [
            'changeStatus' => 'handleStatusChange',
            'selectCommissionType' => 'handleCommissionTypeSelection',
        ];
    }

    /**
     * Handle commission type selection.
     */
    public function selectCommissionType(string $commissionType): void
    {
        $application = $this->getRecord();

        // Validate we're in needs_review status
        if ($application->status !== 'needs_review') {
            Notification::make()
                ->title('Invalid Action')
                ->body('Commission type can only be set during initial review.')
                ->danger()
                ->send();

            return;
        }

        try {
            // Update commission type and amount
            $application->commission_type = $commissionType;

            if ($commissionType === 'scholarship') {
                $application->commission_amount = 0;
            } else {
                $application->commission_amount = $application->program->agent_commission ?? 0;
            }

            $application->save();

            // Transition to submitted status
            $statusService = app(\App\Services\ApplicationStatusService::class);
            $statusService->transitionTo(
                $application,
                'submitted',
                "Commission type set to: {$commissionType}"
            );

            Notification::make()
                ->title('✅ Commission Type Set')
                ->body('Commission type set successfully! Refreshing page...')
                ->success()
                ->duration(2000)
                ->send();

            // Redirect to refresh page and show new status immediately
            $this->redirect($this->getResource()::getUrl('view', ['record' => $application->id]));

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Failed to set commission type: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Handle status change event.
     */
    public function handleStatusChange($status = null, $requiresInput = false, $requiresConfirmation = false): void
    {
        if (! $status) {
            return;
        }

        // If requires input (like additional_documents_needed), show modal
        if ($requiresInput || $status === 'additional_documents_needed') {
            $this->dispatch('open-status-modal', status: $status);

            return;
        }

        // If requires confirmation (like approved), show confirmation modal
        if ($requiresConfirmation || $status === 'approved') {
            $this->dispatch('open-confirmation-modal', status: $status);

            return;
        }

        // Otherwise, change status directly
        $this->changeApplicationStatus($status);
    }

    /**
     * Change application status with optional note.
     */
    public function changeApplicationStatusWithNote(string $status, ?string $note = null): void
    {
        try {
            $application = $this->getRecord();
            $statusService = app(\App\Services\ApplicationStatusService::class);

            // For additional_documents_needed, save the note in additional_documents_request field
            if ($status === 'additional_documents_needed' && $note) {
                $application->additional_documents_request = $note;
                $application->save();
            }

            $statusService->transitionTo($application, $status, $note ?? "Status changed to {$status}");

            Notification::make()
                ->title('✅ Status Updated')
                ->body('Application status changed successfully! Refreshing page...')
                ->success()
                ->duration(2000)
                ->send();

            // Redirect to refresh page and show new status immediately
            $this->redirect($this->getResource()::getUrl('view', ['record' => $application->id]));
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Failed to update status: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Upload offer letter and change status to offer_received.
     */
    public function uploadOfferLetterAndChangeStatus(string $status, array $data): void
    {
        try {
            $application = $this->getRecord();
            $statusService = app(\App\Services\ApplicationStatusService::class);

            // Save offer letter path
            $offerLetterPath = $data['offer_letter'] ?? null;
            $offerDetails = $data['offer_details'] ?? null;

            if ($offerLetterPath) {
                // Create application document for the offer letter
                $document = \App\Models\ApplicationDocument::create([
                    'application_id' => $application->id,
                    'title' => 'Offer Letter',
                    'uploaded_by_user_id' => auth()->id(),
                    'original_filename' => basename($offerLetterPath),
                    'disk' => 'public',
                    'path' => $offerLetterPath,
                    'file_size' => \Storage::disk('public')->size($offerLetterPath),
                    'mime_type' => \Storage::disk('public')->mimeType($offerLetterPath),
                ]);

                // Change status with offer details in the note
                $note = "📄 Offer letter uploaded (Document ID: {$document->id})";
                if ($offerDetails) {
                    $note .= "\n\n📝 Offer Details:\n{$offerDetails}";
                }

                $statusService->transitionTo($application, $status, $note);

                Notification::make()
                    ->title('✅ Offer Letter Uploaded!')
                    ->body('Offer letter uploaded successfully! Refreshing page...')
                    ->success()
                    ->duration(2000)
                    ->send();

                // Redirect to refresh page and show new status immediately
                $this->redirect($this->getResource()::getUrl('view', ['record' => $application->id]));
            } else {
                throw new \Exception('Offer letter file is required.');
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('❌ Upload Failed')
                ->body('Failed to upload offer letter: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }
}
