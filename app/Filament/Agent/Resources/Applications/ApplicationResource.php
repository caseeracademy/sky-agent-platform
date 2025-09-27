<?php

namespace App\Filament\Agent\Resources\Applications;

use App\Enums\AgentNavigationGroup;
use App\Filament\Agent\Resources\Applications\Pages\CreateApplication;
use App\Filament\Agent\Resources\Applications\Pages\EditApplication;
use App\Filament\Agent\Resources\Applications\Pages\ListApplications;
use App\Filament\Agent\Resources\Applications\Pages\ViewApplication;
use App\Filament\Agent\Resources\Applications\Schemas\ApplicationForm;
use App\Filament\Agent\Resources\Applications\Tables\ApplicationsTable;
use App\Models\Application;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|\UnitEnum|null $navigationGroup = AgentNavigationGroup::ApplicationManagement;

    protected static ?string $navigationLabel = 'My Applications';

    protected static ?string $modelLabel = 'Application';

    protected static ?string $pluralModelLabel = 'Applications';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ApplicationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ApplicationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Scope the query to only show applications for students belonging to the agent's team.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereHas('student', fn ($query) => $query->where('agent_id', auth()->id()));
    }

    // This method was moved to the CreateApplication page class for proper implementation

    public static function getPages(): array
    {
        return [
            'index' => ListApplications::route('/'),
            'create' => CreateApplication::route('/create'),
            'view' => ViewApplication::route('/{record}'),
            'edit' => EditApplication::route('/{record}/edit'),
        ];
    }
}
