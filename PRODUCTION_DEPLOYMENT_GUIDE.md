# 🚀 Production Deployment Guide

## Safe Production Deployment Process

### 1. **Pre-Deployment Checklist**

Before deploying to production, ensure you have:

- [ ] Database backup completed
- [ ] Current production files backed up
- [ ] All tests passing locally
- [ ] Environment variables configured
- [ ] Maintenance mode ready

### 2. **Step-by-Step Deployment Process**

#### **Step 1: Backup Current Production**
```bash
# SSH into your production server
ssh ubuntu@your-server-ip

# Navigate to your application directory
cd /var/www/sky-agent-platform

# Create a backup of current version
sudo cp -r /var/www/sky-agent-platform /var/www/sky-agent-platform-backup-$(date +%Y%m%d-%H%M%S)

# Backup database
mysqldump -u sky_user -p sky_agent_platform > /var/www/sky-agent-platform-backup-$(date +%Y%m%d-%H%M%S).sql
```

#### **Step 2: Enable Maintenance Mode**
```bash
# Enable maintenance mode
php artisan down --message="Updating application - back in a few minutes"

# This will show a maintenance page to users
```

#### **Step 3: Pull Latest Changes**
```bash
# Pull latest changes from GitHub
git pull origin main

# Check if there are any new dependencies
composer install --no-dev --optimize-autoloader

# Install/update Node.js dependencies if needed
npm install
npm run build
```

#### **Step 4: Run Database Migrations**
```bash
# Run any new migrations
php artisan migrate --force

# Clear and cache configurations
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

#### **Step 5: Set Proper Permissions**
```bash
# Set ownership
sudo chown -R ubuntu:ubuntu /var/www/sky-agent-platform

# Set directory permissions
find /var/www/sky-agent-platform -type d -exec chmod 755 {} \;

# Set file permissions
find /var/www/sky-agent-platform -type f -exec chmod 644 {} \;

# Set Laravel-specific permissions
sudo chown -R ubuntu:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Ensure log file exists and has proper permissions
touch storage/logs/laravel.log
sudo chown ubuntu:www-data storage/logs/laravel.log
chmod 664 storage/logs/laravel.log
```

#### **Step 6: Test the Application**
```bash
# Test database connection
php artisan tinker
# In tinker: \App\Models\User::count()
# Exit tinker: exit

# Test routes
php artisan route:list

# Check if application loads
curl -I http://your-domain.com
```

#### **Step 7: Disable Maintenance Mode**
```bash
# Disable maintenance mode
php artisan up

# Verify application is working
curl http://your-domain.com
```

### 3. **Automated Deployment Script**

Create a deployment script for easier updates:

```bash
# Create deployment script
nano deploy.sh
```

```bash
#!/bin/bash

# Sky Agent Platform Deployment Script
echo "🚀 Starting Sky Agent Platform Deployment..."

# Enable maintenance mode
echo "📝 Enabling maintenance mode..."
php artisan down --message="Updating application - back in a few minutes"

# Backup current version
echo "💾 Creating backup..."
sudo cp -r /var/www/sky-agent-platform /var/www/sky-agent-platform-backup-$(date +%Y%m%d-%H%M%S)

# Pull latest changes
echo "📥 Pulling latest changes..."
git pull origin main

# Install dependencies
echo "📦 Installing dependencies..."
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Run migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Clear caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Set permissions
echo "🔐 Setting permissions..."
sudo chown -R ubuntu:ubuntu /var/www/sky-agent-platform
find /var/www/sky-agent-platform -type d -exec chmod 755 {} \;
find /var/www/sky-agent-platform -type f -exec chmod 644 {} \;
sudo chown -R ubuntu:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
touch storage/logs/laravel.log
sudo chown ubuntu:www-data storage/logs/laravel.log
chmod 664 storage/logs/laravel.log

# Disable maintenance mode
echo "✅ Disabling maintenance mode..."
php artisan up

echo "🎉 Deployment completed successfully!"
echo "🌐 Your application is now live at: http://your-domain.com"
```

```bash
# Make script executable
chmod +x deploy.sh

# Run deployment
./deploy.sh
```

### 4. **Rollback Process (If Something Goes Wrong)**

If you need to rollback:

```bash
# Enable maintenance mode
php artisan down --message="Rolling back - fixing issues"

# Restore from backup
sudo rm -rf /var/www/sky-agent-platform
sudo mv /var/www/sky-agent-platform-backup-YYYYMMDD-HHMMSS /var/www/sky-agent-platform

# Restore database (if needed)
mysql -u sky_user -p sky_agent_platform < /var/www/sky-agent-platform-backup-YYYYMMDD-HHMMSS.sql

# Set permissions
sudo chown -R ubuntu:ubuntu /var/www/sky-agent-platform
sudo chown -R ubuntu:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Disable maintenance mode
php artisan up
```

### 5. **Post-Deployment Verification**

After deployment, verify:

- [ ] Homepage loads correctly
- [ ] Admin panel accessible at `/admin`
- [ ] Agent portal accessible at `/agent`
- [ ] Database connections working
- [ ] All features functioning
- [ ] No error logs in `storage/logs/laravel.log`

### 6. **Monitoring Commands**

```bash
# Check application status
php artisan about

# Monitor logs
tail -f storage/logs/laravel.log

# Check database connection
php artisan tinker
# \DB::connection()->getPdo();

# Check queue status (if using queues)
php artisan queue:work --once
```

### 7. **Quick Commands Reference**

```bash
# Quick deployment
git pull origin main && composer install --no-dev --optimize-autoloader && php artisan migrate --force && php artisan config:clear && php artisan cache:clear && php artisan up

# Check application health
php artisan about

# View recent logs
tail -n 50 storage/logs/laravel.log

# Test database
php artisan tinker
```

## 🛡️ Safety Tips

1. **Always backup before deploying**
2. **Test in staging environment first** (if available)
3. **Deploy during low-traffic hours**
4. **Monitor logs after deployment**
5. **Have rollback plan ready**
6. **Keep backups for at least 7 days**

## 📞 Support

If you encounter issues during deployment:

1. Check the logs: `tail -f storage/logs/laravel.log`
2. Verify database connection: `php artisan tinker`
3. Check file permissions: `ls -la storage/`
4. Restart services if needed: `sudo systemctl restart nginx`

Remember: **Always backup before deploying!** 🛡️
