#!/bin/bash

# Sky Education Portal - Production Diagnostic Script
# This script helps identify production deployment issues

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
print_status "🔍 Sky Education Portal - Production Diagnostic"
echo ""

# 1. Check Laravel environment
print_status "Checking Laravel environment..."
if [ -f ".env" ]; then
    print_success ".env file exists"
    
    # Check APP_KEY
    if grep -q "APP_KEY=" .env && ! grep -q "APP_KEY=$" .env; then
        print_success "APP_KEY is set"
    else
        print_error "APP_KEY is not set!"
    fi
    
    # Check APP_ENV
    if grep -q "APP_ENV=production" .env; then
        print_success "APP_ENV is set to production"
    else
        print_warning "APP_ENV is not set to production"
    fi
    
    # Check APP_DEBUG
    if grep -q "APP_DEBUG=false" .env; then
        print_success "APP_DEBUG is set to false"
    else
        print_warning "APP_DEBUG is not set to false"
    fi
else
    print_error ".env file not found!"
fi

# 2. Check Vite assets
print_status "Checking Vite assets..."
if [ -f "public/build/manifest.json" ]; then
    print_success "Vite manifest found"
    
    # Check if assets exist
    if [ -d "public/build/assets" ]; then
        print_success "Build assets directory exists"
        
        # Count assets
        ASSET_COUNT=$(find public/build/assets -type f | wc -l)
        print_status "Found $ASSET_COUNT asset files"
        
        # List assets
        echo "Assets:"
        ls -la public/build/assets/
    else
        print_error "Build assets directory not found!"
    fi
else
    print_error "Vite manifest not found!"
    print_status "Run 'npm run build' to fix this"
fi

# 3. Check file permissions
print_status "Checking file permissions..."
if [ -w "storage" ]; then
    print_success "Storage directory is writable"
else
    print_error "Storage directory is not writable!"
fi

if [ -w "bootstrap/cache" ]; then
    print_success "Bootstrap cache directory is writable"
else
    print_error "Bootstrap cache directory is not writable!"
fi

# 4. Check database connection
print_status "Checking database connection..."
if php artisan migrate:status > /dev/null 2>&1; then
    print_success "Database connection working"
else
    print_error "Database connection failed!"
fi

# 5. Check user roles
print_status "Checking user roles in database..."
php artisan tinker --execute="
try {
    \$users = App\Models\User::all(['id', 'name', 'email', 'role']);
    echo 'Users in database:' . PHP_EOL;
    foreach(\$users as \$user) {
        echo 'ID: ' . \$user->id . ' | Name: ' . \$user->name . ' | Email: ' . \$user->email . ' | Role: ' . \$user->role . PHP_EOL;
    }
    
    // Check for super admin
    \$superAdmin = App\Models\User::where('role', 'super_admin')->first();
    if (\$superAdmin) {
        echo 'Super admin found: ' . \$superAdmin->name . ' (' . \$superAdmin->email . ')' . PHP_EOL;
    } else {
        echo 'No super admin found!' . PHP_EOL;
    }
    
    // Check for agents
    \$agents = App\Models\User::whereIn('role', ['agent_owner', 'agent_staff'])->get();
    echo 'Agents found: ' . \$agents->count() . PHP_EOL;
} catch (Exception \$e) {
    echo 'Database error: ' . \$e->getMessage() . PHP_EOL;
}
"

# 6. Check Laravel caches
print_status "Checking Laravel caches..."
if [ -f "bootstrap/cache/config.php" ]; then
    print_success "Config cache exists"
else
    print_warning "Config cache not found"
fi

if [ -f "bootstrap/cache/routes-v7.php" ]; then
    print_success "Route cache exists"
else
    print_warning "Route cache not found"
fi

# 7. Check web server configuration
print_status "Checking web server configuration..."
if command -v nginx >/dev/null 2>&1; then
    print_status "Nginx detected"
    if nginx -t >/dev/null 2>&1; then
        print_success "Nginx configuration is valid"
    else
        print_error "Nginx configuration has errors"
    fi
elif command -v apache2 >/dev/null 2>&1; then
    print_status "Apache detected"
    if apache2ctl configtest >/dev/null 2>&1; then
        print_success "Apache configuration is valid"
    else
        print_error "Apache configuration has errors"
    fi
else
    print_warning "No web server detected"
fi

# 8. Check PHP configuration
print_status "Checking PHP configuration..."
PHP_VERSION=$(php -v | head -n 1)
print_status "PHP Version: $PHP_VERSION"

# Check required extensions
REQUIRED_EXTENSIONS=("pdo" "pdo_mysql" "mbstring" "openssl" "tokenizer" "xml" "ctype" "json" "bcmath" "fileinfo")
for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if php -m | grep -q "$ext"; then
        print_success "PHP extension $ext is loaded"
    else
        print_error "PHP extension $ext is missing!"
    fi
done

# 9. Check recent logs
print_status "Checking recent application logs..."
if [ -f "storage/logs/laravel.log" ]; then
    print_success "Laravel log file exists"
    
    # Show last 10 lines
    echo "Last 10 log entries:"
    tail -n 10 storage/logs/laravel.log
else
    print_warning "Laravel log file not found"
fi

# 10. Test routes
print_status "Testing application routes..."
echo "Testing home page..."
if curl -s -o /dev/null -w "%{http_code}" http://localhost/ | grep -q "200"; then
    print_success "Home page is accessible"
else
    print_error "Home page is not accessible"
fi

echo ""
print_status "🔍 Diagnostic completed!"
echo ""
print_status "Common fixes:"
echo "1. If assets are missing: npm run build"
echo "2. If permissions are wrong: chown -R www-data:www-data storage bootstrap/cache public/build"
echo "3. If database issues: php artisan migrate --force"
echo "4. If user roles missing: php artisan db:seed --force"
echo "5. If caches issues: php artisan config:cache && php artisan route:cache"
echo ""
