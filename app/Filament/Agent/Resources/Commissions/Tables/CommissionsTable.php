<?php

namespace App\Filament\Agent\Resources\Commissions\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CommissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('application.application_number')
                    ->label('Application #')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('application.student.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('application.program.name')
                    ->label('Program')
                    ->limit(30)
                    ->sortable(),
                TextColumn::make('amount')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Earned At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No commissions yet')
            ->emptyStateDescription('Commissions will appear here once applications are approved.');
    }
}
