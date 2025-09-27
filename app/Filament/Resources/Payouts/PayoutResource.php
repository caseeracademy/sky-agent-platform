<?php

namespace App\Filament\Resources\Payouts;

use App\Enums\AdminNavigationGroup;
use App\Filament\Resources\Payouts\Pages\ListPayouts;
use App\Models\Payout;
use App\Services\WalletService;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;

class PayoutResource extends Resource
{
    protected static ?string $model = Payout::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static string|\UnitEnum|null $navigationGroup = AdminNavigationGroup::FinancialManagement;

    protected static ?string $navigationLabel = 'Payout Requests';

    protected static ?int $navigationSort = 1;

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('agent.name')->label('Agent')->sortable()->searchable(),
                TextColumn::make('amount')->money('USD')->sortable(),
                BadgeColumn::make('status')->colors([
                    'warning' => 'pending',
                    'success' => 'paid',
                    'danger' => 'rejected',
                ]),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->action(fn (Payout $record) => app(WalletService::class)->approvePayout($record))
                    ->visible(fn (Payout $record) => $record->status === 'pending'),
                Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('reason')->label('Reason')->required(),
                    ])
                    ->action(function (Payout $record, array $data): void {
                        $record->update(['admin_notes' => $data['reason'] ?? null]);
                        app(WalletService::class)->rejectPayout($record);
                    })
                    ->visible(fn (Payout $record) => $record->status === 'pending'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayouts::route('/'),
        ];
    }
}
