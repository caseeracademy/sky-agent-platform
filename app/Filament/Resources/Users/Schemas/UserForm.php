<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use App\Models\User;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(User::class, 'email', ignoreRecord: true),
                Select::make('role')
                    ->required()
                    ->options([
                        'super_admin' => 'Super Admin',
                        'admin_staff' => 'Admin Staff',
                        'agent_owner' => 'Agent Owner',
                        'agent_staff' => 'Agent Staff',
                    ])
                    ->default('agent_staff')
                    ->reactive()
                    ->searchable(),
                Select::make('parent_agent_id')
                    ->label('Parent Agent')
                    ->relationship('parentAgent', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->visible(fn (callable $get) => $get('role') === 'agent_staff')
                    ->options(function () {
                        return User::where('role', 'agent_owner')->pluck('name', 'id');
                    }),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
                TextInput::make('password')
                    ->password()
                    ->required(fn (string $context): bool => $context === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->dehydrateStateUsing(fn (string $state): string => bcrypt($state)),
                DateTimePicker::make('email_verified_at')
                    ->label('Email Verified At')
                    ->nullable(),
            ]);
    }
}
