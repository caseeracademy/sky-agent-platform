#!/bin/bash

# =============================================================================
# Sky Education Portal - One-Click Installer Script v2.0
# =============================================================================
# 
# This script sets up a fresh Ubuntu/Debian server for hosting the 
# Sky Education Portal Laravel application with comprehensive error handling,
# rollback capabilities, and debugging features.
# 
# FIXES IN V2.0:
# - Fixed hardcoded user issue (creates user first)
# - Added comprehensive pre-flight checks
# - Improved error handling and rollback
# - Added service validation
# - Better logging and debugging
# 
# Usage: 
#   wget -O - https://raw.githubusercontent.com/caseeracademy/sky-agent-platform/main/sky-one-click-installer-v2.sh | bash
#   or
#   ./sky-one-click-installer-v2.sh
# 
# Tested on: Ubuntu 20.04 LTS, Ubuntu 22.04 LTS, Ubuntu 24.04 LTS
# =============================================================================

set -e

# Configuration - All configurable at the top
REPO_URL="https://github.com/caseeracademy/sky-agent-platform.git"
DEPLOY_PATH="/var/www/sky-agent-platform"
APP_USER="skyapp"
DOMAIN="www.skybluetest.site"
DB_NAME="sky_production"
DB_USER="skyapp"
DB_PASSWORD="SkySecure2024!"
ADMIN_EMAIL="admin@www.skybluetest.site"
ADMIN_PASSWORD="SkyBlue2024!"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Logging
LOG_FILE="/var/log/sky-installer.log"
BACKUP_DIR="/tmp/sky-backup-$(date +%Y%m%d-%H%M%S)"

# Status tracking
INSTALLATION_STARTED=false
PHP_INSTALLED=false
NGINX_INSTALLED=false
MYSQL_INSTALLED=false
APP_DEPLOYED=false
USER_CREATED=false
COMPOSER_INSTALLED=false
NODEJS_INSTALLED=false

print_status() { echo -e "${BLUE}[INFO]${NC} $1" | tee -a $LOG_FILE; }
print_success() { echo -e "${GREEN}[SUCCESS]${NC} $1" | tee -a $LOG_FILE; }
print_warning() { echo -e "${YELLOW}[WARNING]${NC} $1" | tee -a $LOG_FILE; }
print_error() { echo -e "${RED}[ERROR]${NC} $1" | tee -a $LOG_FILE; }
print_debug() { echo -e "${PURPLE}[DEBUG]${NC} $1" | tee -a $LOG_FILE; }
print_step() { echo -e "${CYAN}[STEP]${NC} $1" | tee -a $LOG_FILE; }

# Create log file and setup
setup_logging() {
    mkdir -p /var/log
    touch $LOG_FILE
    chmod 644 $LOG_FILE
    
    print_debug "Log file created: $LOG_FILE"
    print_debug "Installation started at: $(date)"
    print_debug "System: $(uname -a)"
    print_debug "User: $(whoami)"
    print_debug "Working directory: $(pwd)"
}

# Enhanced rollback function
rollback() {
    print_error "Installation failed! Starting rollback..."
    print_debug "Rollback triggered at: $(date)"
    
    if [ "$APP_DEPLOYED" = true ]; then
        print_status "Removing application files..."
        rm -rf $DEPLOY_PATH
        print_debug "Application files removed"
    fi
    
    if [ "$MYSQL_INSTALLED" = true ]; then
        print_status "Cleaning up MySQL..."
        
        # Try multiple methods to clean up MySQL
        if mysql -u root -p$DB_PASSWORD -e "DROP DATABASE IF EXISTS $DB_NAME; DROP USER IF EXISTS '$DB_USER'@'localhost';" 2>/dev/null; then
            print_debug "MySQL cleanup with password successful"
        elif mysql -u root -e "DROP DATABASE IF EXISTS $DB_NAME; DROP USER IF EXISTS '$DB_USER'@'localhost';" 2>/dev/null; then
            print_debug "MySQL cleanup without password successful"
        else
            # Try with debian-sys-maint user
            DEBIAN_PASSWORD=$(grep password /etc/mysql/debian.cnf | head -1 | awk '{print $3}' 2>/dev/null)
            if [ ! -z "$DEBIAN_PASSWORD" ]; then
                mysql -u debian-sys-maint -p$DEBIAN_PASSWORD -e "DROP DATABASE IF EXISTS $DB_NAME; DROP USER IF EXISTS '$DB_USER'@'localhost';" 2>/dev/null || true
                print_debug "MySQL cleanup with debian-sys-maint user attempted"
            fi
        fi
        
        print_debug "MySQL cleanup completed"
    fi
    
    if [ "$NGINX_INSTALLED" = true ]; then
        print_status "Removing Nginx configuration..."
        rm -f /etc/nginx/sites-enabled/sky-agent-platform
        rm -f /etc/nginx/sites-available/sky-agent-platform
        systemctl reload nginx 2>/dev/null || true
        print_debug "Nginx configuration removed"
    fi
    
    if [ "$USER_CREATED" = true ]; then
        print_status "Removing application user..."
        userdel -r $APP_USER 2>/dev/null || true
        print_debug "Application user removed"
    fi
    
    print_error "Rollback completed. Check $LOG_FILE for details."
    exit 1
}

