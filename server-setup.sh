#!/bin/bash

# =============================================================================
# Sky Education Portal - Server Setup Script
# =============================================================================
# 
# This script sets up a fresh Ubuntu/Debian server for hosting the 
# Sky Education Portal Laravel application.
# 
# Usage: 
#   wget -O - https://raw.githubusercontent.com/your-repo/server-setup.sh | bash
#   or
#   ./server-setup.sh
# 
# Tested on: Ubuntu 20.04 LTS, Ubuntu 22.04 LTS, Debian 11
# =============================================================================

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_status() { echo -e "${BLUE}[INFO]${NC} $1"; }
print_success() { echo -e "${GREEN}[SUCCESS]${NC} $1"; }
print_warning() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
print_error() { echo -e "${RED}[ERROR]${NC} $1"; }

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    print_error "Please run this script as root (use sudo)"
    exit 1
fi

print_status "Starting Sky Education Portal server setup..."

# =============================================================================
# SYSTEM UPDATE
# =============================================================================

print_status "Updating system packages..."
apt update && apt upgrade -y
print_success "System packages updated"

# =============================================================================
# INSTALL BASIC PACKAGES
# =============================================================================

print_status "Installing basic packages..."
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

print_success "Basic packages installed"

# =============================================================================
# INSTALL PHP 8.2
# =============================================================================

print_status "Installing PHP 8.2..."

# Add PHP repository
add-apt-repository ppa:ondrej/php -y
apt update

# Install PHP and required extensions
apt install -y \
    php8.2 \
    php8.2-fpm \
    php8.2-cli \
    php8.2-common \
    php8.2-curl \
    php8.2-mbstring \
    php8.2-mysql \
    php8.2-pgsql \
    php8.2-sqlite3 \
    php8.2-xml \
    php8.2-zip \
    php8.2-bcmath \
    php8.2-gd \
    php8.2-intl \
    php8.2-redis \
    php8.2-tokenizer \
    php8.2-opcache

print_success "PHP 8.2 installed"

# Configure PHP
print_status "Configuring PHP..."

# Update PHP-FPM configuration
sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 100M/' /etc/php/8.2/fpm/php.ini
sed -i 's/post_max_size = 8M/post_max_size = 100M/' /etc/php/8.2/fpm/php.ini
sed -i 's/max_execution_time = 30/max_execution_time = 300/' /etc/php/8.2/fpm/php.ini
sed -i 's/memory_limit = 128M/memory_limit = 256M/' /etc/php/8.2/fpm/php.ini

# Enable OPcache
echo "opcache.enable=1" >> /etc/php/8.2/fpm/conf.d/10-opcache.ini
echo "opcache.memory_consumption=128" >> /etc/php/8.2/fpm/conf.d/10-opcache.ini
echo "opcache.interned_strings_buffer=8" >> /etc/php/8.2/fpm/conf.d/10-opcache.ini
echo "opcache.max_accelerated_files=4000" >> /etc/php/8.2/fpm/conf.d/10-opcache.ini
echo "opcache.revalidate_freq=60" >> /etc/php/8.2/fpm/conf.d/10-opcache.ini

systemctl restart php8.2-fpm
systemctl enable php8.2-fpm

print_success "PHP configured"

# =============================================================================
# INSTALL COMPOSER
# =============================================================================

print_status "Installing Composer..."

curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
chmod +x /usr/local/bin/composer

print_success "Composer installed"

# =============================================================================
# INSTALL NODE.JS AND NPM
# =============================================================================

print_status "Installing Node.js..."

curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt install -y nodejs

print_success "Node.js $(node --version) installed"

# =============================================================================
# INSTALL NGINX
# =============================================================================

print_status "Installing Nginx..."

apt install -y nginx
systemctl start nginx
systemctl enable nginx

# Remove default site
rm -f /etc/nginx/sites-enabled/default

print_success "Nginx installed"

# =============================================================================
# INSTALL MYSQL
# =============================================================================

