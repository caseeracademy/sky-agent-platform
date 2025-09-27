#!/bin/bash

echo "🚀 Deploying Sky Agent Platform to www.skybluetest.site"

# Configuration
REPO_URL="https://github.com/caseeracademy/sky-agent-platform.git"
DEPLOY_PATH="/var/www/sky-agent-platform"
APP_USER="skyapp"
DOMAIN="www.skybluetest.site"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

echo_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

echo_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if running as root/sudo
if [ "$EUID" -ne 0 ]; then
    echo_error "Please run this script with sudo"
    exit 1
fi

echo_info "Starting deployment to $DOMAIN..."

# Create application user if it doesn't exist
if ! id "$APP_USER" &>/dev/null; then
    echo_info "Creating application user: $APP_USER"
    useradd -m -s /bin/bash $APP_USER
    usermod -a -G www-data $APP_USER
fi

# Clone or update repository
if [ -d "$DEPLOY_PATH" ]; then
    echo_info "Updating existing repository..."
    cd $DEPLOY_PATH
    sudo -u $APP_USER git pull origin main
else
    echo_info "Cloning repository..."
    sudo -u $APP_USER git clone $REPO_URL $DEPLOY_PATH
    chown -R $APP_USER:www-data $DEPLOY_PATH
fi

cd $DEPLOY_PATH

# Install PHP dependencies
echo_info "Installing Composer dependencies..."
sudo -u $APP_USER composer install --no-dev --optimize-autoloader --no-interaction

# Set up environment
echo_info "Setting up environment..."
if [ ! -f ".env" ]; then
    sudo -u $APP_USER cp .env.production.skybluetest .env
    echo_info "Copied production environment file"
else
    echo_warning "Environment file already exists, skipping copy"
fi

# Generate application key if not set
echo_info "Generating application key..."
sudo -u $APP_USER php artisan key:generate --force

# Install Node.js dependencies and build assets
echo_info "Installing Node.js dependencies..."
sudo -u $APP_USER npm install

echo_info "Building production assets..."
sudo -u $APP_USER npm run build

# Run database migrations
echo_info "Running database migrations..."
sudo -u $APP_USER php artisan migrate --force

# Publish assets
echo_info "Publishing Livewire and Filament assets..."
sudo -u $APP_USER php artisan livewire:publish --assets
sudo -u $APP_USER php artisan filament:assets

# Clear and optimize caches
echo_info "Optimizing application..."
sudo -u $APP_USER php artisan optimize:clear
sudo -u $APP_USER php artisan config:cache
sudo -u $APP_USER php artisan route:cache
sudo -u $APP_USER php artisan view:cache

# Set proper permissions
echo_info "Setting file permissions..."
chown -R $APP_USER:www-data storage bootstrap/cache public/build public/livewire
chmod -R 775 storage bootstrap/cache
chmod -R 755 public/build public/livewire public/css public/js
find storage -type f -exec chmod 664 {} \;
find bootstrap/cache -type f -exec chmod 664 {} \;

# Install Nginx configuration
echo_info "Installing Nginx configuration..."
cp nginx.skybluetest.conf /etc/nginx/sites-available/skybluetest
ln -sf /etc/nginx/sites-available/skybluetest /etc/nginx/sites-enabled/

# Test Nginx configuration
echo_info "Testing Nginx configuration..."
nginx -t

if [ $? -eq 0 ]; then
    echo_info "Nginx configuration is valid"
else
    echo_error "Nginx configuration is invalid. Please check and fix."
    exit 1
fi

# Restart services
echo_info "Restarting services..."
systemctl daemon-reload
systemctl restart php8.2-fpm
systemctl reload nginx

# Install SSL certificate with Certbot
echo_info "Setting up SSL certificate..."
if command -v certbot &> /dev/null; then
    certbot --nginx -d $DOMAIN --non-interactive --agree-tos --email admin@skybluetest.site
else
    echo_warning "Certbot not found. Install it manually and run: certbot --nginx -d $DOMAIN"
fi

# Create admin user
echo_info "Creating admin user..."
sudo -u $APP_USER php artisan tinker --execute="
\$user = App\Models\User::updateOrCreate(
    ['email' => 'admin@skybluetest.site'],
    [
        'name' => 'Sky Admin',
        'password' => Hash::make('SkyBlue2024!'),
        'role' => 'super_admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]
);
echo 'Admin user created: ' . \$user->email . '\n';
"

echo_info "Deployment completed successfully!"
echo ""
echo "🎉 Sky Agent Platform is now deployed at:"
echo "   🌐 https://$DOMAIN"
echo ""
echo "📋 Admin Login Details:"
echo "   📧 Email: admin@skybluetest.site"
echo "   🔑 Password: SkyBlue2024!"
echo "   🔗 URL: https://$DOMAIN/admin"
echo ""
echo "🔧 Next steps:"
echo "   1. Update database password in .env file"
echo "   2. Test all functionality"
echo "   3. Set up monitoring and backups"
echo ""
echo "📁 Application path: $DEPLOY_PATH"
echo "👤 Application user: $APP_USER"
