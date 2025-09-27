# Coding Standards and Workflow

This document outlines the coding standards, naming conventions, and workflow rules for this project. Adhering to these standards is mandatory to maintain code quality, consistency, and readability.

## Naming Conventions

- **Controllers:** `PascalCaseController` (e.g., `ApplicationController.php`, `AgentStaffController.php`)
- **Database Tables:** `snake_case_plural` (e.g., `universities`, `application_logs`, `agent_staff`)
- **Database Columns:** `snake_case` (e.g., `agent_commission`, `authorized_user_id`)
- **Models:** `PascalCaseSingular` (e.g., `University`, `ApplicationLog`)
- **Routes (URL):** `kebab-case` (e.g., `/agent/payout-requests`)
- **Route Names:** `snake_case` (e.g., `agent.payouts.store`)
- **Filament Resources:** `PascalCaseResource` (e.g., `ApplicationResource.php`)
- **Blade Views:** `kebab-case.blade.php` (e.g., `list-students.blade.php`)

## Git Workflow

1.  **Branching:** All new work must be done on a separate feature or bugfix branch.
    - `feature/feature-name` (e.g., `feature/agent-payout-system`)
    - `bugfix/bug-name` (e.g., `bugfix/application-status-bug`)

2.  **Commit Messages:** All commit messages must follow the **Conventional Commits** specification. This is crucial for a clear and automated version history.
    - **Format:** `<type>(<scope>): <subject>`
    - **Examples:**
        - `feat(agent): add payout request form`
        - `fix(admin): correct commission calculation`
        - `docs(readme): update setup instructions`
        - `refactor(auth): simplify login controller logic`

## Code Style

- **Formatting:** All PHP code must be formatted using **Laravel Pint**. Before committing, run the following command to automatically format your files:
  ```bash
  ./vendor/bin/pint