<?php

namespace App\Filament\Resources\Students\Tables;

use App\Filament\Exports\StudentExporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('profile_image')
                    ->label('Avatar')
                    ->circular()
                    ->disk('public')
                    ->defaultImageUrl(url('/images/default-avatar.svg'))
                    ->size(40),

                TextColumn::make('agent.name')
                    ->label('Agent')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Student Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable(),
                TextColumn::make('country_of_residence')
                    ->label('Country')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('applications_count')
                    ->label('Applications')
                    ->counts('applications')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('total_commission')
                    ->label('Revenue Generated')
                    ->money('USD')
                    ->getStateUsing(function ($record) {
                        return $record->applications()
                            ->where('status', 'approved')
                            ->join('programs', 'applications.program_id', '=', 'programs.id')
                            ->sum('programs.agent_commission');
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->leftJoin('applications', 'students.id', '=', 'applications.student_id')
                            ->leftJoin('programs', 'applications.program_id', '=', 'programs.id')
                            ->where('applications.status', 'approved')
                            ->groupBy('students.id')
                            ->orderBy('total_commission', $direction)
                            ->selectRaw('students.*, SUM(programs.agent_commission) as total_commission');
                    }),
                TextColumn::make('created_at')
                    ->label('Added On')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('agent_id')
                    ->label('Filter by Agent')
                    ->relationship('agent', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(StudentExporter::class)
                    ->label('Export Students')
                    ->color('success'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->exporter(StudentExporter::class),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
