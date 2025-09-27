<?php

namespace App\Filament\Agent\Widgets;

use App\Models\Payout;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class PayoutHistory extends TableWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Payout::query()
                    ->where('agent_id', auth()->id())
                    ->latest()
            )
            ->columns([
                TextColumn::make('amount')
                    ->money('USD')
                    ->sortable()
                    ->size('lg')
                    ->weight('bold'),
                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'rejected',
                    ])
                    ->sortable()
                    ->size('lg'),
                TextColumn::make('created_at')
                    ->label('Requested')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->description(fn ($record) => $record?->created_at->format('M j, Y g:i A')),
                TextColumn::make('updated_at')
                    ->label('Processed')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->description(fn ($record) => $record?->updated_at->format('M j, Y g:i A'))
                    ->visible(fn ($record) => $record && $record->status !== 'pending'),
            ])
            ->filters([
                SelectFilter::make('date_range')
                    ->label('Date Range')
                    ->options([
                        '7' => 'Last 7 days',
                        '28' => 'Last 28 days',
                        '90' => 'Last 3 months',
                        '365' => 'Last year',
                        'all' => 'All time',
                    ])
                    ->default('28')
                    ->query(function (Builder $query, array $data): Builder {
                        $days = $data['value'] ?? '28';
                        
                        if ($days === 'all') {
                            return $query;
                        }
                        
                        return $query->where('created_at', '>=', now()->subDays((int) $days));
                    }),
            ])
            ->actions([
                Action::make('downloadReceipt')
                    ->label('Download Receipt')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->url(fn (Payout $record): string => route('agent.payout.receipt', $record->id))
                    ->openUrlInNewTab()
                    ->visible(fn (Payout $record): bool => in_array($record->status, ['paid', 'rejected'])),
            ])
            ->actionsColumnLabel('Receipt')
            ->heading('Payout Request History')
            ->emptyStateHeading('No payout history yet')
            ->emptyStateDescription('Request your first payout to see it tracked here.')
            ->emptyStateIcon('heroicon-o-banknotes')
            ->striped()
            ->paginated([10, 25, 50]);
    }
}
