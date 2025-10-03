# 🚀 SERVER DEPLOYMENT INSTRUCTIONS

## ⚠️ IMPORTANT - READ BEFORE PULLING!

This update includes **major database changes**. Follow these steps **CAREFULLY** to avoid data loss.

## 📋 Pre-Deployment Checklist

### 1. Backup Current Database (CRITICAL!)
```bash
# SSH into your server first
ssh user@your-server.com

# Navigate to project directory
cd /path/to/your/project

# Backup database
php artisan db:backup
# OR manually export:
mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql
```

### 2. Put Application in Maintenance Mode
```bash
php artisan down
```

## 🔄 Deployment Steps

### Step 1: Pull Latest Code
```bash
git pull origin main
```

### Step 2: Install/Update Dependencies
```bash
composer install --optimize-autoloader --no-dev
npm install
npm run build
```

### Step 3: Run Migrations
```bash
# This will:
# - Add new tables for scholarship system
# - Add new columns to applications table
# - Update status enum values
# - Migrate old statuses to new simplified flow

php artisan migrate
```

**What the migrations do:**
- Creates scholarship_points, scholarship_commissions tables
- Creates application_status_history table
- Adds commission_type to applications
- Updates status enum (removes waiting_to_apply, payment_pending)
- Migrates existing data to new statuses

### Step 4: Clear All Caches
```bash
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### Step 5: Optimize for Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 6: Set Permissions (if needed)
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Step 7: Bring Application Back Online
```bash
php artisan up
```

## 🧪 Post-Deployment Testing

### Test 1: Login
```bash
# Admin Panel
https://your-domain.com/admin
Email: your-admin-email
Password: your-password

# Agent Panel
https://your-domain.com/agent
Email: your-agent-email
Password: your-password
```

### Test 2: Check Branding
- ✅ Top left should say "Sky Blue Consulting"
- ✅ Colors should be sky blue (not amber)
- ✅ Global search box should appear (top right)

### Test 3: Test Application Flow
1. Create a new student + application as agent
2. Login as admin
3. Select commission type (Money or Scholarship)
4. Change status through workflow
5. Verify auto-refresh works
6. Test offer letter upload
7. Test payment receipt upload
8. Approve application
9. Check if commission/scholarship is created

### Test 4: Check Scholarships
1. Login as agent
2. Go to Scholarships page
3. Check if existing scholarships display correctly
4. Verify no icon rendering issues

## ⚠️ Potential Issues & Solutions

### Issue 1: Migration Fails
**Error:** "Data truncated for column 'status'"

**Solution:**
```bash
# Some applications might have old status values
# Run this SQL first to check:
mysql -u username -p database_name

SELECT DISTINCT status, COUNT(*) FROM applications GROUP BY status;

# If you see old statuses, update them manually:
UPDATE applications SET status = 'submitted' WHERE status IN ('draft', 'pending', 'under_review');
UPDATE applications SET status = 'applied' WHERE status = 'waiting_to_apply';
UPDATE applications SET status = 'payment_approval' WHERE status = 'payment_pending';

# Then run migration again:
php artisan migrate
```

### Issue 2: 500 Error After Deployment
**Solution:**
```bash
# Check logs
tail -f storage/logs/laravel.log

# Clear all caches again
php artisan optimize:clear

# Ensure storage is writable
chmod -R 775 storage bootstrap/cache
```

### Issue 3: CSS/JS Not Updated
**Solution:**
```bash
# Rebuild frontend assets
npm run build

# Clear browser cache
# In browser: Ctrl+Shift+R (force refresh)
```

### Issue 4: Icons Still Showing Large
**Solution:**
```bash
php artisan view:clear
# Force refresh browser (Ctrl+Shift+R)
```

## 🔙 Rollback Plan (If Something Goes Wrong)

### Option 1: Rollback Migration
```bash
php artisan migrate:rollback --step=1
```

### Option 2: Restore Database Backup
```bash
# Restore from backup
mysql -u username -p database_name < backup_YYYYMMDD_HHMMSS.sql

# Rollback code
git reset --hard HEAD~1
composer install
php artisan optimize:clear
```

### Option 3: Full Rollback
```bash
# Restore database
mysql -u username -p database_name < backup_YYYYMMDD_HHMMSS.sql

# Rollback to previous commit
git log  # Find previous commit hash
git reset --hard PREVIOUS_COMMIT_HASH
composer install
npm install
npm run build
php artisan optimize:clear
```

## 📊 Database Changes Summary

### New Tables Created:
- `degrees` - Degree types (Bachelor, Master, PhD, etc.)
- `scholarship_points` - Individual scholarship points earned
- `scholarship_commissions` - Earned scholarships
- `application_cycles` - Annual application cycles
- `admin_scholarship_inventories` - System scholarship tracking
- `application_status_history` - Complete status change audit trail

### Modified Tables:
- `applications` - Added commission_type, payment fields, status enum updated
- `programs` - Added degree_id
- `universities` - Added scholarship_requirements JSON field

### Removed Statuses (auto-migrated):
- `waiting_to_apply` → migrated to `applied`
- `payment_pending` → migrated to `payment_approval`
- `under_review`, `draft`, `pending` → migrated to `submitted`

## 📞 Support

If you encounter issues during deployment:

1. **Check logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Check environment:**
   ```bash
   php artisan about
   ```

3. **Verify database connection:**
   ```bash
   php artisan db:show
   ```

## ✅ Success Indicators

After deployment, you should see:
- ✅ Brand name: "Sky Blue Consulting"
- ✅ Sky blue color theme
- ✅ Global search working (Cmd+K)
- ✅ Application status changes working
- ✅ Offer letter upload working
- ✅ Payment receipt upload working
- ✅ Commissions being created on approval
- ✅ No icon rendering issues
- ✅ No errors in browser console

## 🎯 Quick Deployment (If No Data to Preserve)

If this is a fresh server or you don't need existing data:

```bash
# Pull code
git pull origin main

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Wipe and rebuild database
php artisan db:wipe
php artisan migrate --seed

# Clear caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Done!
php artisan up
```

---

**Deployment Time:** ~5-10 minutes
**Downtime:** ~2-5 minutes (during migration)
**Risk Level:** Medium (database changes)
**Backup:** CRITICAL - Always backup before deploying!

---

**Status:** Ready to deploy
**Last Updated:** October 3, 2025
**Commit:** Complete Application Lifecycle System

