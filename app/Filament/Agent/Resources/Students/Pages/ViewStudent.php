<?php

namespace App\Filament\Agent\Resources\Students\Pages;

use App\Filament\Agent\Resources\Students\StudentResource;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class ViewStudent extends ViewRecord
{
    protected static string $resource = StudentResource::class;

    protected static ?string $title = 'Student Details';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                // Single fullwidth tabs card
                Tabs::make('Student Details')
                    ->columnSpanFull()
                    ->contained(false)
                    ->tabs([
                        Tab::make('Student Overview')
                            ->schema([
                                Section::make('Basic Information')
                                    ->schema([
                                        Placeholder::make('name')
                                            ->label('Full Name')
                                            ->content(fn ($record) => $record->name),
                                        Placeholder::make('email')
                                            ->label('Email Address')
                                            ->content(fn ($record) => $record->email),
                                        Placeholder::make('phone')
                                            ->label('Phone Number')
                                            ->content(fn ($record) => $record->phone ?: 'Not provided'),
                                    ])
                                    ->columns(3),

                                Section::make('Personal Details')
                                    ->schema([
                                        Placeholder::make('nationality')
                                            ->label('Nationality')
                                            ->content(fn ($record) => $record->nationality ?: 'Not provided'),
                                        Placeholder::make('date_of_birth')
                                            ->label('Date of Birth')
                                            ->content(fn ($record) => $record->date_of_birth ? $record->date_of_birth->format('M j, Y') : 'Not provided'),
                                        Placeholder::make('gender')
                                            ->label('Gender')
                                            ->content(fn ($record) => $record->gender ? ucfirst($record->gender) : 'Not provided'),
                                        Placeholder::make('country_of_residence')
                                            ->label('Country of Residence')
                                            ->content(fn ($record) => $record->country_of_residence ?: 'Not provided'),
                                    ])
                                    ->columns(2),

                                Section::make('System Information')
                                    ->schema([
                                        Placeholder::make('created_at')
                                            ->label('Added to System')
                                            ->content(fn ($record) => $record->created_at->format('M j, Y g:i A')),
                                        Placeholder::make('updated_at')
                                            ->label('Last Updated')
                                            ->content(fn ($record) => $record->updated_at->format('M j, Y g:i A')),
                                        Placeholder::make('student_status')
                                            ->label('Student Status')
                                            ->content(function ($record) {
                                                $color = 'success';
                                                $label = 'Active';

                                                return "<span class=\"fi-badge fi-color-{$color} fi-size-md inline-flex items-center justify-center gap-x-1 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset\">{$label}</span>";
                                            })
                                            ->html(),
                                    ])
                                    ->columns(3)
                                    ->collapsible(),
                            ]),

                        Tab::make('Applications')
                            ->schema([
                                Section::make('Application History')
                                    ->schema([
                                        Placeholder::make('applications_list')
                                            ->label('Applications')
                                            ->content(function ($record) {
                                                return new \Illuminate\Support\HtmlString(
                                                    view('filament.components.student-applications-list', [
                                                        'applications' => $record->applications()
                                                            ->with(['program.university', 'agent'])
                                                            ->orderBy('created_at', 'desc')
                                                            ->get(),
                                                    ])->render()
                                                );
                                            }),
                                    ]),

                                Section::make('Application Summary')
                                    ->schema([
                                        Placeholder::make('total_applications')
                                            ->label('Total Applications')
                                            ->content(fn ($record) => $record->applications()->count().' application'.($record->applications()->count() !== 1 ? 's' : '')),
                                        Placeholder::make('approved_applications')
                                            ->label('Approved Applications')
                                            ->content(function ($record) {
                                                $count = $record->applications()->where('status', 'approved')->count();

                                                return $count.' approved';
                                            }),
                                        Placeholder::make('pending_applications')
                                            ->label('Pending Applications')
                                            ->content(function ($record) {
                                                $count = $record->applications()->whereIn('status', ['pending', 'submitted', 'under_review'])->count();

                                                return $count.' pending';
                                            }),
                                        Placeholder::make('total_commission_earned')
                                            ->label('Total Commission Earned')
                                            ->content(function ($record) {
                                                $total = $record->applications()->where('status', 'approved')->sum('commission_amount');

                                                return '$'.number_format($total, 2);
                                            }),
                                    ])
                                    ->columns(2)
                                    ->collapsible(),
                            ]),

                        Tab::make('Documents')
                            ->schema([
                                Section::make('Uploaded Documents')
                                    ->schema([
                                        Placeholder::make('documents_list')
                                            ->label('Documents')
                                            ->content(function ($record) {
                                                return new \Illuminate\Support\HtmlString(
                                                    view('filament.components.student-documents-list', [
                                                        'documents' => $record->documents()->with('uploadedBy')->orderBy('created_at', 'desc')->get(),
                                                        'showReplace' => true, // Show replace button for agents
                                                        'showUploadButton' => true, // Show upload button for agents
                                                        'studentId' => $record->id,
                                                    ])->render()
                                                );
                                            }),
                                    ]),

                                Section::make('Document Summary')
                                    ->schema([
                                        Placeholder::make('total_documents')
                                            ->label('Total Documents')
                                            ->content(fn ($record) => $record->documents()->count().' document'.($record->documents()->count() !== 1 ? 's' : '')),
                                        Placeholder::make('document_types')
                                            ->label('Document Types')
                                            ->content(function ($record) {
                                                $types = $record->documents()->distinct('type')->pluck('type');

                                                return $types->isEmpty() ? 'No documents' : $types->count().' different types';
                                            }),
                                    ])
                                    ->columns(2)
                                    ->collapsible(),
                            ]),

                        Tab::make('Agent Information')
                            ->schema([
                                Section::make('Your Agent Details')
                                    ->schema([
                                        Placeholder::make('agent_name')
                                            ->label('Agent Name')
                                            ->content(fn ($record) => $record->agent->name ?? 'Not assigned'),
                                        Placeholder::make('agent_email')
                                            ->label('Agent Email')
                                            ->content(fn ($record) => $record->agent->email ?? 'Not assigned'),
                                        Placeholder::make('agent_role')
                                            ->label('Agent Role')
                                            ->content(fn ($record) => $record->agent ? ucfirst(str_replace('_', ' ', $record->agent->role)) : 'Not assigned'),
                                        Placeholder::make('agent_status')
                                            ->label('Account Status')
                                            ->content(function ($record) {
                                                if (! $record->agent) {
                                                    return '<span class="fi-badge fi-color-gray fi-size-md inline-flex items-center justify-center gap-x-1 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset">Not Assigned</span>';
                                                }
                                                $color = $record->agent->is_active ? 'success' : 'danger';
                                                $label = $record->agent->is_active ? 'Active' : 'Inactive';

                                                return "<span class=\"fi-badge fi-color-{$color} fi-size-md inline-flex items-center justify-center gap-x-1 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset\">{$label}</span>";
                                            })
                                            ->html(),
                                    ])
                                    ->columns(2),

                                Section::make('Agent Performance with This Student')
                                    ->schema([
                                        Placeholder::make('student_applications')
                                            ->label('Applications for This Student')
                                            ->content(fn ($record) => $record->applications()->count().' application'.($record->applications()->count() !== 1 ? 's' : '')),
                                        Placeholder::make('student_success_rate')
                                            ->label('Success Rate for This Student')
                                            ->content(function ($record) {
                                                $total = $record->applications()->count();
                                                $approved = $record->applications()->where('status', 'approved')->count();
                                                if ($total === 0) {
                                                    return '0% (No applications)';
                                                }
                                                $rate = round(($approved / $total) * 100);

                                                return $rate.'% ('.$approved.'/'.$total.' approved)';
                                            }),
                                        Placeholder::make('total_commission_this_student')
                                            ->label('Total Commission from This Student')
                                            ->content(function ($record) {
                                                $total = $record->applications()->where('status', 'approved')->sum('commission_amount');

                                                return '$'.number_format($total, 2);
                                            }),
                                        Placeholder::make('relationship_duration')
                                            ->label('Student Relationship Duration')
                                            ->content(function ($record) {
                                                $duration = $record->created_at->diffForHumans(null, true);

                                                return $duration.' (since '.$record->created_at->format('M j, Y').')';
                                            }),
                                    ])
                                    ->columns(2),
                            ]),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
