# 🚀 DEPLOY TO SERVER - SIMPLE CHECKLIST

## ⚠️ STEP 1: BACKUP (CRITICAL - DON'T SKIP!)

```bash
# SSH to server
ssh user@your-server.com

# Go to project folder
cd /var/www/your-project
# or wherever your project is

# Backup database
mysqldump -u DB_USER -p DB_NAME > backup_$(date +%Y%m%d_%H%M%S).sql
```

**Save this backup file somewhere safe!**

---

## 🔧 STEP 2: DEPLOY

```bash
# Put site in maintenance mode
php artisan down

# Pull new code
git pull origin main

# Update dependencies
composer install --optimize-autoloader --no-dev

# Build frontend assets
npm install
npm run build

# Run new migrations
php artisan migrate

# Clear ALL caches
php artisan optimize:clear

# Cache for production
php artisan config:cache
php artisan route:cache

# Bring site back online
php artisan up
```

---

## ✅ STEP 3: VERIFY

### Check the site:
1. Visit: `https://your-domain.com/admin`
2. ✅ Should see "Sky Blue Consulting" (not "Laravel")
3. ✅ Colors should be sky blue
4. ✅ Global search box in header
5. ✅ No errors

### Test application workflow:
1. Create a test application
2. Admin selects commission type
3. Change status through workflow
4. ✅ Status changes should auto-refresh page
5. ✅ No errors

### Check logs:
```bash
tail -f storage/logs/laravel.log
# Should see no errors
```

---

## 🆘 IF SOMETHING BREAKS

### Quick Rollback:
```bash
# Restore database
mysql -u DB_USER -p DB_NAME < backup_YYYYMMDD_HHMMSS.sql

# Rollback code
git reset --hard HEAD~1

# Clear caches
php artisan optimize:clear

# Back online
php artisan up
```

### Then contact me with:
- Error message
- Laravel log (`storage/logs/laravel.log`)
- What step failed

---

## 📊 What Changed (Database)

**New tables added:**
- scholarship_points
- scholarship_commissions
- application_status_history
- degrees
- application_cycles
- admin_scholarship_inventories
- system_scholarship_awards

**Applications table updated:**
- Added: commission_type, payment fields
- Updated: status enum (9 statuses instead of 11)

**Data migration:**
- Old statuses automatically updated to new ones
- No data loss if migrations run successfully

---

## ⏱️ Expected Downtime

- **Backup:** 1-2 minutes
- **Deployment:** 3-5 minutes  
- **Total:** ~5-7 minutes

---

## 🎯 SUMMARY

**What to do on server:**
1. Backup database ⚠️
2. `php artisan down`
3. `git pull origin main`
4. `composer install --no-dev`
5. `npm install && npm run build`
6. `php artisan migrate`
7. `php artisan optimize:clear`
8. `php artisan config:cache`
9. `php artisan up`
10. Test the site ✅

**That's it!** 🎉

---

## 💡 PRO TIP

Run this one-liner (after backup!):

```bash
php artisan down && \
git pull origin main && \
composer install --optimize-autoloader --no-dev && \
npm install && npm run build && \
php artisan migrate --force && \
php artisan optimize:clear && \
php artisan config:cache && \
php artisan route:cache && \
php artisan up && \
echo "✅ DEPLOYMENT COMPLETE!"
```

---

**Need Help?** Send me the error message and I'll help you fix it!

**Backup First!** Always backup before deploying! ⚠️

