<?php

namespace App\Filament\Resources\Programs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProgramsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('university.name')
                    ->label('University')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('degree_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Certificate' => 'gray',
                        'Diploma' => 'info',
                        'Bachelor' => 'success',
                        'Master' => 'warning',
                        'PhD' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('tuition_fee')
                    ->label('Tuition Fee')
                    ->money('CAD')
                    ->sortable(),
                TextColumn::make('agent_commission')
                    ->label('Agent Commission')
                    ->money('CAD')
                    ->sortable(),
                TextColumn::make('system_commission')
                    ->label('System Commission')
                    ->money('CAD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('university_id')
                    ->label('University')
                    ->relationship('university', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('degree_type')
                    ->options([
                        'Certificate' => 'Certificate',
                        'Diploma' => 'Diploma',
                        'Bachelor' => 'Bachelor',
                        'Master' => 'Master',
                        'PhD' => 'PhD',
                    ]),
                TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->boolean()
                    ->trueLabel('Active programs only')
                    ->falseLabel('Inactive programs only')
                    ->native(false),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name', 'asc');
    }
}
