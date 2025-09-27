<?php

namespace App\Filament\Agent\Resources\AgentStaff;

use App\Enums\AgentNavigationGroup;
use App\Filament\Agent\Resources\AgentStaff\Pages\CreateAgentStaff;
use App\Filament\Agent\Resources\AgentStaff\Pages\EditAgentStaff;
use App\Filament\Agent\Resources\AgentStaff\Pages\ListAgentStaff;
use App\Filament\Agent\Resources\AgentStaff\Schemas\AgentStaffForm;
use App\Filament\Agent\Resources\AgentStaff\Tables\AgentStaffTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AgentStaffResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|\UnitEnum|null $navigationGroup = AgentNavigationGroup::TeamManagement;

    protected static ?string $navigationLabel = 'My Team';

    protected static ?string $modelLabel = 'Staff Member';

    protected static ?string $pluralModelLabel = 'Staff Members';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return AgentStaffForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AgentStaffTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Only show staff members belonging to the authenticated agent owner.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('parent_agent_id', auth()->id());
    }

    /**
     * Only Agent Owners can view this resource.
     */
    public static function canViewAny(): bool
    {
        return auth()->check() && auth()->user()->role === 'agent_owner';
    }

    /**
     * Automatically set role and parent_agent_id before creating.
     */
    protected static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['role'] = 'agent_staff';
        $data['parent_agent_id'] = auth()->id();
        $data['is_active'] = true;
        return $data;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAgentStaff::route('/'),
            'create' => CreateAgentStaff::route('/create'),
            'edit' => EditAgentStaff::route('/{record}/edit'),
        ];
    }
}
