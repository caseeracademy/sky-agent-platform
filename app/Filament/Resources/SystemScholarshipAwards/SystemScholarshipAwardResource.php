<?php

namespace App\Filament\Resources\SystemScholarshipAwards;

use App\Enums\AdminNavigationGroup;
use App\Filament\Resources\SystemScholarshipAwards\Pages\ListSystemScholarshipAwards;
use App\Filament\Resources\SystemScholarshipAwards\Pages\ViewSystemScholarshipAward;
use App\Filament\Resources\SystemScholarshipAwards\Tables\SystemScholarshipAwardsTable;
use App\Models\SystemScholarshipDisplay;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SystemScholarshipAwardResource extends Resource
{
    protected static ?string $model = SystemScholarshipDisplay::class;

    protected static ?string $slug = 'system-scholarship-awards';

    protected static ?string $recordRouteKeyName = 'record';

    protected static ?string $modelLabel = 'System Scholarship Award';

    protected static ?string $pluralModelLabel = 'System Scholarship Awards';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static ?string $navigationLabel = 'System Scholarships';

    protected static string|\UnitEnum|null $navigationGroup = AdminNavigationGroup::ScholarshipManagement;

    protected static ?int $navigationSort = 1;

    public static function table(Table $table): Table
    {
        return SystemScholarshipAwardsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSystemScholarshipAwards::route('/'),
            'view' => ViewSystemScholarshipAward::route('/{record}'),
        ];
    }

    /**
     * Get all system scholarship displays.
     * Note: Actual data is provided in ListSystemScholarshipAwards page via getTableRecords().
     */
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        // Return empty query since data is provided directly in ListSystemScholarshipAwards
        return SystemScholarshipDisplay::query()->whereRaw('1 = 0'); // Always empty
    }
}
