<?php

namespace App\Providers;

use Filament\Support\Facades\FilamentIcon;
use Illuminate\Support\ServiceProvider;

class IconSizeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register custom icon sizes to fix large icons issue
        $this->registerCustomIconSizes();
    }

    /**
     * Register custom icon sizes to override default large icons
     */
    private function registerCustomIconSizes(): void
    {
        // Override default Filament icons with properly sized ones
        FilamentIcon::register([
            // Table icons
            'panels::tables.actions.edit' => 'heroicon-o-pencil',
            'panels::tables.actions.delete' => 'heroicon-o-trash',
            'panels::tables.actions.view' => 'heroicon-o-eye',

            // Navigation icons
            'panels::sidebar.group.collapse-button' => 'heroicon-o-chevron-up',
            'panels::sidebar.group.expand-button' => 'heroicon-o-chevron-down',

            // Form icons
            'panels::forms.field-wrapper.prefix' => 'heroicon-o-magnifying-glass',
            'panels::forms.field-wrapper.suffix' => 'heroicon-o-information-circle',

            // Topbar icons
            'panels::topbar.global-search.field' => 'heroicon-o-magnifying-glass',
            'panels::topbar.user-menu.open' => 'heroicon-o-user-circle',

            // Action icons
            'panels::actions.create' => 'heroicon-o-plus',
            'panels::actions.edit' => 'heroicon-o-pencil',
            'panels::actions.delete' => 'heroicon-o-trash',
            'panels::actions.view' => 'heroicon-o-eye',

            // Status icons
            'panels::status-indicators.enabled' => 'heroicon-o-check-circle',
            'panels::status-indicators.disabled' => 'heroicon-o-x-circle',
        ]);
    }
}
