<?php

namespace App\Filament\Agent\Resources\AgentStaff\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AgentStaffForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('role')
                    ->default('agent_staff'),
                Hidden::make('parent_agent_id')
                    ->default(auth()->id()),
                Hidden::make('is_active')
                    ->default(true),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Full name of staff member'),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->placeholder('e.g., staff@agency.com'),
                TextInput::make('password')
                    ->password()
                    ->required(fn (string $context): bool => $context === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->dehydrateStateUsing(fn (string $state): string => bcrypt($state))
                    ->minLength(8)
                    ->placeholder('Strong password (min 8 characters)'),
            ]);
    }
}
