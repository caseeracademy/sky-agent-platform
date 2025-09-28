#!/bin/bash

# Sky Education Portal - Production Deployment Script
# This script fixes the common production issues

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

print_status() { echo -e "${BLUE}[INFO]${NC} $1"; }
print_success() { echo -e "${GREEN}[SUCCESS]${NC} $1"; }
print_warning() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
print_error() { echo -e "${RED}[ERROR]${NC} $1"; }

echo ""
print_status "🚀 Starting Sky Education Portal Production Deployment..."
echo ""

# Check if running as root
if [ "$EUID" -eq 0 ]; then
    print_warning "Running as root. This is not recommended for production."
fi

# 1. Install PHP dependencies
print_status "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction
print_success "PHP dependencies installed"

# 2. Install Node.js dependencies
print_status "Installing Node.js dependencies..."
npm install --production
print_success "Node.js dependencies installed"

# 3. Build assets (CRITICAL FIX)
print_status "Building assets for production..."
npm run build
print_success "Assets built successfully"

# 4. Set proper permissions
print_status "Setting file permissions..."
if [ -w "storage" ]; then
    chmod -R 775 storage bootstrap/cache
    print_success "Storage permissions set"
else
    print_warning "Cannot set storage permissions (not writable)"
fi

if [ -w "public/build" ]; then
    chmod -R 755 public/build
    print_success "Build assets permissions set"
else
    print_warning "Cannot set build permissions (not writable)"
fi

# 5. Clear and cache Laravel
print_status "Clearing and caching Laravel..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache
print_success "Laravel caches updated"

# 6. Run migrations
print_status "Running database migrations..."
php artisan migrate --force
print_success "Database migrations completed"

# 7. Verify assets
print_status "Verifying asset build..."
if [ -f "public/build/manifest.json" ]; then
    print_success "Vite manifest found"
    echo "Manifest contents:"
    cat public/build/manifest.json
else
    print_error "Vite manifest not found! Assets may not load correctly."
    print_status "Run 'npm run build' manually to fix this."
fi

# 8. Check for common issues
print_status "Checking for common production issues..."

# Check if .env exists
if [ ! -f ".env" ]; then
    print_error ".env file not found!"
    print_status "Copy .env.example to .env and configure it."
fi

# Check APP_KEY
if ! grep -q "APP_KEY=" .env || grep -q "APP_KEY=$" .env; then
    print_warning "APP_KEY not set. Generating..."
    php artisan key:generate
fi

# Check database connection
print_status "Testing database connection..."
if php artisan migrate:status > /dev/null 2>&1; then
    print_success "Database connection working"
else
    print_error "Database connection failed!"
    print_status "Check your database configuration in .env"
fi

# 9. Final verification
print_status "Final verification..."

# Check if assets are accessible
if [ -f "public/build/assets/app-C6G_3qQV.css" ] || [ -f "public/build/assets/app-tLPvyI11.js" ]; then
    print_success "Vite assets found"
else
    print_warning "Vite assets not found. CSS may not load correctly."
fi

# Check user roles in database
print_status "Checking user roles..."
php artisan tinker --execute="
\$users = App\Models\User::all(['id', 'name', 'role']);
foreach(\$users as \$user) {
    echo 'User: ' . \$user->name . ' (ID: ' . \$user->id . ') - Role: ' . \$user->role . PHP_EOL;
}
"

echo ""
print_success "🎉 Production deployment completed!"
echo ""
print_status "Next steps:"
echo "1. Visit your website to check if CSS loads correctly"
echo "2. Try logging into /admin with super admin credentials"
echo "3. Try logging into /agent with agent credentials"
echo "4. Check storage/logs/laravel.log for any errors"
echo ""
print_status "If you still have issues:"
echo "- Check file permissions: chown -R www-data:www-data storage bootstrap/cache public/build"
echo "- Verify database user roles are set correctly"
echo "- Check web server configuration"
echo ""