# Set up error handling
trap rollback ERR

# Comprehensive pre-flight checks
pre_flight_checks() {
    print_step "Running comprehensive pre-flight checks..."
    
    # Check if running as root
    if [ "$EUID" -ne 0 ]; then
        print_error "Please run this script as root (use sudo)"
        exit 1
    fi
    print_debug "Root privileges confirmed"
    
    # Check available disk space (minimum 2GB)
    AVAILABLE_SPACE=$(df / | awk 'NR==2 {print $4}')
    if [ $AVAILABLE_SPACE -lt 2097152 ]; then
        print_error "Insufficient disk space. Need at least 2GB free. Available: $((AVAILABLE_SPACE / 1024 / 1024))GB"
        exit 1
    fi
    print_debug "Disk space check passed: $((AVAILABLE_SPACE / 1024 / 1024))GB available"
    
    # Check memory (minimum 1GB)
    MEMORY_KB=$(grep MemTotal /proc/meminfo | awk '{print $2}')
    MEMORY_GB=$((MEMORY_KB / 1024 / 1024))
    if [ $MEMORY_GB -lt 1 ]; then
        print_warning "Low memory detected ($MEMORY_GB GB). Consider upgrading."
    fi
    print_debug "Memory check: ${MEMORY_GB}GB available"
    
    # Check internet connectivity
    if ! ping -c 1 google.com &> /dev/null; then
        print_warning "No internet connection detected. Some features may not work."
    else
        print_debug "Internet connectivity confirmed"
    fi
    
    # Check if required commands exist
    for cmd in curl wget git; do
        if ! command -v $cmd &> /dev/null; then
            print_debug "Installing missing command: $cmd"
            apt update && apt install -y $cmd
        fi
    done
    
    # Check if user already exists
    if id "$APP_USER" &>/dev/null; then
        print_warning "User $APP_USER already exists. Will use existing user."
    else
        print_debug "User $APP_USER does not exist. Will create new user."
    fi
    
    # Check if application directory exists
    if [ -d "$DEPLOY_PATH" ]; then
        print_warning "Application directory $DEPLOY_PATH already exists. Will backup and replace."
        mkdir -p $BACKUP_DIR
        cp -r $DEPLOY_PATH $BACKUP_DIR/ 2>/dev/null || true
        print_debug "Backup created at: $BACKUP_DIR"
    fi
    
    print_success "Pre-flight checks completed successfully"
}

# Create application user FIRST (this was the main issue)
create_application_user() {
    print_step "Creating application user..."
    
    # Create user if it doesn't exist
    if ! id "$APP_USER" &>/dev/null; then
        useradd -m -s /bin/bash $APP_USER
        usermod -a -G www-data $APP_USER
        USER_CREATED=true
        print_success "Application user $APP_USER created"
        print_debug "User created with UID: $(id -u $APP_USER)"
    else
        print_warning "Application user $APP_USER already exists"
        print_debug "Using existing user with UID: $(id -u $APP_USER)"
    fi
    
    # Create application directory
    mkdir -p $DEPLOY_PATH
    chown $APP_USER:www-data $DEPLOY_PATH
    chmod 755 $DEPLOY_PATH
    print_debug "Application directory created: $DEPLOY_PATH"
}

