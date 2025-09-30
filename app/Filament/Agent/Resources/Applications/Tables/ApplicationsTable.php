<?php

namespace App\Filament\Agent\Resources\Applications\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('program.name')
                    ->label('Program')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                TextColumn::make('program.university.name')
                    ->label('University')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
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
                    })
                    ->sortable(),
                TextColumn::make('intake_date')
                    ->label('Intake Date')
                    ->date()
                    ->sortable()
                    ->placeholder('Not specified'),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'submitted' => 'Submitted',
                        'under_review' => 'Under Review',
                        'additional_documents_required' => 'Additional Documents Required',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'enrolled' => 'Enrolled',
                        'cancelled' => 'Cancelled',
                    ]),
                SelectFilter::make('program_id')
                    ->label('Program')
                    ->relationship('program', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Details')
                    ->button(),
            ])
            ->toolbarActions([
                // Bulk delete removed - agents cannot delete applications
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No applications yet')
            ->emptyStateDescription('Start by creating your first application for a student.')
            ->emptyStateIcon('heroicon-o-document-text');
    }
}
