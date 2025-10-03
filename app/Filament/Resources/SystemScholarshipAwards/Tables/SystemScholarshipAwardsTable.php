<?php

namespace App\Filament\Resources\SystemScholarshipAwards\Tables;

use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SystemScholarshipAwardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ViewColumn::make('system_scholarship_card')
                    ->label('')
                    ->view('filament.admin.columns.system-scholarship-card')
                    ->sortable(false),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'earned' => 'Scholarships Earned',
                        'in_progress' => 'In Progress',
                    ]),

                SelectFilter::make('university_id')
                    ->label('University')
                    ->relationship('university', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('degree_id')
                    ->label('Degree Type')
                    ->relationship('degree', 'name')
                    ->preload(),
            ])
            ->defaultSort('status', 'asc') // In progress first, then earned
            ->emptyStateHeading('No System Scholarship Progress Yet')
            ->emptyStateDescription('System scholarships are earned automatically as agents complete their quotas across all universities!')
            ->emptyStateIcon('heroicon-o-building-library')
            ->contentGrid([
                'md' => 1,
                'xl' => 1,
            ]);
    }
}
