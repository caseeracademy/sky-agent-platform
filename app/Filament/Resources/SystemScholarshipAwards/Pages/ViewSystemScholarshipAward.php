<?php

namespace App\Filament\Resources\SystemScholarshipAwards\Pages;

use App\Filament\Resources\SystemScholarshipAwards\SystemScholarshipAwardResource;
use App\Services\SystemScholarshipService;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class ViewSystemScholarshipAward extends ViewRecord
{
    protected static string $resource = SystemScholarshipAwardResource::class;

    protected static ?string $title = 'System Scholarship Details';

    public function mount($record): void
    {
        // If record is a string ID, we need to find the actual SystemScholarshipDisplay
        if (is_string($record)) {
            $systemService = app(SystemScholarshipService::class);
            $foundRecord = $systemService->getSystemScholarshipById($record);

            if (! $foundRecord) {
                abort(404, 'System scholarship not found');
            }

            // Convert array to SystemScholarshipDisplay model
            $this->record = \App\Models\SystemScholarshipDisplay::fromSystemScholarshipData($foundRecord);
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
                Tabs::make('System Scholarship Details')
                    ->columnSpanFull()
                    ->contained(false)
                    ->tabs([
                        Tab::make('System Overview')
                            ->schema([
                                Section::make('System Progress')
                                    ->schema([
                                        Placeholder::make('system_header')
                                            ->label('')
                                            ->content(fn ($record) => new \Illuminate\Support\HtmlString(
                                                view('filament.admin.components.system-scholarship-header', [
                                                    'scholarship' => $record,
                                                ])->render()
                                            )),
                                    ]),

                                Section::make('Business Model')
                                    ->schema([
                                        Placeholder::make('university_contract')
                                            ->label('University Contract')
                                            ->content(function ($record) {
                                                $threshold = $record->university_threshold ?? 4;

                                                return "University gives system 1 scholarship per {$threshold} students";
                                            }),
                                        Placeholder::make('agent_contract')
                                            ->label('Agent Contract')
                                            ->content(function ($record) {
                                                $threshold = $record->agent_threshold ?? 5;

                                                return "System gives agents 1 scholarship per {$threshold} students";
                                            }),
                                        Placeholder::make('system_profit')
                                            ->label('System Profit Model')
                                            ->content(function ($record) {
                                                $needed = $record->students_per_system_scholarship ?? 20;

                                                return "System earns 1 scholarship every {$needed} total students";
                                            }),
                                    ])
                                    ->columns(3),

                                Section::make('Current Status')
                                    ->schema([
                                        Placeholder::make('total_students')
                                            ->label('Total Students Approved')
                                            ->content(fn ($record) => ($record->total_students ?? 0).' students'),
                                        Placeholder::make('system_scholarships_earned')
                                            ->label('System Scholarships Earned')
                                            ->content(fn ($record) => ($record->system_scholarships_earned ?? 0).' scholarships'),
                                        Placeholder::make('progress_percentage')
                                            ->label('Current Cycle Progress')
                                            ->content(fn ($record) => ($record->progress_percentage ?? 0).'%'),
                                        Placeholder::make('students_needed')
                                            ->label('Students Needed for Next')
                                            ->content(fn ($record) => ($record->students_needed_for_next ?? 0).' more students'),
                                    ])
                                    ->columns(4),
                            ]),

                        Tab::make('Contributing Agents')
                            ->schema([
                                Section::make('Agent Contributions')
                                    ->schema([
                                        Placeholder::make('agents_list')
                                            ->label('')
                                            ->content(fn ($record) => new \Illuminate\Support\HtmlString(
                                                view('filament.admin.components.system-contributing-agents', [
                                                    'agents' => $record->contributing_agents ?? [],
                                                    'scholarship' => $record,
                                                ])->render()
                                            )),
                                    ]),

                                Section::make('Agent Summary')
                                    ->schema([
                                        Placeholder::make('total_agents')
                                            ->label('Contributing Agents')
                                            ->content(function ($record) {
                                                $count = count($record->contributing_agents ?? []);

                                                return $count.' agent'.($count !== 1 ? 's' : '');
                                            }),
                                        Placeholder::make('completed_agents')
                                            ->label('Completed Agents')
                                            ->content(function ($record) {
                                                $agents = $record->contributing_agents ?? [];
                                                $completed = collect($agents)->where('has_completed', true)->count();

                                                return $completed.' completed';
                                            }),
                                        Placeholder::make('in_progress_agents')
                                            ->label('In Progress Agents')
                                            ->content(function ($record) {
                                                $agents = $record->contributing_agents ?? [];
                                                $inProgress = collect($agents)->where('has_completed', false)->count();

                                                return $inProgress.' working';
                                            }),
                                    ])
                                    ->columns(3)
                                    ->collapsible(),
                            ]),

                        Tab::make('System Logic')
                            ->schema([
                                Section::make('Calculation Breakdown')
                                    ->schema([
                                        Placeholder::make('calculation_explanation')
                                            ->label('')
                                            ->content(fn ($record) => new \Illuminate\Support\HtmlString(
                                                view('filament.admin.components.system-calculation-explanation', [
                                                    'scholarship' => $record,
                                                ])->render()
                                            )),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