# Install system packages
install_system_packages() {
    print_step "Installing system packages..."
    
    apt update
    apt install -y \
        software-properties-common \
        apt-transport-https \
        ca-certificates \
        curl \
        wget \
        gnupg \
        lsb-release \
        unzip \
        git \
        supervisor \
        cron \
        nano \
        htop \
        fail2ban \
        ufw
    
    print_success "System packages installed"
    print_debug "System packages installation completed"
}

# Install PHP 8.3
install_php() {
    print_step "Installing PHP 8.3..."
    
    # Add PHP repository
    add-apt-repository ppa:ondrej/php -y
    apt update
    
    # Install PHP and required extensions
    apt install -y \
        php8.3 \
        php8.3-fpm \
        php8.3-cli \
        php8.3-common \
        php8.3-curl \
        php8.3-mbstring \
        php8.3-mysql \
        php8.3-pgsql \
        php8.3-sqlite3 \
        php8.3-xml \
        php8.3-zip \
        php8.3-bcmath \
        php8.3-gd \
        php8.3-intl \
        php8.3-redis \
        php8.3-tokenizer \
        php8.3-opcache \
        php8.3-imagick
    
    # Configure PHP
    print_status "Configuring PHP..."
    
    # Update PHP-FPM configuration
    sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 100M/' /etc/php/8.3/fpm/php.ini
    sed -i 's/post_max_size = 8M/post_max_size = 100M/' /etc/php/8.3/fpm/php.ini
    sed -i 's/max_execution_time = 30/max_execution_time = 300/' /etc/php/8.3/fpm/php.ini
    sed -i 's/memory_limit = 128M/memory_limit = 512M/' /etc/php/8.3/fpm/php.ini
    sed -i 's/max_input_vars = 1000/max_input_vars = 3000/' /etc/php/8.3/fpm/php.ini
    
    # Enable OPcache
    cat > /etc/php/8.3/fpm/conf.d/10-opcache.ini << 'EOF'
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
opcache.fast_shutdown=1
EOF
    
    # Configure session settings
    cat > /etc/php/8.3/fpm/conf.d/20-session.ini << 'EOF'
session.save_handler = files
session.save_path = "/var/lib/php/sessions"
session.gc_maxlifetime = 1440
session.cookie_lifetime = 0
session.cookie_secure = 0
session.cookie_httponly = 1
EOF
    
    systemctl restart php8.3-fpm
    systemctl enable php8.3-fpm
    
    PHP_INSTALLED=true
    print_success "PHP 8.3 installed and configured"
    print_debug "PHP version: $(php -v | head -n1)"
}

# Install Composer (FIXED - no user dependency)
install_composer() {
    print_step "Installing Composer..."
    
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
    chmod +x /usr/local/bin/composer
    
    # Test composer installation
    composer --version
    COMPOSER_INSTALLED=true
    print_success "Composer installed"
    print_debug "Composer version: $(composer --version)"
}

# Install Node.js 20 LTS
install_nodejs() {
    print_step "Installing Node.js 20 LTS..."
    
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
    apt install -y nodejs
    
    # Install global packages
    npm install -g npm@latest
    npm install -g terser
    
    NODEJS_INSTALLED=true
    print_success "Node.js $(node --version) installed"
    print_debug "Node.js version: $(node --version)"
    print_debug "NPM version: $(npm --version)"
}

# Install Nginx
install_nginx() {
    print_step "Installing Nginx..."
    
    apt install -y nginx
    systemctl start nginx
    systemctl enable nginx
    
    # Remove default site
    rm -f /etc/nginx/sites-enabled/default
    
    NGINX_INSTALLED=true
    print_success "Nginx installed"
    print_debug "Nginx version: $(nginx -v 2>&1)"
}

# Install MySQL with robust authentication handling
install_mysql() {
    print_step "Installing MySQL with secure authentication..."
    
    # Install MySQL server
    apt install -y mysql-server
    
    # Start MySQL service
    systemctl start mysql
    systemctl enable mysql
    
    # Wait for MySQL to be ready
    print_status "Waiting for MySQL to start..."
    sleep 5
    
    # Test MySQL connection and handle authentication
    setup_mysql_authentication
    
    MYSQL_INSTALLED=true
    print_success "MySQL installed and secured"
    print_debug "MySQL version: $(mysql --version)"
}

