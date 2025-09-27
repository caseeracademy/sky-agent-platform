<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AgentNavigationGroup: string implements HasLabel
{
    case Dashboard = 'dashboard';
    case StudentManagement = 'student_management';
    case ApplicationManagement = 'application_management';
    case CommissionPayouts = 'commission_payouts';
    case TeamManagement = 'team_management';

    public function getLabel(): string
    {
        return match ($this) {
            self::Dashboard => 'Dashboard',
            self::StudentManagement => 'Student Management',
            self::ApplicationManagement => 'Application Management',
            self::CommissionPayouts => 'Commission & Payouts',
            self::TeamManagement => 'Team Management',
        };
    }

}
