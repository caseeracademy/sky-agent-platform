#!/bin/bash

# =============================================================================
# Sky Education Portal - Production Deployment Script
# =============================================================================
# 
# This script automates the deployment process for the Sky Education Portal
# to a production environment.
# 
# Usage: ./deploy.sh [options]
# Options:
#   --skip-backup    Skip database backup
#   --skip-migrate   Skip database migrations
#   --skip-build     Skip asset building
#   --force          Force deployment without confirmation
# 
# =============================================================================

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Default options
SKIP_BACKUP=false
SKIP_MIGRATE=false
SKIP_BUILD=false
FORCE=false

# Parse command line arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        --skip-backup)
            SKIP_BACKUP=true
            shift
            ;;
        --skip-migrate)
            SKIP_MIGRATE=true
            shift
            ;;
        --skip-build)
            SKIP_BUILD=true
            shift
            ;;
        --force)
            FORCE=true
            shift
            ;;
        *)
            echo "Unknown option: $1"
            exit 1
            ;;
    esac
done

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Function to confirm action
confirm() {
    if [ "$FORCE" = true ]; then
        return 0
    fi
    
    read -p "$(echo -e ${YELLOW}$1${NC}) [y/N]: " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        return 0
    else
        return 1
    fi
}

print_status "Starting Sky Education Portal deployment..."

# =============================================================================
# PRE-DEPLOYMENT CHECKS
# =============================================================================

print_status "Running pre-deployment checks..."

# Check if we're in the correct directory
if [ ! -f "artisan" ]; then
    print_error "This script must be run from the Laravel project root directory"
    exit 1
fi

# Check if .env file exists
if [ ! -f ".env" ]; then
    print_error ".env file not found. Please create it from env.production.example"
    exit 1
fi

# Check if required commands exist
REQUIRED_COMMANDS=("php" "composer" "npm" "git")
for cmd in "${REQUIRED_COMMANDS[@]}"; do
    if ! command_exists "$cmd"; then
        print_error "Required command '$cmd' not found"
        exit 1
    fi
done

# Check PHP version
PHP_VERSION=$(php -r "echo PHP_VERSION;")
print_status "PHP Version: $PHP_VERSION"

# Check if we're in production environment
APP_ENV=$(php -r "echo env('APP_ENV', 'local');")
if [ "$APP_ENV" != "production" ]; then
    print_warning "APP_ENV is not set to 'production' (current: $APP_ENV)"
    if ! confirm "Continue anyway?"; then
        exit 1
    fi
fi

print_success "Pre-deployment checks passed"

# =============================================================================
# BACKUP DATABASE (OPTIONAL)
# =============================================================================

if [ "$SKIP_BACKUP" = false ]; then
    print_status "Creating database backup..."
    
    DB_CONNECTION=$(php -r "echo config('database.default');")
    BACKUP_DIR="storage/backups"
    BACKUP_FILE="$BACKUP_DIR/backup_$(date +%Y%m%d_%H%M%S).sql"
    
    # Create backup directory if it doesn't exist
    mkdir -p "$BACKUP_DIR"
    
    if [ "$DB_CONNECTION" = "mysql" ]; then
        DB_HOST=$(php -r "echo config('database.connections.mysql.host');")
        DB_NAME=$(php -r "echo config('database.connections.mysql.database');")
        DB_USER=$(php -r "echo config('database.connections.mysql.username');")
        DB_PASS=$(php -r "echo config('database.connections.mysql.password');")
        
        mysqldump -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_FILE"
        print_success "Database backup created: $BACKUP_FILE"
    else
        print_warning "Database backup skipped (only MySQL supported in this script)"
    fi
fi

# =============================================================================
# GIT OPERATIONS
# =============================================================================

print_status "Checking Git status..."

# Check if there are uncommitted changes
if [ -n "$(git status --porcelain)" ]; then
    print_warning "There are uncommitted changes in the repository"
    git status --short
    if ! confirm "Continue with deployment?"; then
        exit 1
    fi
fi

# Pull latest changes
print_status "Pulling latest changes from repository..."
git pull origin main

print_success "Repository updated"

