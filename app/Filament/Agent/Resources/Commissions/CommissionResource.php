<?php

namespace App\Filament\Agent\Resources\Commissions;

use App\Enums\AgentNavigationGroup;
use App\Filament\Agent\Resources\Commissions\Pages\ListCommissions;
use App\Filament\Agent\Resources\Commissions\Tables\CommissionsTable;
use App\Models\Commission;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;

class CommissionResource extends Resource
{
    protected static ?string $model = Commission::class;

    protected static ?string $navigationLabel = 'Commissions';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|\UnitEnum|null $navigationGroup = AgentNavigationGroup::CommissionPayouts;

    protected static ?int $navigationSort = 1;

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return CommissionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCommissions::route('/'),
        ];
    }
}
