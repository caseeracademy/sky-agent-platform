<?php

namespace App\Filament\Exports;

use App\Models\User;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class UserExporter extends Exporter
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('name')->label('Name'),
            ExportColumn::make('email')->label('Email'),
            ExportColumn::make('role')->label('Role'),
            ExportColumn::make('is_active')
                ->label('Active')
                ->state(fn (User $record) => $record->is_active ? 'Yes' : 'No'),
            ExportColumn::make('students_count')
                ->label('Total Students')
                ->state(fn (User $record) => $record->students()->count()),
            ExportColumn::make('applications_count')
                ->label('Total Applications')
                ->state(fn (User $record) => $record->applications()->count()),
            ExportColumn::make('approved_applications_count')
                ->label('Approved Applications')
                ->state(fn (User $record) => $record->applications()->where('status', 'approved')->count()),
            ExportColumn::make('total_commission_earned')
                ->label('Total Commission Earned')
                ->state(fn (User $record) => $record->commissions()->sum('amount')),
            ExportColumn::make('pending_commission')
                ->label('Pending Commission')
                ->state(fn (User $record) => $record->wallet?->balance ?? 0),
            ExportColumn::make('created_at')->label('Registered At'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your user export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
