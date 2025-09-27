<?php

namespace App\Filament\Resources\Applications\Pages;

use App\Filament\Resources\Applications\ApplicationResource;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ViewApplication extends ViewRecord
{
    protected static string $resource = ApplicationResource::class;
    
    protected static ?string $title = 'Application Hub';

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
                                                    'cancelled' => 'gray'
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
                                            ->content(fn ($record) => '$' . number_format($record->program->tuition_fee ?? 0, 2)),
                                        Placeholder::make('commission_amount')
                                            ->label('Commission Amount')
                                            ->content(fn ($record) => '<span class="text-lg font-bold text-green-600">$' . number_format($record->commission_amount ?? 0, 2) . '</span>')
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

                        Tab::make('Document Review')
                            ->schema([
                                Section::make('Uploaded Documents')
                                    ->schema([
                                        Placeholder::make('documents_list')
                                            ->label('Documents')
                                            ->content(function ($record) {
                                                $documents = $record->applicationDocuments;
                                                
                                                if ($documents->isEmpty()) {
                                                    return '<div class="text-gray-500 italic">No documents uploaded yet.</div>';
                                                }
                                                
                                                $html = '<div class="space-y-3">';
                                                foreach ($documents as $document) {
                                                    $uploadedBy = $document->uploadedByUser->name ?? 'Unknown';
                                                    $uploadDate = $document->created_at->format('M j, Y g:i A');
                                                    $fileSize = $document->formatted_file_size;
                                                    $downloadUrl = $document->download_url;
                                                    
                                                    $html .= '<div class="border border-gray-200 rounded-lg p-4 bg-gray-50">';
                                                    $html .= '<div class="flex items-center justify-between">';
                                                    $html .= '<div class="flex-1">';
                                                    $html .= '<h4 class="font-medium text-gray-900">' . e($document->original_filename) . '</h4>';
                                                    $html .= '<p class="text-sm text-gray-500">Uploaded by ' . e($uploadedBy) . ' on ' . $uploadDate . '</p>';
                                                    $html .= '<p class="text-xs text-gray-400">File size: ' . $fileSize . ' | Type: ' . e($document->mime_type ?? 'Unknown') . '</p>';
                                                    $html .= '</div>';
                                                    $html .= '<div class="ml-4">';
                                                    $html .= '<a href="' . $downloadUrl . '" target="_blank" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">';
                                                    $html .= '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
                                                    $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>';
                                                    $html .= '</svg>';
                                                    $html .= 'Download';
                                                    $html .= '</a>';
                                                    $html .= '</div>';
                                                    $html .= '</div>';
                                                    $html .= '</div>';
                                                }
                                                $html .= '</div>';
                                                
                                                return $html;
                                            })
                                            ->html(),
                                    ]),
                            ]),

                        Tab::make('Commission')
                            ->schema([
                                Section::make('Commission Details')
                                    ->schema([
                                        Placeholder::make('commission_amount')
                                            ->label('Commission Amount')
                                            ->content(fn ($record) => $record->commission_amount ? '$' . number_format($record->commission_amount, 2) : 'Not calculated'),
                                        Placeholder::make('commission_status')
                                            ->label('Commission Status')
                                            ->content(function ($record) {
                                                if (!$record->commission_amount) {
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
                                                    return number_format($rate, 2) . '%';
                                                }
                                                return 'N/A';
                                            }),
                                        Placeholder::make('tuition_fee')
                                            ->label('Total Tuition Fee')
                                            ->content(fn ($record) => $record->program->tuition_fee ? '$' . number_format($record->program->tuition_fee, 2) : 'N/A'),
                                    ])
                                    ->columns(2),
                                    
                                Section::make('Payment Information')
                                    ->schema([
                                        Placeholder::make('payment_status')
                                            ->label('Payment Status')
                                            ->content(function ($record) {
                                                if ($record->status === 'approved' && $record->commission_amount && !$record->commission_paid) {
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
                                                return $count . ' application' . ($count !== 1 ? 's' : '');
                                            }),
                                        Placeholder::make('approved_applications')
                                            ->label('Approved Applications')
                                            ->content(function ($record) {
                                                $count = \App\Models\Application::where('agent_id', $record->agent_id)
                                                    ->where('status', 'approved')
                                                    ->count();
                                                return $count . ' approved';
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
                                                return !empty($contact) ? implode('<br>', $contact) : 'No additional contact information';
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
                                                                    <div class="h-3.5 w-3.5 rounded-full ' . $dotClasses . '"></div>
                                                                </div>
                                                                <div>
                                                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ' . $badgeClasses . '">' . e($statusLabel) . '</span>
                                                                    <p class="mt-1 text-sm font-medium text-gray-900">' . e($log->note) . '</p>
                                                                </div>
                                                            </div>
                                                            <div class="text-right text-sm text-gray-500">
                                                                <p>' . e($timestamp) . '</p>
                                                                <p>' . e($timeAgo) . '</p>
                                                            </div>
                                                        </div>

                                                        <div class="grid gap-3 rounded-xl bg-gray-50 p-4 text-sm text-gray-600">
                                                            <div class="flex items-start">
                                                                <span class="mt-1 flex h-6 w-6 items-center justify-center rounded-full ' . $cardAccent . '">
                                                                    <svg class="h-3 w-3 text-current" viewBox="0 0 20 20" fill="currentColor">
                                                                        <path fill-rule="evenodd" d="M10 3a1 1 0 01.894.553l6 12A1 1 0 0116 17H4a1 1 0 01-.894-1.447l6-12A1 1 0 0110 3zm0 3a1 1 0 100 2 1 1 0 000-2zm-1 5a1 1 0 112 0v2a1 1 0 11-2 0v-2z" clip-rule="evenodd"></path>
                                                                    </svg>
                                                                </span>
                                                                <div class="ml-3">
                                                                    <p class="font-semibold text-gray-900">Log Notes</p>
                                                                    <p class="text-gray-600">' . e(Str::of($log->note)->ucfirst()) . '</p>
                                                                </div>
                                                            </div>

                                                            <div class="flex items-start">
                                                                <span class="mt-1 flex h-6 w-6 items-center justify-center rounded-full ' . $cardAccent . '">
                                                                    <svg class="h-3 w-3 text-current" viewBox="0 0 20 20" fill="currentColor">
                                                                        <path fill-rule="evenodd" d="M6 3a1 1 0 00-.894.553L2.382 9H17.618l-2.724-5.447A1 1 0 0014 3H6zm2 10a2 2 0 104 0V9H8v4z" clip-rule="evenodd"></path>
                                                                    </svg>
                                                                </span>
                                                                <div class="ml-3">
                                                                    <p class="font-semibold text-gray-900">Status Change</p>
                                                                    <p class="text-gray-600">' . ($log->status_change ? e(Str::of($log->status_change)->headline()) : 'No status change recorded.') . '</p>
                                                                </div>
                                                            </div>

                                                            <div class="flex items-start">
                                                                <span class="mt-1 flex h-6 w-6 items-center justify-center rounded-full ' . $cardAccent . '">
                                                                    <svg class="h-3 w-3 text-current" viewBox="0 0 20 20" fill="currentColor">
                                                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                                                    </svg>
                                                                </span>
                                                                <div class="ml-3">
                                                                    <p class="font-semibold text-gray-900">Performed By</p>
                                                                    <p class="text-gray-600">' . e($userName) . ' <span class="text-xs text-gray-400">(' . e(Str::headline($userRole)) . ')</span></p>
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
                    ])
            ]);
    }

    protected function getHeaderActions(): array
    {
        $actions = [];

        // Only show edit action if application is not approved (locking rule)
        if ($this->getRecord()->status !== 'approved') {
            $actions[] = EditAction::make();
        }

        return $actions;
    }
}