print_status "Installing MySQL..."

apt install -y mysql-server

# Secure MySQL installation
mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'SecureRootPassword123!';"
mysql -e "DELETE FROM mysql.user WHERE User='';"
mysql -e "DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');"
mysql -e "DROP DATABASE IF EXISTS test;"
mysql -e "DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';"
mysql -e "FLUSH PRIVILEGES;"

systemctl start mysql
systemctl enable mysql

print_success "MySQL installed and secured"

# =============================================================================
# INSTALL REDIS
# =============================================================================

print_status "Installing Redis..."

apt install -y redis-server

# Configure Redis
sed -i 's/supervised no/supervised systemd/' /etc/redis/redis.conf
sed -i 's/# maxmemory <bytes>/maxmemory 256mb/' /etc/redis/redis.conf
sed -i 's/# maxmemory-policy noeviction/maxmemory-policy allkeys-lru/' /etc/redis/redis.conf

systemctl restart redis-server
systemctl enable redis-server

print_success "Redis installed and configured"

# =============================================================================
# INSTALL SSL WITH LET'S ENCRYPT
# =============================================================================

print_status "Installing Certbot for SSL..."

apt install -y certbot python3-certbot-nginx

print_success "Certbot installed"

# =============================================================================
# CONFIGURE FIREWALL
# =============================================================================

print_status "Configuring firewall..."

# Enable UFW
ufw --force enable

# Default policies
ufw default deny incoming
ufw default allow outgoing

# Allow SSH (adjust port if needed)
ufw allow 22/tcp

# Allow HTTP and HTTPS
ufw allow 80/tcp
ufw allow 443/tcp

# Allow MySQL (only from localhost)
ufw allow from 127.0.0.1 to any port 3306

print_success "Firewall configured"

# =============================================================================
# CONFIGURE FAIL2BAN
# =============================================================================

print_status "Configuring Fail2Ban..."

# Create jail for nginx
cat > /etc/fail2ban/jail.local << EOF
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 5

[nginx-http-auth]
enabled = true
port = http,https
logpath = /var/log/nginx/error.log

[nginx-req-limit]
enabled = true
port = http,https
logpath = /var/log/nginx/access.log
maxretry = 10

[sshd]
enabled = true
port = ssh
logpath = /var/log/auth.log
maxretry = 3
EOF

systemctl restart fail2ban
systemctl enable fail2ban

print_success "Fail2Ban configured"

# =============================================================================
# CREATE APPLICATION USER
# =============================================================================

print_status "Creating application user..."

# Create user for running the application
useradd -m -s /bin/bash skyapp
usermod -aG www-data skyapp

# Create application directory
mkdir -p /var/www/sky-portal
chown skyapp:www-data /var/www/sky-portal
chmod 755 /var/www/sky-portal

print_success "Application user created"

# =============================================================================
# CREATE DATABASE AND USER
# =============================================================================

print_status "Creating application database..."

# Generate random password for database user
DB_PASSWORD=$(openssl rand -base64 32)

# Create database and user
mysql -u root -pSecureRootPassword123! << EOF
CREATE DATABASE sky_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'skyapp'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON sky_production.* TO 'skyapp'@'localhost';
FLUSH PRIVILEGES;
EOF

# Save database credentials
cat > /home/skyapp/database-credentials.txt << EOF
Database Name: sky_production
Username: skyapp
Password: $DB_PASSWORD
EOF

chown skyapp:skyapp /home/skyapp/database-credentials.txt
chmod 600 /home/skyapp/database-credentials.txt

print_success "Database and user created"

# =============================================================================
# CONFIGURE NGINX FOR LARAVEL
# =============================================================================

print_status "Configuring Nginx for Laravel..."

