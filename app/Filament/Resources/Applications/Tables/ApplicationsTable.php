<?php

namespace App\Filament\Resources\Applications\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
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
                TextColumn::make('agent.name')
                    ->label('Agent')
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
                        'gray' => ['draft', 'cancelled'],
                        'warning' => ['needs_review', 'waiting_to_apply', 'payment_pending'],
                        'info' => ['submitted', 'applied', 'payment_approval', 'ready_for_approval'],
                        'danger' => ['additional_documents_needed', 'rejected'],
                        'success' => ['approved', 'enrolled', 'offer_received'],
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'needs_review' => 'Needs Review',
                        'draft' => 'Draft',
                        'submitted' => 'Submitted',
                        'under_review' => 'Under Review',
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
                TextColumn::make('assignedAdmin.name')
                    ->label('Assigned Admin')
                    ->placeholder('Unassigned')
                    ->sortable(),
                TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'needs_review' => 'Needs Review',
                        'draft' => 'Draft',
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
                SelectFilter::make('agent_id')
                    ->label('Agent')
                    ->relationship('agent', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('assigned_admin_id')
                    ->label('Assigned Admin')
                    ->relationship('assignedAdmin', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Details')
                    ->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
