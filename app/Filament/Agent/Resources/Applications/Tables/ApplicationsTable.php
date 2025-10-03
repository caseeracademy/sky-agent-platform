<?php

namespace App\Filament\Agent\Resources\Applications\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('student.profile_image')
                    ->label('Avatar')
                    ->circular()
                    ->disk('public')
                    ->defaultImageUrl(url('/images/default-avatar.svg'))
                    ->size(40),
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
                        'warning' => ['needs_review', 'waiting_to_apply', 'payment_pending'],
                        'info' => ['submitted', 'applied', 'payment_approval', 'ready_for_approval'],
                        'danger' => ['additional_documents_needed', 'rejected'],
                        'success' => ['approved', 'enrolled', 'offer_received'],
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'needs_review' => 'Needs Review',
                        'pending' => 'Pending',
                        'submitted' => 'Submitted',
                        'additional_documents_needed' => 'Additional Docs Needed',
                        'waiting_to_apply' => 'Waiting to Apply',
                        'applied' => 'Applied',
                        'offer_received' => 'Offer Received',
                        'payment_pending' => 'Payment Pending',
                        'payment_approval' => 'Payment Approval',
                        'ready_for_approval' => 'Ready for Approval',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'enrolled' => 'Enrolled',
                        'cancelled' => 'Cancelled',
                        default => ucfirst(str_replace('_', ' ', $state)),
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
                        'needs_review' => 'Needs Review',
                        'pending' => 'Pending',
                        'submitted' => 'Submitted',
                        'additional_documents_needed' => 'Additional Documents Needed',
                        'waiting_to_apply' => 'Waiting to Apply',
                        'applied' => 'Applied',
                        'offer_received' => 'Offer Received',
                        'payment_pending' => 'Payment Pending',
                        'payment_approval' => 'Payment Approval',
                        'ready_for_approval' => 'Ready for Approval',
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