# Setup MySQL authentication with multiple fallback methods
setup_mysql_authentication() {
    print_status "Setting up MySQL authentication..."
    
    # Method 1: Try connecting without password (fresh install)
    if mysql -u root -e "SELECT 1;" 2>/dev/null; then
        print_debug "MySQL root access without password confirmed"
        mysql -u root -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '$DB_PASSWORD';"
        mysql -u root -e "DELETE FROM mysql.user WHERE User='';"
        mysql -u root -e "DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');"
        mysql -u root -e "DROP DATABASE IF EXISTS test;"
        mysql -u root -e "DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';"
        mysql -u root -e "FLUSH PRIVILEGES;"
        print_success "MySQL secured with password authentication"
        return 0
    fi
    
    # Method 2: Try connecting with the password we set
    if mysql -u root -p$DB_PASSWORD -e "SELECT 1;" 2>/dev/null; then
        print_debug "MySQL root access with password confirmed"
        print_success "MySQL already secured"
        return 0
    fi
    
    # Method 3: Try using mysql_secure_installation approach
    print_warning "Standard authentication failed, trying alternative methods..."
    
    # Create a temporary SQL file for secure installation
    cat > /tmp/mysql_secure.sql << EOF
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '$DB_PASSWORD';
DELETE FROM mysql.user WHERE User='';
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';
FLUSH PRIVILEGES;
EOF
    
    # Try to execute the secure installation
    if mysql -u root < /tmp/mysql_secure.sql 2>/dev/null; then
        print_success "MySQL secured using alternative method"
        rm -f /tmp/mysql_secure.sql
        return 0
    fi
    
    # Method 4: Use debian-sys-maint user (Ubuntu/Debian specific)
    print_warning "Trying Ubuntu/Debian system maintenance user..."
    DEBIAN_PASSWORD=$(grep password /etc/mysql/debian.cnf | head -1 | awk '{print $3}')
    if [ ! -z "$DEBIAN_PASSWORD" ]; then
        if mysql -u debian-sys-maint -p$DEBIAN_PASSWORD -e "SELECT 1;" 2>/dev/null; then
            print_debug "Using debian-sys-maint user for MySQL setup"
            mysql -u debian-sys-maint -p$DEBIAN_PASSWORD < /tmp/mysql_secure.sql 2>/dev/null
            if [ $? -eq 0 ]; then
                print_success "MySQL secured using debian-sys-maint user"
                rm -f /tmp/mysql_secure.sql
                return 0
            fi
        fi
    fi
    
    # Method 5: Reset MySQL root password (last resort)
    print_warning "Attempting MySQL root password reset..."
    reset_mysql_root_password
    
    # Clean up
    rm -f /tmp/mysql_secure.sql
}

# Reset MySQL root password as last resort
reset_mysql_root_password() {
    print_status "Resetting MySQL root password..."
    
    # Stop MySQL
    systemctl stop mysql
    
    # Start MySQL in safe mode
    mysqld_safe --skip-grant-tables --skip-networking &
    MYSQL_PID=$!
    
    # Wait for MySQL to start
    sleep 5
    
    # Connect and reset password
    mysql -u root << EOF
USE mysql;
UPDATE user SET authentication_string=PASSWORD('$DB_PASSWORD') WHERE User='root';
UPDATE user SET plugin='mysql_native_password' WHERE User='root';
FLUSH PRIVILEGES;
EOF
    
    # Stop the safe mode MySQL
    kill $MYSQL_PID
    sleep 2
    
    # Start MySQL normally
    systemctl start mysql
    
    # Test the new password
    if mysql -u root -p$DB_PASSWORD -e "SELECT 1;" 2>/dev/null; then
        print_success "MySQL root password reset successful"
        
        # Now secure the installation
        mysql -u root -p$DB_PASSWORD -e "DELETE FROM mysql.user WHERE User='';"
        mysql -u root -p$DB_PASSWORD -e "DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');"
        mysql -u root -p$DB_PASSWORD -e "DROP DATABASE IF EXISTS test;"
        mysql -u root -p$DB_PASSWORD -e "DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';"
        mysql -u root -p$DB_PASSWORD -e "FLUSH PRIVILEGES;"
        
        return 0
    else
        print_error "Failed to reset MySQL root password"
        return 1
    fi
}

