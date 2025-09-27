# Production Deployment Guide
## Sky Education Portal

This guide covers the complete process of deploying the Sky Education Portal to a production environment.

## Prerequisites

### Server Requirements
- **PHP**: 8.2 or higher with required extensions
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Database**: MySQL 8.0+ or PostgreSQL 13+ (recommended over SQLite)
- **Node.js**: 18+ for asset compilation
- **Redis**: 6.0+ (recommended for caching and sessions)
- **Composer**: Latest version
- **Git**: For version control

### PHP Extensions Required
```bash
# Check required extensions
php -m | grep -E "(openssl|pdo|mbstring|tokenizer|xml|ctype|json|bcmath|curl|fileinfo|gd|zip)"
```

Required extensions:
- openssl
- pdo
- mbstring
- tokenizer
- xml
- ctype
- json
- bcmath
- curl
- fileinfo
- gd (for image processing)
- zip

## Security Configuration

### 1. Environment Variables
Copy `env.production.example` to `.env` and configure:

```bash
cp env.production.example .env
```

**Critical Settings:**
```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_32_CHARACTER_SECRET_KEY
```

### 2. Database Security
- Use strong, unique database credentials
- Create a dedicated database user with minimal privileges
- Enable SSL connections for remote databases

### 3. File Permissions
```bash
# Set proper permissions
chmod -R 755 /path/to/your/app
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chown -R www-data:www-data /path/to/your/app
```

### 4. Web Server Configuration

#### Apache (.htaccess)
The included `.htaccess` file should handle most security requirements:
- Prevents directory listing
- Handles authorization headers
- Redirects to front controller

#### Nginx Configuration
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    
    server_name yourdomain.com;
    root /path/to/your/app/public;
    
    index index.php;
    
    # SSL Configuration
    ssl_certificate /path/to/ssl/cert.pem;
    ssl_certificate_key /path/to/ssl/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    
    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
    
    # File upload limit
    client_max_body_size 100M;
    
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
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## Deployment Process

### Option 1: Automated Deployment (Recommended)

1. **Upload files to server**
2. **Run the deployment script:**
```bash
./deploy.sh
```

The script will:
- Validate the environment
- Back up the database
- Pull latest code
- Install dependencies
- Build assets
- Run migrations
- Optimize caches
- Set permissions

### Option 2: Manual Deployment

1. **Upload Application Files**
```bash
# Clone or upload your application
git clone https://github.com/yourusername/sky-portal.git
cd sky-portal
```

2. **Install Dependencies**
```bash
# Install Composer dependencies (production only)
composer install --no-dev --optimize-autoloader

# Install and build assets
npm ci --production
npm run build
```

3. **Configure Environment**
```bash
# Copy environment file
cp env.production.example .env

# Generate application key
php artisan key:generate

# Configure your database and other settings in .env
```

4. **Set Up Database**
```bash
# Run migrations
php artisan migrate --force

# Seed initial data
php artisan db:seed --class=UserSeeder --force
```

5. **Optimize Application**
```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Create storage link
php artisan storage:link

# Optimize everything
php artisan optimize
```

## Post-Deployment Configuration

### 1. Admin User Access
The default super admin credentials are:
- **Email**: `superadmin@sky.com`
- **Password**: `password`

**⚠️ IMPORTANT**: Change these credentials immediately after deployment!

### 2. SSL Certificate Setup
```bash
# Using Let's Encrypt with Certbot
sudo certbot --nginx -d yourdomain.com
```

### 3. Database Optimization
```sql
-- For MySQL, optimize tables periodically
OPTIMIZE TABLE applications, users, students, commissions;

-- Set up automated backups
-- Add to crontab:
# 0 2 * * * mysqldump -u username -p password database_name > /backups/backup_$(date +\%Y\%m\%d).sql
```

### 4. Monitoring Setup

#### Log Monitoring
```bash
# Monitor application logs
tail -f storage/logs/laravel.log

# Set up log rotation
sudo vim /etc/logrotate.d/laravel
```

Add to logrotate config:
```
/path/to/your/app/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    notifempty
    create 0644 www-data www-data
}
```

#### Performance Monitoring
Consider setting up:
- Application Performance Monitoring (APM) tools
- Database query monitoring
- Server resource monitoring
- Uptime monitoring

### 5. Backup Strategy

#### Database Backups
```bash
#!/bin/bash
# Add to crontab for daily backups
BACKUP_DIR="/backups/database"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="sky_production"

mkdir -p $BACKUP_DIR
mysqldump -u username -p'password' $DB_NAME > $BACKUP_DIR/backup_$DATE.sql
gzip $BACKUP_DIR/backup_$DATE.sql

# Keep only last 30 days
find $BACKUP_DIR -name "backup_*.sql.gz" -type f -mtime +30 -delete
```

#### File Backups
```bash
# Backup uploaded files and storage
rsync -av storage/app/public/ /backups/files/
```

## Security Checklist

- [ ] APP_DEBUG set to false
- [ ] Strong, unique APP_KEY generated
- [ ] Database credentials secured
- [ ] File permissions properly set
- [ ] Web server configured with security headers
- [ ] SSL certificate installed and configured
- [ ] Default admin credentials changed
- [ ] Firewall rules configured
- [ ] Regular security updates scheduled
- [ ] Backup strategy implemented
- [ ] Monitoring and alerting set up

## Performance Optimization

### 1. Caching
- **Config caching**: `php artisan config:cache`
- **Route caching**: `php artisan route:cache`
- **View caching**: `php artisan view:cache`
- **Redis for sessions and cache**: Configure in `.env`

### 2. Database Optimization
- Add proper indexes to frequently queried columns
- Regular database maintenance and optimization
- Consider read replicas for high-traffic applications

### 3. Asset Optimization
- Assets are automatically optimized during build
- Consider CDN for static assets
- Enable gzip compression on web server

### 4. Queue Workers (Optional)
If using queues for heavy operations:
```bash
# Start queue worker as a service
php artisan queue:work --daemon

# Or use Supervisor for process management
```

## Troubleshooting

### Common Issues

1. **Permission Errors**
```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

2. **Database Connection Errors**
- Verify database credentials in `.env`
- Check database server is running
- Verify firewall allows database connections

3. **Asset Loading Issues**
- Ensure `npm run build` was executed
- Check `APP_URL` in `.env` matches your domain
- Verify web server serves static files correctly

4. **Session/Authentication Issues**
- Clear application cache: `php artisan cache:clear`
- Verify session configuration in `.env`
- Check session storage permissions

### Log Locations
- **Application logs**: `storage/logs/laravel.log`
- **Web server logs**: `/var/log/nginx/` or `/var/log/apache2/`
- **PHP logs**: `/var/log/php/`

## Maintenance

### Regular Tasks
- Monitor application logs daily
- Update dependencies monthly
- Database backups daily
- Security updates as available
- Performance monitoring ongoing

### Update Process
1. Test updates in staging environment
2. Back up production database and files
3. Run deployment script with updates
4. Monitor for issues post-deployment

## Support

For technical support or deployment assistance:
- Check application logs first
- Review this documentation
- Test in a staging environment
- Contact your development team

---

**Remember**: Always test your deployment process in a staging environment that mirrors production before deploying to live servers.
