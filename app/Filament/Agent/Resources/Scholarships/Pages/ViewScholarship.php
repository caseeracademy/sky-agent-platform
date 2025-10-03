<?php

namespace App\Filament\Agent\Resources\Scholarships\Pages;

use App\Filament\Agent\Resources\Scholarships\ScholarshipResource;
use App\Models\Application;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class ViewScholarship extends ViewRecord
{
    protected static string $resource = ScholarshipResource::class;

    protected static ?string $title = 'Scholarship Details';

    public function mount($record): void
    {
        // If record is a string ID, we need to find the actual ScholarshipDisplay
        if (is_string($record)) {
            // Get all scholarship displays for the current agent
            $scholarshipDisplays = \App\Models\ScholarshipDisplay::getAllForAgent(auth()->id());

            // Find the specific record by ID
            $foundRecord = $scholarshipDisplays->firstWhere('id', $record);

            if (! $foundRecord) {
                abort(404, 'Scholarship not found');
            }

            $this->record = $foundRecord;
        } else {
            $this->record = $record;
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                // Single fullwidth tabs card
                Tabs::make('Scholarship Details')
                    ->columnSpanFull()
                    ->contained(false)
                    ->tabs([
                        Tab::make('Scholarship Overview')
                            ->schema([
                                Section::make('Profile')
                                    ->schema([
                                        Placeholder::make('profile_display')
                                            ->label('')
                                            ->content(fn ($record) => new \Illuminate\Support\HtmlString(
                                                view('filament.agent.components.scholarship-profile-header', [
                                                    'scholarship' => $record,
                                                ])->render()
                                            )),
                                    ]),

                                Section::make('Basic Information')
                                    ->schema([
                                        Placeholder::make('scholarship_number')
                                            ->label('Scholarship Number')
                                            ->content(fn ($record) => $record->commission_number ?: ($record->type === 'progress' ? 'In Progress' : 'N/A')),
                                        Placeholder::make('university_name')
                                            ->label('University')
                                            ->content(function ($record) {
                                                $university = \App\Models\University::find($record->university_id);

                                                return $university ? $university->name : 'Unknown University';
                                            }),
                                        Placeholder::make('degree_level')
                                            ->label('Degree Level')
                                            ->content(function ($record) {
                                                $degree = \App\Models\Degree::find($record->degree_id);

                                                return $degree ? $degree->name : 'Unknown';
                                            }),
                                    ])
                                    ->columns(3),

                                Section::make('Status Details')
                                    ->schema([
                                        Placeholder::make('status')
                                            ->label('Status')
                                            ->content(function ($record) {
                                                $color = $record->type === 'progress' ? 'warning' : 'success';
                                                $label = $record->type === 'progress' ? 'In Progress' : 'Earned';

                                                return "<span class=\"fi-badge fi-color-{$color} fi-size-md inline-flex items-center justify-center gap-x-1 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset\">{$label}</span>";
                                            })
                                            ->html(),
                                        Placeholder::make('progress')
                                            ->label('Progress')
                                            ->content(fn ($record) => $record->type === 'progress' ? ($record->progress_text ?: 'N/A') : 'Completed')
                                            ->visible(fn ($record) => $record->type === 'progress'),
                                        Placeholder::make('earned_at')
                                            ->label('Earned Date')
                                            ->content(fn ($record) => $record->earned_at ? $record->earned_at->format('M j, Y g:i A') : 'Not earned yet')
                                            ->visible(fn ($record) => $record->type === 'completed'),
                                        Placeholder::make('used_at')
                                            ->label('Used Date')
                                            ->content(fn ($record) => $record->used_at ? $record->used_at->format('M j, Y g:i A') : 'Not used yet')
                                            ->visible(fn ($record) => $record->type === 'completed'),
                                    ])
                                    ->columns(2),

                                Section::make('System Information')
                                    ->schema([
                                        Placeholder::make('scholarship_type')
                                            ->label('Scholarship Type')
                                            ->content(fn ($record) => ucfirst($record->type)),
                                        Placeholder::make('threshold')
                                            ->label('Students Required')
                                            ->content(fn ($record) => ($record->threshold ?? 'N/A').' students'),
                                        Placeholder::make('current_points')
                                            ->label('Current Points')
                                            ->content(fn ($record) => $record->current_points ?? 'N/A')
                                            ->visible(fn ($record) => $record->type === 'progress'),
                                    ])
                                    ->columns(3)
                                    ->collapsible(),
                            ]),

                        Tab::make('Contributing Applications')
                            ->schema([
                                Section::make('Applications List')
                                    ->schema([
                                        Placeholder::make('applications_list')
                                            ->label('')
                                            ->content(fn ($record) => new \Illuminate\Support\HtmlString(
                                                view('filament.agent.components.scholarship-applications-list', [
                                                    'applications' => $this->getApplications($record),
                                                    'scholarship' => $record,
                                                ])->render()
                                            )),
                                    ]),

                                Section::make('Application Summary')
                                    ->schema([
                                        Placeholder::make('total_applications')
                                            ->label('Contributing Applications')
                                            ->content(function ($record) {
                                                $count = $this->getApplications($record)->count();

                                                return $count.' application'.($count !== 1 ? 's' : '');
                                            }),
                                        Placeholder::make('progress_percentage')
                                            ->label('Progress Percentage')
                                            ->content(fn ($record) => ($record->progress_percentage ?? 0).'%')
                                            ->visible(fn ($record) => $record->type === 'progress'),
                                    ])
                                    ->columns(2)
                                    ->collapsible(),
                            ])
                            ->visible(fn ($record) => $record->type === 'progress'),

                        Tab::make('How It Works')
                            ->schema([
                                Section::make('Scholarship Information')
                                    ->schema([
                                        Placeholder::make('how_it_works')
                                            ->label('')
                                            ->content(fn ($record) => new \Illuminate\Support\HtmlString(
                                                view('filament.agent.components.scholarship-how-it-works', [
                                                    'scholarship' => $record,
                                                ])->render()
                                            )),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    protected function getApplications($record)
    {
        if ($record && $record->type === 'progress') {
            $universityId = $record->university_id ?? ($record->university->id ?? null);
            $degreeId = $record->degree_id ?? ($record->degree->id ?? null);

            if ($universityId && $degreeId) {
                return Application::where('agent_id', auth()->id())
                    ->where('status', 'approved')
                    ->where('commission_type', 'scholarship')
                    ->whereHas('program', function ($query) use ($universityId, $degreeId) {
                        $query->where('university_id', $universityId)
                            ->where('degree_id', $degreeId);
                    })
                    ->with(['student', 'program'])
                    ->get();
            }
        }

        return collect();
    }
}