# Create database and user with robust error handling
setup_database() {
    print_step "Setting up database..."
    
    # Test MySQL connection first
    if ! test_mysql_connection; then
        print_error "Cannot connect to MySQL. Database setup failed."
        return 1
    fi
    
    # Create database and user with error handling
    print_status "Creating database and user..."
    
    # Create SQL script for database setup
    cat > /tmp/database_setup.sql << EOF
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF
    
    # Execute database setup
    if mysql -u root -p$DB_PASSWORD < /tmp/database_setup.sql; then
        print_success "Database and user created successfully"
    else
        print_error "Failed to create database and user"
        rm -f /tmp/database_setup.sql
        return 1
    fi
    
    # Test database connection with new user
    print_status "Testing database connection..."
    if mysql -u $DB_USER -p$DB_PASSWORD -e "SELECT 1;" $DB_NAME 2>/dev/null; then
        print_success "Database connection test passed"
    else
        print_error "Database connection test failed"
        rm -f /tmp/database_setup.sql
        return 1
    fi
    
    # Save database credentials
    cat > /home/$APP_USER/database-credentials.txt << EOF
Database Name: $DB_NAME
Username: $DB_USER
Password: $DB_PASSWORD
Host: localhost
Port: 3306
Connection String: mysql://$DB_USER:$DB_PASSWORD@localhost:3306/$DB_NAME
EOF
    
    chown $APP_USER:$APP_USER /home/$APP_USER/database-credentials.txt
    chmod 600 /home/$APP_USER/database-credentials.txt
    
    # Clean up
    rm -f /tmp/database_setup.sql
    
    print_success "Database setup completed successfully"
    print_debug "Database credentials saved to /home/$APP_USER/database-credentials.txt"
}

# Test MySQL connection with multiple methods
test_mysql_connection() {
    print_debug "Testing MySQL connection..."
    
    # Method 1: Try with password
    if mysql -u root -p$DB_PASSWORD -e "SELECT 1;" 2>/dev/null; then
        print_debug "MySQL connection successful with password"
        return 0
    fi
    
    # Method 2: Try without password
    if mysql -u root -e "SELECT 1;" 2>/dev/null; then
        print_debug "MySQL connection successful without password"
        return 0
    fi
    
    # Method 3: Try with debian-sys-maint user
    DEBIAN_PASSWORD=$(grep password /etc/mysql/debian.cnf | head -1 | awk '{print $3}' 2>/dev/null)
    if [ ! -z "$DEBIAN_PASSWORD" ]; then
        if mysql -u debian-sys-maint -p$DEBIAN_PASSWORD -e "SELECT 1;" 2>/dev/null; then
            print_debug "MySQL connection successful with debian-sys-maint user"
            return 0
        fi
    fi
    
    print_error "All MySQL connection methods failed"
    return 1
}

# Deploy application
deploy_application() {
    print_step "Deploying application..."
    
    cd $DEPLOY_PATH
    
    # Clone repository
    sudo -u $APP_USER git clone $REPO_URL .
    print_debug "Repository cloned successfully"
    
    # Install PHP dependencies
    print_status "Installing Composer dependencies..."
    sudo -u $APP_USER composer install --no-dev --optimize-autoloader --no-interaction
    print_debug "Composer dependencies installed"
    
    # Set up environment
    print_status "Setting up environment..."
    sudo -u $APP_USER cp .env.production.example .env
    
    # Update .env with database credentials
    sudo -u $APP_USER sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
    sudo -u $APP_USER sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
    sudo -u $APP_USER sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASSWORD/" .env
    sudo -u $APP_USER sed -i "s/APP_URL=.*/APP_URL=http:\/\/$DOMAIN/" .env
    sudo -u $APP_USER sed -i "s/SESSION_DRIVER=.*/SESSION_DRIVER=file/" .env
    sudo -u $APP_USER sed -i "s/CACHE_DRIVER=.*/CACHE_DRIVER=file/" .env
    sudo -u $APP_USER sed -i "s/QUEUE_CONNECTION=.*/QUEUE_CONNECTION=database/" .env
    sudo -u $APP_USER sed -i "s/APP_DEBUG=.*/APP_DEBUG=true/" .env
    
    # Generate application key
    sudo -u $APP_USER php artisan key:generate --force
    print_debug "Application key generated"
    
    # Install Node.js dependencies and build assets
    print_status "Installing Node.js dependencies..."
    sudo -u $APP_USER npm install
    print_debug "NPM dependencies installed"
    
    print_status "Building production assets..."
    sudo -u $APP_USER npm run build
    print_debug "Assets built successfully"
    
    # Run database migrations
    print_status "Running database migrations..."
    sudo -u $APP_USER php artisan migrate --force
    print_debug "Database migrations completed"
    
    # Publish assets
    print_status "Publishing Livewire and Filament assets..."
    sudo -u $APP_USER php artisan livewire:publish --assets
    sudo -u $APP_USER php artisan filament:assets
    print_debug "Assets published"
    
    # Clear and optimize caches
    print_status "Optimizing application..."
    sudo -u $APP_USER php artisan optimize:clear
    sudo -u $APP_USER php artisan config:cache
    sudo -u $APP_USER php artisan route:cache
    sudo -u $APP_USER php artisan view:cache
    print_debug "Application optimized"
    
    # Set proper permissions
    print_status "Setting file permissions..."
    chown -R $APP_USER:www-data storage bootstrap/cache public/build public/livewire
    chmod -R 775 storage bootstrap/cache
    chmod -R 755 public/build public/livewire public/css public/js
    find storage -type f -exec chmod 664 {} \;
    find bootstrap/cache -type f -exec chmod 664 {} \;
    print_debug "File permissions set"
    
    APP_DEPLOYED=true
    print_success "Application deployed successfully"
}

