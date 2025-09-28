# Sky Education Portal - Deployment Guide

## Production Deployment

### Prerequisites

- **Server**: Ubuntu 20.04+ / CentOS 8+ / Debian 11+
- **PHP**: 8.4+ with extensions: BCMath, Ctype, cURL, DOM, Fileinfo, JSON, Mbstring, OpenSSL, PCRE, PDO, Tokenizer, XML, GD, MySQL/PostgreSQL
- **Database**: MySQL 8.0+ or PostgreSQL 13+
- **Web Server**: Nginx 1.18+ or Apache 2.4+
- **Node.js**: 20+ (for asset compilation)
- **Composer**: Latest version

### Quick Deployment Steps

#### 1. Server Setup

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.4
sudo apt install software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php8.4 php8.4-fpm php8.4-mysql php8.4-xml php8.4-mbstring php8.4-curl php8.4-gd php8.4-zip php8.4-bcmath php8.4-intl

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install nodejs

# Install MySQL
sudo apt install mysql-server
sudo mysql_secure_installation
```

#### 2. Application Deployment

```bash
# Clone repository
git clone https://github.com/caseeracademy/sky-agent-platform.git
cd sky-agent-platform

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate --force
php artisan db:seed --force

# Set permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### 3. Web Server Configuration

**Nginx Configuration** (`/etc/nginx/sites-available/sky-portal`):

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/sky-agent-platform/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Enable site:**
```bash
sudo ln -s /etc/nginx/sites-available/sky-portal /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

#### 4. SSL Certificate (Let's Encrypt)

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com
```

#### 5. Database Configuration

**MySQL Setup:**
```sql
CREATE DATABASE sky_production;
CREATE USER 'sky_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON sky_production.* TO 'sky_user'@'localhost';
FLUSH PRIVILEGES;
```

**Update `.env`:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sky_production
DB_USERNAME=sky_user
DB_PASSWORD=secure_password
```

### Environment Variables

| Variable | Description | Example |
|----------|-------------|---------|
| `APP_ENV` | Environment | `production` |
| `APP_DEBUG` | Debug mode | `false` |
| `APP_URL` | Application URL | `https://your-domain.com` |
| `DB_CONNECTION` | Database type | `mysql` |
| `DB_HOST` | Database host | `127.0.0.1` |
| `DB_DATABASE` | Database name | `sky_production` |
| `DB_USERNAME` | Database user | `sky_user` |
| `DB_PASSWORD` | Database password | `secure_password` |
| `MAIL_MAILER` | Mail driver | `smtp` |
| `MAIL_HOST` | SMTP host | `smtp.gmail.com` |
| `MAIL_PORT` | SMTP port | `587` |
| `MAIL_USERNAME` | SMTP username | `your-email@gmail.com` |
| `MAIL_PASSWORD` | SMTP password | `your-app-password` |

### Security Checklist

- [ ] Set `APP_DEBUG=false`
- [ ] Set `APP_ENV=production`
- [ ] Use strong database passwords
- [ ] Enable SSL/HTTPS
- [ ] Set proper file permissions (755 for directories, 644 for files)
- [ ] Configure firewall (UFW/iptables)
- [ ] Regular security updates
- [ ] Database backups

### Monitoring & Maintenance

**Log Files:**
- Application: `storage/logs/laravel.log`
- Nginx: `/var/log/nginx/`
- PHP-FPM: `/var/log/php8.4-fpm.log`

**Backup Commands:**
```bash
# Database backup
mysqldump -u sky_user -p sky_production > backup_$(date +%Y%m%d).sql

# Application backup
tar -czf sky_backup_$(date +%Y%m%d).tar.gz /path/to/sky-agent-platform
```

**Update Process:**
```bash
cd /path/to/sky-agent-platform
git pull origin main
composer install --no-dev --optimize-autoloader
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Troubleshooting

**Common Issues:**

1. **Permission Errors:**
   ```bash
   sudo chown -R www-data:www-data storage bootstrap/cache
   sudo chmod -R 775 storage bootstrap/cache
   ```

2. **Composer Memory Issues:**
   ```bash
   php -d memory_limit=-1 /usr/local/bin/composer install
   ```

3. **Database Connection:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

4. **Asset Issues:**
   ```bash
   npm run build
   php artisan view:clear
   ```

### Support

For deployment issues, check:
- Laravel logs: `storage/logs/laravel.log`
- Web server logs: `/var/log/nginx/error.log`
- PHP-FPM logs: `/var/log/php8.4-fpm.log`
