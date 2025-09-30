<?php

namespace App\Filament\Exports;

use App\Models\Student;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class StudentExporter extends Exporter
{
    protected static ?string $model = Student::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('name')->label('Student Name'),
            ExportColumn::make('email')->label('Email'),
            ExportColumn::make('phone')->label('Phone'),
            ExportColumn::make('country_of_residence')->label('Country of Residence'),
            ExportColumn::make('nationality')->label('Nationality'),
            ExportColumn::make('date_of_birth')->label('Date of Birth'),
            ExportColumn::make('agent.name')->label('Agent Name'),
            ExportColumn::make('agent.email')->label('Agent Email'),
            ExportColumn::make('applications_count')
                ->label('Total Applications')
                ->state(fn (Student $record) => $record->applications()->count()),
            ExportColumn::make('approved_applications_count')
                ->label('Approved Applications')
                ->state(fn (Student $record) => $record->applications()->where('status', 'approved')->count()),
            ExportColumn::make('total_commission')
                ->label('Total Commission Generated')
                ->state(function (Student $record) {
                    return $record->applications()
                        ->where('status', 'approved')
                        ->join('programs', 'applications.program_id', '=', 'programs.id')
                        ->sum('programs.agent_commission');
                }),
            ExportColumn::make('created_at')->label('Created At'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your student export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
