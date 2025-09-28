# Sky Education Portal - Update Guide

## 🔄 **How to Update Your Existing Installation**

This guide shows you how to update your Sky Education Portal without reinstalling PHP, MySQL, or other system components.

## 📋 **Pre-Update Checklist**

Before starting, make sure you have:
- ✅ Access to your server
- ✅ Current application directory: `/var/www/sky-agent-platform`
- ✅ Database backup (recommended)
- ✅ Current user permissions

## 🚀 **Step-by-Step Update Process**

### **1. Backup Your Current Installation**

```bash
# Navigate to your application directory
cd /var/www/sky-agent-platform

# Create a backup of your current installation
sudo cp -r /var/www/sky-agent-platform /var/www/sky-agent-platform-backup-$(date +%Y%m%d_%H%M%S)

# Backup your database
mysqldump -u root -p sky_production > /var/backups/sky-database-backup-$(date +%Y%m%d_%H%M%S).sql
```

### **2. Pull Latest Changes from GitHub**

```bash
# Navigate to your application directory
cd /var/www/sky-agent-platform

# Pull the latest changes from GitHub
git pull origin main

# Check what files were updated
git log --oneline -10
```

### **3. Update PHP Dependencies**

```bash
# Update Composer dependencies
composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Clear Composer cache if needed
composer clear-cache
```

### **4. Update Node.js Dependencies and Assets**

```bash
# Update Node.js dependencies
npm install

# Build assets for production
npm run build

# Verify assets were built
ls -la public/build/
```

### **5. Run Database Migrations**

```bash
# Check migration status
php artisan migrate:status

# Run any new migrations
php artisan migrate --force

# If you get errors about missing columns, add them manually:
php artisan tinker --execute="
use Illuminate\Support\Facades\DB;
try {
    DB::statement('ALTER TABLE commissions ADD COLUMN status ENUM(\"pending\", \"paid\", \"cancelled\") DEFAULT \"pending\" AFTER amount');
    echo 'Status column added successfully!' . PHP_EOL;
} catch (Exception \$e) {
    echo 'Column already exists or error: ' . \$e->getMessage() . PHP_EOL;
}
"
```

### **6. Clear and Rebuild Caches**

```bash
# Clear all Laravel caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Rebuild caches for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### **7. Fix Permissions (If Needed)**

```bash
# Fix ownership to your user
sudo chown -R ubuntu:ubuntu /var/www/sky-agent-platform

# Set proper permissions
find /var/www/sky-agent-platform -type d -exec chmod 755 {} \;
find /var/www/sky-agent-platform -type f -exec chmod 644 {} \;

# Set Laravel-specific permissions
sudo chown -R ubuntu:www-data storage bootstrap/cache public/build
chmod -R 775 storage bootstrap/cache
chmod -R 755 public/build
```

### **8. Restart Services (If Needed)**

```bash
# Restart PHP-FPM
sudo systemctl restart php8.3-fpm

# Restart web server
sudo systemctl restart nginx
# OR if using Apache:
# sudo systemctl restart apache2
```

## 🔧 **Troubleshooting Common Update Issues**

### **Issue 1: Git Pull Conflicts**
```bash
# If you have local changes that conflict
git stash
git pull origin main
git stash pop
```

### **Issue 2: Composer Memory Issues**
```bash
# Increase memory limit
php -d memory_limit=-1 /usr/local/bin/composer install --no-dev --optimize-autoloader --ignore-platform-reqs
```

### **Issue 3: Database Migration Errors**
```bash
# Check what migrations are pending
php artisan migrate:status

# If specific migrations fail, run them individually
php artisan migrate --path=database/migrations/2024_01_15_000000_add_status_to_commissions.php --force
```

### **Issue 4: Asset Building Issues**
```bash
# Clear Node.js cache
npm cache clean --force

# Remove node_modules and reinstall
rm -rf node_modules package-lock.json
npm install
npm run build
```

### **Issue 5: Permission Issues**
```bash
# Fix ownership
sudo chown -R ubuntu:ubuntu /var/www/sky-agent-platform

# Fix Laravel directories
sudo chown -R ubuntu:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

## 🎯 **Verification Steps**

After updating, verify everything works:

### **1. Check Application Status**
```bash
# Test Laravel commands
php artisan --version
php artisan route:list | head -5

# Check if assets are built
ls -la public/build/manifest.json
```

### **2. Test Database Connection**
```bash
# Test database connection
php artisan migrate:status

# Check if all tables exist
php artisan tinker --execute="
use Illuminate\Support\Facades\DB;
\$tables = DB::select('SHOW TABLES');
foreach(\$tables as \$table) {
    echo array_values((array)\$table)[0] . PHP_EOL;
}
"
```

### **3. Test Web Application**
```bash
# Test if the application loads
curl -I http://your-domain.com

# Check for errors in logs
tail -f storage/logs/laravel.log
```

## 🔄 **Quick Update Script**

Create a quick update script:

```bash
# Create update script
cat > update-sky.sh << 'EOF'
#!/bin/bash

echo "🔄 Updating Sky Education Portal..."

# Navigate to application directory
cd /var/www/sky-agent-platform

# Pull latest changes
echo "📥 Pulling latest changes..."
git pull origin main

# Update dependencies
echo "📦 Updating dependencies..."
composer install --no-dev --optimize-autoloader --ignore-platform-reqs
npm install
npm run build

# Run migrations
echo "🗄️ Running migrations..."
php artisan migrate --force

# Clear caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Rebuild caches
echo "🔨 Rebuilding caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Fix permissions
echo "🔧 Fixing permissions..."
sudo chown -R ubuntu:ubuntu /var/www/sky-agent-platform
sudo chown -R ubuntu:www-data storage bootstrap/cache public/build
chmod -R 775 storage bootstrap/cache
chmod -R 755 public/build

echo "✅ Update completed successfully!"
EOF

# Make it executable
chmod +x update-sky.sh

# Run the update
./update-sky.sh
```

## 📝 **Update Log**

Keep track of your updates:

```bash
# Create update log
echo "$(date): Updated to latest version" >> /var/www/sky-agent-platform/update.log

# Check update history
cat /var/www/sky-agent-platform/update.log
```

## ⚠️ **Important Notes**

1. **Always backup** before updating
2. **Test in staging** if possible
3. **Monitor logs** after updating
4. **Check database** for any missing columns
5. **Verify assets** are built correctly

## 🆘 **Rollback Instructions**

If something goes wrong:

```bash
# Restore from backup
sudo rm -rf /var/www/sky-agent-platform
sudo mv /var/www/sky-agent-platform-backup-* /var/www/sky-agent-platform

# Restore database
mysql -u root -p sky_production < /var/backups/sky-database-backup-*.sql

# Restart services
sudo systemctl restart php8.3-fpm nginx
```

## 🎉 **Success!**

Your Sky Education Portal should now be updated with the latest fixes and improvements!
