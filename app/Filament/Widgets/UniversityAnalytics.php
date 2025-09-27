<?php

namespace App\Filament\Widgets;

use App\Models\University;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class UniversityAnalytics extends TableWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public function getHeading(): string
    {
        return 'University Performance Analytics';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                University::query()
                    ->withCount([
                        'programs',
                        'programs as total_applications' => function (Builder $query) {
                            $query->join('applications', 'programs.id', '=', 'applications.program_id');
                        },
                        'programs as approved_applications' => function (Builder $query) {
                            $query->join('applications', 'programs.id', '=', 'applications.program_id')
                                ->where('applications.status', 'approved');
                        },
                        'programs as enrolled_applications' => function (Builder $query) {
                            $query->join('applications', 'programs.id', '=', 'applications.program_id')
                                ->where('applications.status', 'enrolled');
                        }
                    ])
                    ->selectRaw('universities.*, COALESCE(SUM(commissions.amount), 0) as total_commission')
                    ->leftJoin('programs', 'universities.id', '=', 'programs.university_id')
                    ->leftJoin('applications', 'programs.id', '=', 'applications.program_id')
                    ->leftJoin('commissions', 'applications.id', '=', 'commissions.application_id')
                    ->groupBy('universities.id', 'universities.name', 'universities.location', 'universities.is_active', 'universities.created_at', 'universities.updated_at')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('University')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('location')
                    ->label('Location')
                    ->searchable()
                    ->sortable()
                    ->limit(25),

                TextColumn::make('programs_count')
                    ->label('Programs')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('total_applications')
                    ->label('Total Apps')
                    ->alignCenter()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('approved_applications')
                    ->label('Approved')
                    ->alignCenter()
                    ->sortable()
                    ->badge()
                    ->color('success'),

                TextColumn::make('enrolled_applications')
                    ->label('Enrolled')
                    ->alignCenter()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('conversion_rate')
                    ->label('Approval Rate')
                    ->alignCenter()
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        if ($record->total_applications == 0) return '0%';
                        $rate = round(($record->approved_applications / $record->total_applications) * 100, 1);
                        return $rate . '%';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        (float) str_replace('%', '', $state) >= 70 => 'success',
                        (float) str_replace('%', '', $state) >= 50 => 'warning',
                        default => 'danger',
                    }),

                TextColumn::make('total_commission')
                    ->label('Total Commission')
                    ->alignRight()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => '$' . number_format($state, 2))
                    ->color('success'),
            ])
            ->defaultSort('total_applications', 'desc')
            ->paginated(false);
    }
}
