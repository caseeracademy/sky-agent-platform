<?php

namespace App\Filament\Agent\Resources\Scholarships;

use App\Enums\AgentNavigationGroup;
use App\Filament\Agent\Resources\Scholarships\Pages\ConvertScholarship;
use App\Filament\Agent\Resources\Scholarships\Pages\ListScholarships;
use App\Filament\Agent\Resources\Scholarships\Pages\ViewScholarship;
use App\Filament\Agent\Resources\Scholarships\Tables\ScholarshipsTable;
use App\Models\ScholarshipDisplay;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ScholarshipResource extends Resource
{
    protected static ?string $model = ScholarshipDisplay::class;

    protected static ?string $navigationLabel = 'My Scholarships';

    protected static ?string $modelLabel = 'Scholarship';

    protected static ?string $pluralModelLabel = 'Scholarships';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string|\UnitEnum|null $navigationGroup = AgentNavigationGroup::CommissionPayouts;

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return ScholarshipsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListScholarships::route('/'),
            'view' => ViewScholarship::route('/{record}'),
            'convert' => ConvertScholarship::route('/{record}/convert'),
        ];
    }

    /**
     * Get all scholarship displays for the authenticated agent (both completed and progress).
     * Note: Actual data is provided in ListScholarships page via getTableRecords().
     */
    public static function getEloquentQuery(): Builder
    {
        // Return empty query since data is provided directly in ListScholarships
        return ScholarshipDisplay::query()->whereRaw('1 = 0'); // Always empty
    }

    public static function canCreate(): bool
    {
        return false; // Scholarships are automatically created
    }
}
