<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AdminNavigationGroup: string implements HasLabel
{
    case Dashboard = 'dashboard';
    case ApplicationManagement = 'application_management';
    case FinancialManagement = 'financial_management';
    case UserManagement = 'user_management';
    case SystemSetup = 'system_setup';

    public function getLabel(): string
    {
        return match ($this) {
            self::Dashboard => 'Dashboard & Reporting',
            self::ApplicationManagement => 'Application Management',
            self::FinancialManagement => 'Financial Management',
            self::UserManagement => 'User Management',
            self::SystemSetup => 'System Setup',
        };
    }

}
