<?php

namespace App\Filament\Agent\Resources\Scholarships\Tables;

use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ScholarshipsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ViewColumn::make('scholarship_card')
                    ->label('')
                    ->view('filament.agent.columns.scholarship-card')
                    ->sortable(false),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'earned' => 'Ready to Use',
                        'used' => 'Already Used',
                        'expired' => 'Expired',
                        'in_progress' => 'In Progress',
                    ]),

                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'completed' => 'Completed Scholarships',
                        'progress' => 'Progress Scholarships',
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
            ->defaultSort('type', 'asc') // Progress first, then completed
            ->emptyStateHeading('No Scholarship Progress Yet')
            ->emptyStateDescription('Start applying students to begin earning scholarship points and unlock free applications!')
            ->emptyStateIcon('heroicon-o-academic-cap')
            ->contentGrid([
                'md' => 1,
                'xl' => 1,
            ]);
    }
}
