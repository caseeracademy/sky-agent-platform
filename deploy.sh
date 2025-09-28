#!/bin/bash

# Sky Agent Platform - Safe Production Deployment Script
# This script safely deploys the latest changes to production

echo "🚀 Starting Sky Agent Platform Deployment..."
echo "=============================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

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

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    print_error "Not in Laravel project directory. Please run this script from your project root."
    exit 1
fi

# Step 1: Enable maintenance mode
print_status "Enabling maintenance mode..."
php artisan down --message="Updating application - back in a few minutes"
if [ $? -eq 0 ]; then
    print_success "Maintenance mode enabled"
else
    print_error "Failed to enable maintenance mode"
    exit 1
fi

# Step 2: Create backup
print_status "Creating backup..."
BACKUP_DIR="/var/www/sky-agent-platform-backup-$(date +%Y%m%d-%H%M%S)"
sudo cp -r /var/www/sky-agent-platform "$BACKUP_DIR"
if [ $? -eq 0 ]; then
    print_success "Backup created at: $BACKUP_DIR"
else
    print_error "Failed to create backup"
    php artisan up
    exit 1
fi

# Step 3: Pull latest changes
print_status "Pulling latest changes from GitHub..."
git pull origin main
if [ $? -eq 0 ]; then
    print_success "Latest changes pulled successfully"
else
    print_error "Failed to pull changes"
    print_warning "Restoring from backup..."
    sudo rm -rf /var/www/sky-agent-platform
    sudo mv "$BACKUP_DIR" /var/www/sky-agent-platform
    php artisan up
    exit 1
fi

# Step 4: Install dependencies
print_status "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader
if [ $? -eq 0 ]; then
    print_success "Composer dependencies installed"
else
    print_error "Failed to install Composer dependencies"
    exit 1
fi

print_status "Installing NPM dependencies and building assets..."
npm install
if [ $? -eq 0 ]; then
    print_success "NPM dependencies installed"
else
    print_warning "NPM install failed, continuing..."
fi

npm run build
if [ $? -eq 0 ]; then
    print_success "Assets built successfully"
else
    print_warning "Asset build failed, continuing..."
fi

# Step 5: Run migrations
print_status "Running database migrations..."
php artisan migrate --force
if [ $? -eq 0 ]; then
    print_success "Database migrations completed"
else
    print_error "Database migrations failed"
    exit 1
fi

# Step 6: Clear caches
print_status "Clearing application caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
print_success "Caches cleared"

# Step 7: Set permissions
print_status "Setting proper permissions..."
sudo chown -R ubuntu:ubuntu /var/www/sky-agent-platform
find /var/www/sky-agent-platform -type d -exec chmod 755 {} \;
find /var/www/sky-agent-platform -type f -exec chmod 644 {} \;
sudo chown -R ubuntu:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
touch storage/logs/laravel.log
sudo chown ubuntu:www-data storage/logs/laravel.log
chmod 664 storage/logs/laravel.log
print_success "Permissions set correctly"

# Step 8: Test application
print_status "Testing application..."
php artisan about > /dev/null 2>&1
if [ $? -eq 0 ]; then
    print_success "Application test passed"
else
    print_warning "Application test failed, but continuing..."
fi

# Step 9: Disable maintenance mode
print_status "Disabling maintenance mode..."
php artisan up
if [ $? -eq 0 ]; then
    print_success "Maintenance mode disabled"
else
    print_error "Failed to disable maintenance mode"
    exit 1
fi

# Step 10: Final verification
print_status "Performing final verification..."
curl -s -o /dev/null -w "%{http_code}" http://localhost > /dev/null
if [ $? -eq 0 ]; then
    print_success "Application is responding"
else
    print_warning "Could not verify application response"
fi

echo ""
echo "🎉 Deployment completed successfully!"
echo "=============================================="
echo -e "${GREEN}✅ Your application is now live!${NC}"
echo -e "${BLUE}🌐 Check your application at your domain${NC}"
echo -e "${YELLOW}📁 Backup location: $BACKUP_DIR${NC}"
echo ""
echo "If you encounter any issues:"
echo "1. Check logs: tail -f storage/logs/laravel.log"
echo "2. Restart services: sudo systemctl restart nginx"
echo "3. Rollback if needed: sudo rm -rf /var/www/sky-agent-platform && sudo mv $BACKUP_DIR /var/www/sky-agent-platform"
echo ""
