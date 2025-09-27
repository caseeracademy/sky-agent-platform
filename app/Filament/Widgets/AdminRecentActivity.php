<?php

namespace App\Filament\Widgets;

use App\Models\ApplicationLog;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class AdminRecentActivity extends TableWidget
{
    protected static ?string $heading = 'Recent Activity';
    
    protected int | string | array $columnSpan = 'full';
    
    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => 
                ApplicationLog::query()
                    ->with(['user', 'application.student', 'application.program'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('created_at')
                    ->label('Time')
                    ->dateTime('M j, g:i A')
                    ->sortable(),
                    
                TextColumn::make('user.name')
                    ->label('User')
                    ->default('System')
                    ->badge()
                    ->color(fn ($record) => match ($record->user?->role) {
                        'super_admin' => 'danger',
                        'admin_staff' => 'warning',
                        'agent_owner' => 'success',
                        'agent_staff' => 'info',
                        default => 'gray',
                    }),
                    
                TextColumn::make('application.application_number')
                    ->label('Application')
                    ->url(fn ($record) => route('filament.admin.resources.applications.view', $record->application))
                    ->color('primary')
                    ->weight('semibold'),
                    
                TextColumn::make('application.student.name')
                    ->label('Student')
                    ->limit(20),
                    
                TextColumn::make('note')
                    ->label('Activity')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->note),
                    
                BadgeColumn::make('status_change')
                    ->label('Status Change')
                    ->colors([
                        'gray' => ['pending', 'draft'],
                        'info' => 'submitted',
                        'warning' => 'under_review',
                        'danger' => ['additional_documents_required', 'rejected'],
                        'success' => ['approved', 'enrolled'],
                    ])
                    ->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false);
    }
}
