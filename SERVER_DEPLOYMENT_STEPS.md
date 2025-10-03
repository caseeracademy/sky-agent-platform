# 🚀 Server Deployment Instructions

## ✅ Code Successfully Pushed to GitHub!

**Repository:** https://github.com/caseeracademy/sky-agent-platform  
**Latest Commit:** fdfb65a

---

## 📋 Step-by-Step Deployment to Your Server

### Step 1: Connect to Your Server
```bash
ssh your-username@your-server-ip
# Example: ssh root@your-server.com
```

### Step 2: Navigate to Your Project Directory
```bash
cd /path/to/your/project
# Example: cd /var/www/sky-agent-platform
```

### Step 3: **CRITICAL** - Backup Your Database First!
```bash
# For MySQL:
mysqldump -u your_db_user -p your_database_name > backup_$(date +%Y%m%d_%H%M%S).sql

# Example:
mysqldump -u sky_user -p sky_db > backup_20251003_163000.sql
```

### Step 4: Put Site in Maintenance Mode
```bash
php artisan down --message="Updating system, back shortly"
```

### Step 5: Pull Latest Code from GitHub
```bash
git pull origin main
```

### Step 6: Install/Update Dependencies
```bash
composer install --optimize-autoloader --no-dev
```

### Step 7: Run Database Migrations
```bash
php artisan migrate --force
```

**Migrations that will run:**
- `2025_10_03_141907_create_system_settings_table` - Company settings
- `2025_10_03_141908_add_profile_fields_to_users_table` - Agent profiles
- `2025_10_03_152054_remove_commission_fields_from_system_settings` - Cleanup
- `2025_10_03_161436_add_created_by_admin_to_students_and_applications` - Track creators
- `2025_10_03_163646_make_nationality_nullable_in_students_table` - Fix required fields

### Step 8: Clear All Caches
```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 9: Bring Site Back Online
```bash
php artisan up
```

### Step 10: Test the Deployment
Visit your site and verify:
- ✅ Admin can create students and assign to agents
- ✅ Student creation includes all countries (Somalia, etc.)
- ✅ No duplicate application number errors
- ✅ Settings pages load correctly
- ✅ Commission type change buttons work

---

## 🔧 If Something Goes Wrong

### Rollback Database:
```bash
# Restore from backup
mysql -u your_db_user -p your_database_name < backup_20251003_163000.sql
```

### Rollback Code:
```bash
git reset --hard 77dd268  # Previous commit
php artisan optimize:clear
```

### Check Logs:
```bash
tail -f storage/logs/laravel.log
```

---

## 📝 What Changed in This Deployment

### New Features:
1. **Admin Student Creation** - Create students and assign to any agent
2. **Commission Type Change** - Fix mistakes by switching money ↔ scholarship
3. **Degree Selection** - 3-step flow (University → Degree → Program)
4. **Create Application Later** - Button on student details if no application
5. **Settings Pages** - Company info and agent profiles
6. **58 Countries** - Complete list including Somalia/Somali

### Bug Fixes:
1. **CRITICAL:** Duplicate application number race condition
2. **Fixed:** Required field errors (nationality, etc.)
3. **Fixed:** Navigation organization

### Database Changes:
- 5 new migrations (all safe, no data loss)
- New tables: `system_settings`
- New columns: `created_by_user_id`, profile fields
- Modified columns: Made some fields nullable

---

## ⚡ Quick Deployment (One-Liner)

```bash
php artisan down && \
git pull origin main && \
composer install --optimize-autoloader --no-dev && \
php artisan migrate --force && \
php artisan optimize:clear && \
php artisan config:cache && \
php artisan route:cache && \
php artisan up
```

---

## ✅ Post-Deployment Verification

1. Login as admin: `http://your-domain.com/admin`
2. Try creating a student → Select agent → Should work!
3. Check countries list → Should see Somalia
4. View an application → Commission type change buttons should appear
5. Check Settings → System Settings page should load

---

## 📞 Support

If you encounter any issues during deployment:
1. Check `storage/logs/laravel.log` for errors
2. Ensure database backup was created
3. Verify all migrations ran successfully
4. Clear browser cache if UI looks strange

---

## 🎉 Expected Results After Deployment

- ✅ No more duplicate application number errors
- ✅ Admin can create students for agents
- ✅ All countries available in dropdowns
- ✅ Commission types can be changed
- ✅ Settings pages functional
- ✅ Better organized navigation

**Deployment time: ~2-3 minutes**

Good luck! 🚀