cat > /etc/nginx/sites-available/sky-portal << 'EOF'
server {
    listen 80;
    listen [::]:80;
    server_name _;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    
    server_name _;
    root /var/www/sky-portal/public;
    
    index index.php;
    
    # SSL Configuration (will be configured by Certbot)
    # ssl_certificate /path/to/ssl/cert.pem;
    # ssl_certificate_key /path/to/ssl/private.key;
    
    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
    
    # File upload limit
    client_max_body_size 100M;
    
    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }
    
    error_page 404 /index.php;
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
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
}
EOF

# Enable the site
ln -sf /etc/nginx/sites-available/sky-portal /etc/nginx/sites-enabled/
nginx -t && systemctl reload nginx

print_success "Nginx configured for Laravel"

# =============================================================================
# SETUP LOG ROTATION
# =============================================================================

print_status "Setting up log rotation..."

cat > /etc/logrotate.d/sky-portal << EOF
/var/www/sky-portal/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    notifempty
    create 0644 skyapp www-data
    postrotate
        systemctl reload php8.2-fpm
    endscript
}
EOF

print_success "Log rotation configured"

# =============================================================================
# SETUP CRON FOR LARAVEL SCHEDULER
# =============================================================================

print_status "Setting up Laravel scheduler..."

# Add Laravel scheduler to skyapp user's crontab
sudo -u skyapp crontab -l > /tmp/crontab_skyapp 2>/dev/null || echo "" > /tmp/crontab_skyapp
echo "* * * * * cd /var/www/sky-portal && php artisan schedule:run >> /dev/null 2>&1" >> /tmp/crontab_skyapp
sudo -u skyapp crontab /tmp/crontab_skyapp
rm /tmp/crontab_skyapp

print_success "Laravel scheduler configured"

# =============================================================================
# FINAL SYSTEM CONFIGURATION
# =============================================================================

print_status "Applying final system configurations..."

# Increase file limits
echo "fs.file-max = 65536" >> /etc/sysctl.conf

# Configure swap if not exists and system has less than 2GB RAM
MEMORY_KB=$(grep MemTotal /proc/meminfo | awk '{print $2}')
MEMORY_GB=$((MEMORY_KB / 1024 / 1024))

if [ $MEMORY_GB -lt 2 ] && [ ! -f /swapfile ]; then
    print_status "Creating swap file (system has less than 2GB RAM)..."
    fallocate -l 1G /swapfile
    chmod 600 /swapfile
    mkswap /swapfile
    swapon /swapfile
    echo '/swapfile swap swap defaults 0 0' >> /etc/fstab
    print_success "Swap file created"
fi

# Apply sysctl changes
sysctl -p

print_success "System configuration applied"

# =============================================================================
# SETUP COMPLETE
# =============================================================================

print_success "🎉 Server setup completed!"
print_status "Server is ready for Sky Education Portal deployment"

echo ""
echo "==============================================================================="
echo "NEXT STEPS:"
echo "==============================================================================="
echo ""
print_status "1. Update your domain name in Nginx configuration:"
echo "   sudo nano /etc/nginx/sites-available/sky-portal"
echo "   Replace 'server_name _;' with 'server_name yourdomain.com;'"
echo ""
print_status "2. Get SSL certificate:"
echo "   sudo certbot --nginx -d yourdomain.com"
echo ""
print_status "3. Deploy your application to /var/www/sky-portal:"
echo "   sudo -u skyapp git clone https://github.com/yourusername/sky-portal.git /var/www/sky-portal"
echo ""
print_status "4. Database credentials saved to:"
echo "   /home/skyapp/database-credentials.txt"
echo ""
print_status "5. Configure your .env file with the database credentials"
echo ""
print_status "6. Run the deployment script:"
echo "   cd /var/www/sky-portal && sudo -u skyapp ./deploy.sh"
echo ""
echo "==============================================================================="
echo "SECURITY REMINDERS:"
echo "==============================================================================="
print_warning "- Change MySQL root password from default"
print_warning "- Update SSH configuration if needed"
print_warning "- Configure backup strategy"
print_warning "- Set up monitoring and alerting"
print_warning "- Review and update firewall rules as needed"
echo ""
print_success "Setup completed successfully!"
