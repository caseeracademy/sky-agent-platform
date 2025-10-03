<?php

namespace App\Filament\Resources\ScholarshipAwards\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ScholarshipAwardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('commission_number')
                    ->label('Scholarship #')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                TextColumn::make('agent.name')
                    ->label('Agent')
                    ->searchable()
                    ->sortable()
                    ->color('primary'),
                TextColumn::make('university.name')
                    ->label('University')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->color('info'),
                TextColumn::make('degree.name')
                    ->label('Degree')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Certificate' => 'gray',
                        'Diploma' => 'info',
                        'Bachelor' => 'success',
                        'Master' => 'warning',
                        'PhD' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('application_year')
                    ->label('Year')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('qualifying_points_count')
                    ->label('Points Used')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->description('Points used to earn this scholarship'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'earned' => 'success',
                        'used' => 'info',
                        'expired' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'earned' => 'Available',
                        'used' => 'Used',
                        'expired' => 'Expired',
                        default => ucfirst($state),
                    }),
                TextColumn::make('earned_at')
                    ->label('Earned At')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->since(),
                TextColumn::make('used_at')
                    ->label('Used At')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->since()
                    ->placeholder('Not used yet')
                    ->toggleable(),
                TextColumn::make('notes')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'earned' => 'Available',
                        'used' => 'Used',
                        'expired' => 'Expired',
                    ]),
                SelectFilter::make('application_year')
                    ->label('Application Year')
                    ->options(function () {
                        $currentYear = \App\Models\ScholarshipPoint::getCurrentApplicationYear();

                        return [
                            $currentYear => $currentYear,
                            $currentYear - 1 => $currentYear - 1,
                            $currentYear - 2 => $currentYear - 2,
                        ];
                    })
                    ->default(\App\Models\ScholarshipPoint::getCurrentApplicationYear()),
                SelectFilter::make('university_id')
                    ->label('University')
                    ->relationship('university', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('degree_id')
                    ->label('Degree Type')
                    ->relationship('degree', 'name')
                    ->preload(),
                SelectFilter::make('agent_id')
                    ->label('Agent')
                    ->relationship('agent', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
