<?php

namespace App\Filament\Resources\Applications\Tables;

use App\Models\Commission;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\SelectAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
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
                        'info' => 'submitted',
                        'warning' => 'under_review',
                        'danger' => ['additional_documents_required', 'rejected'],
                        'success' => ['approved', 'enrolled'],
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
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
                        'draft' => 'Draft',
                        'submitted' => 'Submitted',
                        'under_review' => 'Under Review',
                        'additional_documents_required' => 'Additional Documents Required',
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