# =============================================================================
# DEPENDENCY INSTALLATION
# =============================================================================

print_status "Installing/updating dependencies..."

# Install composer dependencies
print_status "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Install npm dependencies and build assets
if [ "$SKIP_BUILD" = false ]; then
    print_status "Installing NPM dependencies..."
    npm ci --production
    
    print_status "Building production assets..."
    npm run build
    print_success "Assets built successfully"
fi

print_success "Dependencies installed"

# =============================================================================
# LARAVEL APPLICATION SETUP
# =============================================================================

print_status "Setting up Laravel application..."

# Generate application key if not exists
if ! grep -q "APP_KEY=" .env || [ -z "$(grep APP_KEY= .env | cut -d'=' -f2)" ]; then
    print_status "Generating application key..."
    php artisan key:generate --no-interaction
fi

# Clear and cache configuration
print_status "Optimizing configuration..."
php artisan config:clear
php artisan config:cache

# Clear and cache routes
print_status "Optimizing routes..."
php artisan route:clear
php artisan route:cache

# Clear and cache views
print_status "Optimizing views..."
php artisan view:clear
php artisan view:cache

# Optimize autoloader
print_status "Optimizing autoloader..."
composer dump-autoload --optimize

# Cache events and listeners
print_status "Caching events..."
php artisan event:cache

print_success "Laravel optimization completed"

# =============================================================================
# DATABASE OPERATIONS
# =============================================================================

if [ "$SKIP_MIGRATE" = false ]; then
    print_status "Running database migrations..."
    
    # Check if database is accessible
    if ! php artisan migrate:status >/dev/null 2>&1; then
        print_error "Cannot connect to database. Please check your database configuration."
        exit 1
    fi
    
    # Run migrations
    php artisan migrate --force
    
    # Seed initial data if this is a fresh installation
    if confirm "Seed initial data (users, etc.)?"; then
        php artisan db:seed --class=UserSeeder --force
    fi
    
    print_success "Database operations completed"
fi

# =============================================================================
# STORAGE AND PERMISSIONS
# =============================================================================

print_status "Setting up storage and permissions..."

# Create storage link
php artisan storage:link

# Set proper permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# Clear all caches
print_status "Clearing caches..."
php artisan cache:clear
php artisan view:clear

print_success "Storage and permissions configured"

# =============================================================================
# SECURITY CHECKS
# =============================================================================

print_status "Running security checks..."

# Check if APP_DEBUG is false in production
APP_DEBUG=$(php -r "echo config('app.debug') ? 'true' : 'false';")
if [ "$APP_DEBUG" = "true" ]; then
    print_error "APP_DEBUG is set to true in production! This is a security risk."
    exit 1
fi

# Check if sensitive files are not publicly accessible
SENSITIVE_FILES=(".env" "storage/logs")
for file in "${SENSITIVE_FILES[@]}"; do
    if [ -f "public/$file" ]; then
        print_error "Sensitive file '$file' is accessible in public directory!"
        exit 1
    fi
done

print_success "Security checks passed"

# =============================================================================
# FINAL OPTIMIZATION
# =============================================================================

print_status "Running final optimizations..."

# Clear and recreate all caches
php artisan optimize

# Restart queue workers if they exist
if pgrep -f "artisan queue:work" > /dev/null; then
    print_status "Restarting queue workers..."
    php artisan queue:restart
fi

print_success "Final optimizations completed"

# =============================================================================
# DEPLOYMENT COMPLETE
# =============================================================================

print_success "🎉 Deployment completed successfully!"
print_status "Application URL: $(php -r 'echo config("app.url");')"
print_status "Environment: $(php -r 'echo config("app.env");')"
print_status "Debug Mode: $(php -r 'echo config("app.debug") ? "ON" : "OFF";')"

print_status "Next steps:"
echo "  1. Test the application thoroughly"
echo "  2. Monitor application logs for any errors"
echo "  3. Set up monitoring and alerting"
echo "  4. Configure automated backups"

print_warning "Remember to:"
echo "  - Configure your web server (Apache/Nginx)"
echo "  - Set up SSL certificates"
echo "  - Configure firewall rules"
echo "  - Set up monitoring tools"