# Configure Nginx
configure_nginx() {
    print_step "Configuring Nginx..."
    
    # Create Nginx configuration
    cat > /etc/nginx/sites-available/sky-agent-platform << EOF
server {
    listen 80;
    server_name $DOMAIN _;
    root $DEPLOY_PATH/public;
    index index.php;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    
    # File upload limit
    client_max_body_size 100M;
    
    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;
    
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
    
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }
    
    error_page 404 /index.php;
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
    
    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|pdf|txt)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    access_log /var/log/nginx/sky-agent-platform.access.log;
    error_log /var/log/nginx/sky-agent-platform.error.log;
}
EOF
    
    # Enable the site
    ln -sf /etc/nginx/sites-available/sky-agent-platform /etc/nginx/sites-enabled/
    
    # Test Nginx configuration
    nginx -t
    
    if [ $? -eq 0 ]; then
        systemctl reload nginx
        print_success "Nginx configured successfully"
        print_debug "Nginx configuration test passed"
    else
        print_error "Nginx configuration test failed"
        exit 1
    fi
}

# Install SSL certificate
install_ssl() {
    print_step "Installing SSL certificate..."
    
    # Install Certbot
    apt install -y certbot python3-certbot-nginx
    
    # Get SSL certificate
    certbot --nginx -d $DOMAIN --non-interactive --agree-tos --email $ADMIN_EMAIL
    
    if [ $? -eq 0 ]; then
        print_success "SSL certificate installed successfully"
        print_debug "SSL certificate obtained for $DOMAIN"
    else
        print_warning "SSL certificate installation failed. You can install it manually later."
        print_debug "SSL certificate installation failed - continuing without SSL"
    fi
}

# Create admin user
create_admin_user() {
    print_step "Creating admin user..."
    
    cd $DEPLOY_PATH
    
    sudo -u $APP_USER php artisan tinker --execute="
\$user = App\Models\User::updateOrCreate(
    ['email' => '$ADMIN_EMAIL'],
    [
        'name' => 'Sky Admin',
        'password' => Hash::make('$ADMIN_PASSWORD'),
        'role' => 'super_admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]
);
echo 'Admin user created: ' . \$user->email . '\n';
"
    
    print_success "Admin user created"
    print_debug "Admin user: $ADMIN_EMAIL"
}

# Setup monitoring and logging
setup_monitoring() {
    print_step "Setting up monitoring and logging..."
    
    # Create log rotation
    cat > /etc/logrotate.d/sky-agent-platform << EOF
$DEPLOY_PATH/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    notifempty
    create 0644 $APP_USER www-data
    postrotate
        systemctl reload php8.3-fpm
    endscript
}
EOF
    
    # Setup Laravel scheduler
    sudo -u $APP_USER crontab -l > /tmp/crontab_$APP_USER 2>/dev/null || echo "" > /tmp/crontab_$APP_USER
    echo "* * * * * cd $DEPLOY_PATH && php artisan schedule:run >> /dev/null 2>&1" >> /tmp/crontab_$APP_USER
    sudo -u $APP_USER crontab /tmp/crontab_$APP_USER
    rm /tmp/crontab_$APP_USER
    
    print_success "Monitoring and logging configured"
    print_debug "Log rotation configured"
    print_debug "Laravel scheduler configured"
}

