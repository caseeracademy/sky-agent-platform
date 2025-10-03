<?php

namespace App\Providers\Filament;

use App\Enums\AdminNavigationGroup;
use App\Http\Middleware\CheckUserRole;
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

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('Sky Blue Consulting')
            ->colors([
                'primary' => Color::Sky,
            ])
            ->maxContentWidth('full')
            ->globalSearch(true)
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label(AdminNavigationGroup::Dashboard->getLabel()),
                NavigationGroup::make()
                    ->label(AdminNavigationGroup::ApplicationManagement->getLabel()),
                NavigationGroup::make()
                    ->label(AdminNavigationGroup::FinancialManagement->getLabel()),
                NavigationGroup::make()
                    ->label(AdminNavigationGroup::ScholarshipManagement->getLabel()),
                NavigationGroup::make()
                    ->label(AdminNavigationGroup::UserManagement->getLabel()),
                NavigationGroup::make()
                    ->label(AdminNavigationGroup::SystemSetup->getLabel()),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                \App\Filament\Widgets\AdminApplicationsOverview::class,
                \App\Filament\Widgets\AdminAdvancedStats::class,
                \App\Filament\Widgets\AdminCommissionStats::class,
                \App\Filament\Widgets\AdminConversionFunnel::class,
                \App\Filament\Widgets\FinancialAnalytics::class,
                \App\Filament\Widgets\UniversityAnalytics::class,
                \App\Filament\Widgets\AdminRecentActivity::class,
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
                CheckUserRole::class.':super_admin,admin_staff',
            ]);
    }
}
