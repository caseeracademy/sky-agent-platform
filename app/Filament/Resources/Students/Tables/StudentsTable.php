<?php

namespace App\Filament\Resources\Students\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Actions\HeaderAction;
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
                HeaderAction::make('export_csv')
                    ->label('CSV')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function ($livewire) {
                        $query = $livewire->getFilteredTableQuery();

                        return response()->streamDownload(function () use ($query) {
                            $students = $query->with('agent')->get();
                            $csv = fopen('php://output', 'w');

                            // Headers
                            fputcsv($csv, ['ID', 'Name', 'Email', 'Phone', 'Passport', 'Nationality', 'Country', 'Agent', 'Applications', 'Created At']);

                            // Data
                            foreach ($students as $student) {
                                fputcsv($csv, [
                                    $student->id,
                                    $student->name,
                                    $student->email,
                                    $student->phone_number ?? $student->phone,
                                    $student->passport_number,
                                    $student->nationality,
                                    $student->country_of_residence,
                                    $student->agent->name ?? 'N/A',
                                    $student->applications()->count(),
                                    $student->created_at->format('Y-m-d H:i'),
                                ]);
                            }

                            fclose($csv);
                        }, 'students_export_'.date('Y-m-d_His').'.csv');
                    }),
                HeaderAction::make('export_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-text')
                    ->color('danger')
                    ->action(function ($livewire) {
                        $query = $livewire->getFilteredTableQuery();
                        $students = $query->with('agent')->get();

                        $pdf = \App::make('dompdf.wrapper');
                        $pdf->loadHTML(view('exports.students-pdf', ['students' => $students])->render());

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'students_export_'.date('Y-m-d_His').'.pdf');
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
