# Sky Education Portal

A comprehensive Laravel application for managing student recruitment through a network of agents.

## Features

- **Multi-role System**: Super Admin, Admin Staff, Agent Owner, Agent Staff
- **Student Management**: Complete student lifecycle management
- **Application Tracking**: Track applications from submission to completion
- **Document Management**: Upload and manage student documents
- **Commission System**: Automated commission calculations and payouts
- **Wallet System**: Track earnings and transactions
- **Admin Panels**: Separate admin and agent interfaces using Filament

## Tech Stack

- **Framework**: Laravel 12
- **Admin Panel**: Filament 3.x
- **Database**: MySQL/PostgreSQL (Production), SQLite (Development)
- **Frontend**: Tailwind CSS (via Filament)
- **Testing**: PHPUnit

## Installation

### Prerequisites

- PHP 8.4+
- Composer
- Node.js 20+
- MySQL/PostgreSQL
- Nginx/Apache

### Quick Start

1. **Clone the repository**
   ```bash
   git clone https://github.com/caseeracademy/sky-agent-platform.git
   cd sky-agent-platform
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Build assets**
   ```bash
   npm run build
   ```

6. **Start development server**
   ```bash
   php artisan serve
   ```

## Default Access

- **Admin Panel**: `/admin`
- **Agent Panel**: `/agent`
- **Default Admin**: admin@example.com / password

## Testing

Run the test suite:

```bash
php artisan test
```

## Production Deployment

1. **Server Requirements**
   - PHP 8.4+
   - MySQL 8.0+ or PostgreSQL 13+
   - Nginx or Apache
   - SSL Certificate

2. **Environment Configuration**
   - Copy `.env.example` to `.env`
   - Update database credentials
   - Set `APP_ENV=production`
   - Set `APP_DEBUG=false`

3. **Optimize for Production**
   ```bash
   composer install --no-dev --optimize-autoloader
   npm run build
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

4. **Set Permissions**
   ```bash
   chown -R www-data:www-data storage bootstrap/cache
   chmod -R 775 storage bootstrap/cache
   ```

## Architecture

The application follows Laravel best practices with:

- **Models**: User, Student, Application, Program, University, Commission, Payout
- **Controllers**: RESTful API controllers for admin and agent interfaces
- **Services**: Business logic separation (WalletService)
- **Observers**: Model event handling (ApplicationObserver)
- **Notifications**: Email notifications for key events
- **Filament Resources**: Admin and agent interface resources

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests: `php artisan test`
5. Submit a pull request

## License

This project is proprietary software.
