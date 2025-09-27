<?php

namespace App\Filament\Pages;

use App\Enums\AdminNavigationGroup;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class Reports extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;
    protected string $view = 'filament.pages.reports';
    protected static string|\UnitEnum|null $navigationGroup = AdminNavigationGroup::Dashboard;
    protected static ?int $navigationSort = 2;

    public function getTitle(): string
    {
        return 'Analytics & Reports';
    }

    public function getHeading(): string
    {
        return 'Analytics & Reports';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\AdminAdvancedStats::class,
            \App\Filament\Widgets\AdminConversionFunnel::class,
            \App\Filament\Widgets\FinancialAnalytics::class,
            \App\Filament\Widgets\UniversityAnalytics::class,
        ];
    }
}
