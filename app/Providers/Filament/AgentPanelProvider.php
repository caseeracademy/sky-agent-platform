<?php

namespace App\Providers\Filament;

use App\Enums\AgentNavigationGroup;
use App\Http\Middleware\EnsureUserIsAgent;
use Filament\Auth\Pages\Login;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AgentPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('agent')
            ->path('agent')
            ->login(Login::class)
            ->brandName('Sky Blue Consulting')
            ->colors([
                'primary' => Color::Sky,
            ])
            ->globalSearch(true)
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label(AgentNavigationGroup::Dashboard->getLabel()),
                NavigationGroup::make()
                    ->label(AgentNavigationGroup::StudentManagement->getLabel()),
                NavigationGroup::make()
                    ->label(AgentNavigationGroup::ApplicationManagement->getLabel()),
                NavigationGroup::make()
                    ->label(AgentNavigationGroup::CommissionPayouts->getLabel()),
                NavigationGroup::make()
                    ->label(AgentNavigationGroup::TeamManagement->getLabel()),
            ])
            ->discoverResources(in: app_path('Filament/Agent/Resources'), for: 'App\Filament\Agent\Resources')
            ->discoverPages(in: app_path('Filament/Agent/Pages'), for: 'App\Filament\Agent\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Agent/Widgets'), for: 'App\Filament\Agent\Widgets')
            ->widgets([
                \App\Filament\Agent\Widgets\AgentPersonalStats::class,
                \App\Filament\Agent\Widgets\AgentCommissionOverview::class,
                \App\Filament\Agent\Widgets\ScholarshipProgress::class,
                \App\Filament\Agent\Widgets\AgentPerformanceChart::class,
                \App\Filament\Agent\Widgets\AgentRecentApplications::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                EnsureUserIsAgent::class,
            ])
            ->authGuard('web')
            ->databaseNotifications();
    }
}
