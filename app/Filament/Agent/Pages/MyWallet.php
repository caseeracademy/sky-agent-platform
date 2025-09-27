<?php

namespace App\Filament\Agent\Pages;

use App\Filament\Agent\Widgets\MyWalletStats;
use App\Filament\Agent\Widgets\PayoutHistory;
use App\Services\WalletService;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Number;

class MyWallet extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;
    protected static ?string $title = 'My Wallet';
    protected static ?string $navigationLabel = 'My Wallet';

    protected string $view = 'filament.agent.pages.my-wallet';

    public float $availableBalance = 0;
    public float $pendingBalance = 0;

    public function mount(): void
    {
        $wallet = auth()->user()->wallet()->first();

        $this->availableBalance = (float) ($wallet->available_balance ?? 0);
        $this->pendingBalance = (float) ($wallet->pending_balance ?? 0);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            MyWalletStats::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            PayoutHistory::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('requestPayout')
                ->label('Request Payout')
                ->icon('heroicon-o-banknotes')
                ->color('primary')
                ->modalHeading('Request a Payout')
                ->modalWidth('lg')
                ->form([
                    Placeholder::make('available')
                        ->label('💰 Available Balance')
                        ->content(fn () => Number::currency($this->availableBalance, 'USD'))
                        ->extraAttributes(['class' => 'text-lg font-semibold text-green-600']),

                    Placeholder::make('pending')
                        ->label('⏳ Pending Payouts')
                        ->content(fn () => Number::currency($this->pendingBalance, 'USD'))
                        ->extraAttributes(['class' => 'text-lg font-semibold text-yellow-600']),

                    TextInput::make('amount')
                        ->label('💵 Withdrawal Amount')
                        ->numeric()
                        ->prefix('$')
                        ->minValue(0.01)
                        ->maxValue(fn () => max($this->availableBalance, 0.01))
                        ->step(0.01)
                        ->helperText(fn () => 'Maximum available: ' . Number::currency($this->availableBalance, 'USD'))
                        ->placeholder('Enter amount to withdraw')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $amount = (float) ($data['amount'] ?? 0);

                    try {
                        if ($amount <= 0) {
                            throw new \InvalidArgumentException('Amount must be greater than zero.');
                        }

                        app(WalletService::class)->requestPayout(auth()->user(), $amount);

                        Notification::make()
                            ->title('Payout Requested')
                            ->body('Your payout request has been submitted for approval.')
                            ->success()
                            ->send();

                        $this->mount();
                    } catch (\Throwable $exception) {
                        Notification::make()
                            ->title('Payout Failed')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
