<?php

namespace App\Filament\Agent\Widgets;

use App\Models\Application;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class AgentRecentApplications extends TableWidget
{
    protected static ?string $heading = 'Recent Applications';
    
    protected int | string | array $columnSpan = 'full';
    
    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => 
                Application::query()
                    ->where('agent_id', auth()->id())
                    ->with(['student', 'program.university'])
                    ->latest()
                    ->limit(8)
            )
            ->columns([
                TextColumn::make('application_number')
                    ->label('App #')
                    ->searchable()
                    ->weight('semibold'),
                    
                TextColumn::make('student.name')
                    ->label('Student')
                    ->searchable()
                    ->limit(25),
                    
                TextColumn::make('program.name')
                    ->label('Program')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->program->name),
                    
                TextColumn::make('program.university.name')
                    ->label('University')
                    ->limit(25)
                    ->tooltip(fn ($record) => $record->program->university->name),
                    
                BadgeColumn::make('status')
                    ->colors([
                        'gray' => ['pending', 'cancelled'],
                        'info' => 'submitted',
                        'warning' => 'under_review',
                        'danger' => ['additional_documents_required', 'rejected'],
                        'success' => ['approved', 'enrolled'],
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'submitted' => 'Submitted',
                        'under_review' => 'Under Review',
                        'additional_documents_required' => 'Additional Docs Required',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'enrolled' => 'Enrolled',
                        'cancelled' => 'Cancelled',
                        default => ucfirst($state),
                    }),
                    
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Details')
                    ->url(fn ($record) => route('filament.agent.resources.applications.view', $record)),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false);
    }
}
