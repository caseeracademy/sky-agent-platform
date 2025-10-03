<?php

namespace App\Filament\Resources\ScholarshipAwards;

use App\Enums\AdminNavigationGroup;
use App\Filament\Resources\ScholarshipAwards\Pages\CreateScholarshipAward;
use App\Filament\Resources\ScholarshipAwards\Pages\EditScholarshipAward;
use App\Filament\Resources\ScholarshipAwards\Pages\ListScholarshipAwards;
use App\Filament\Resources\ScholarshipAwards\Schemas\ScholarshipAwardForm;
use App\Filament\Resources\ScholarshipAwards\Tables\ScholarshipAwardsTable;
use App\Models\ScholarshipCommission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ScholarshipAwardResource extends Resource
{
    protected static ?string $model = ScholarshipCommission::class;

    protected static ?string $navigationLabel = 'Agent Scholarships';

    protected static ?string $modelLabel = 'Agent Scholarship';

    protected static ?string $pluralModelLabel = 'Agent Scholarships';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string|\UnitEnum|null $navigationGroup = AdminNavigationGroup::SystemSetup;

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return ScholarshipAwardForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ScholarshipAwardsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListScholarshipAwards::route('/'),
            'create' => CreateScholarshipAward::route('/create'),
            'edit' => EditScholarshipAward::route('/{record}/edit'),
        ];
    }
}
