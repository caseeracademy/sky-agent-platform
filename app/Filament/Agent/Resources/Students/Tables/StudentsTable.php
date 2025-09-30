<?php

namespace App\Filament\Agent\Resources\Students\Tables;

// Bulk delete actions removed - only super admin can delete students
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StudentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Full Name')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('email')
                    ->label('Email Address')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->icon('heroicon-m-envelope'),

                TextColumn::make('phone')
                    ->label('Phone Number')
                    ->searchable()
                    ->icon('heroicon-m-phone'),

                TextColumn::make('nationality')
                    ->label('Nationality')
                    ->searchable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('date_of_birth')
                    ->label('Date of Birth')
                    ->date('M j, Y')
                    ->sortable(),

                TextColumn::make('applications_count')
                    ->label('Applications')
                    ->counts('applications')
                    ->badge()
                    ->color('success'),

                TextColumn::make('created_at')
                    ->label('Added')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('nationality')
                    ->options([
                        'Canadian' => 'Canadian',
                        'American' => 'American',
                        'British' => 'British',
                        'Australian' => 'Australian',
                        'Indian' => 'Indian',
                        'Chinese' => 'Chinese',
                        'Other' => 'Other',
                    ])
                    ->multiple(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('View Details')
                    ->button()
                    ->color('info'),
                EditAction::make()
                    ->label('Edit')
                    ->button(),
            ])
            ->toolbarActions([
                // Bulk delete removed - only super admin can delete students
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50]);
    }
}