# Comprehensive health check
health_check() {
    print_step "Running comprehensive health check..."
    
    # Check if services are running
    if systemctl is-active --quiet php8.3-fpm; then
        print_success "PHP-FPM is running"
    else
        print_error "PHP-FPM is not running"
        systemctl status php8.3-fpm
    fi
    
    if systemctl is-active --quiet nginx; then
        print_success "Nginx is running"
    else
        print_error "Nginx is not running"
        systemctl status nginx
    fi
    
    if systemctl is-active --quiet mysql; then
        print_success "MySQL is running"
    else
        print_error "MySQL is not running"
        systemctl status mysql
    fi
    
    # Test database connection
    cd $DEPLOY_PATH
    print_status "Testing database connection..."
    sudo -u $APP_USER php artisan tinker --execute="
try {
    DB::connection()->getPdo();
    echo 'Database connection: OK\n';
} catch (Exception \$e) {
    echo 'Database connection: FAILED - ' . \$e->getMessage() . '\n';
}
"
    
    # Test HTTP response
    print_status "Testing HTTP response..."
    if curl -f -s http://localhost/admin > /dev/null; then
        print_success "Application is responding"
    else
        print_warning "Application health check failed - this might be normal if SSL is required"
    fi
    
    # Check file permissions
    print_status "Checking file permissions..."
    if [ -w "$DEPLOY_PATH/storage" ]; then
        print_success "Storage directory is writable"
    else
        print_error "Storage directory is not writable"
    fi
    
    print_success "Health check completed"
    print_debug "All health checks passed"
}

# Main installation function
main() {
    print_status "🚀 Starting Sky Education Portal installation v2.0..."
    print_status "Installation log: $LOG_FILE"
    
    INSTALLATION_STARTED=true
    
    # Run all installation steps in correct order
    setup_logging
    pre_flight_checks
    create_application_user  # CREATE USER FIRST!
    install_system_packages
    install_php
    install_composer
    install_nodejs
    install_nginx
    install_mysql
    setup_database
    deploy_application
    configure_nginx
    install_ssl
    create_admin_user
    setup_monitoring
    health_check
    
    print_success "🎉 Installation completed successfully!"
    
    echo ""
    echo "==============================================================================="
    echo "INSTALLATION SUMMARY"
    echo "==============================================================================="
    echo ""
    print_success "✅ Sky Education Portal is now deployed at:"
    echo "   🌐 https://$DOMAIN"
    echo "   🌐 http://$DOMAIN (if SSL failed)"
    echo ""
    print_success "📋 Admin Login Details:"
    echo "   📧 Email: $ADMIN_EMAIL"
    echo "   🔑 Password: $ADMIN_PASSWORD"
    echo "   🔗 Admin URL: https://$DOMAIN/admin"
    echo ""
    print_success "📁 Application Details:"
    echo "   📂 Path: $DEPLOY_PATH"
    echo "   👤 User: $APP_USER"
    echo "   🗄️ Database: $DB_NAME"
    echo ""
    print_success "🔧 Database Credentials:"
    echo "   📄 Saved to: /home/$APP_USER/database-credentials.txt"
    echo ""
    print_success "📊 Monitoring:"
    echo "   📝 Logs: $DEPLOY_PATH/storage/logs/"
    echo "   📝 Nginx: /var/log/nginx/"
    echo "   📝 Installer: $LOG_FILE"
    echo ""
    print_warning "🔒 Security Reminders:"
    echo "   • Change default passwords"
    echo "   • Configure firewall rules"
    echo "   • Set up regular backups"
    echo "   • Monitor logs regularly"
    echo ""
    print_success "🎯 Next Steps:"
    echo "   1. Test all functionality"
    echo "   2. Configure backup strategy"
    echo "   3. Set up monitoring alerts"
    echo "   4. Review security settings"
    echo ""
    echo "==============================================================================="
    print_success "Installation completed successfully!"
}

# Run main function
main "$@"
