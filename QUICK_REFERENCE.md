# 🚀 QUICK REFERENCE GUIDE

## 📦 What You Have Now

**Repository:** https://github.com/caseeracademy/sky-agent-platform  
**Latest Commit:** Complete Application Lifecycle System + Cleanup Command  

---

## 🛠️ LOCAL DEVELOPMENT

### Reset Database & Create Admin:
```bash
php artisan db:clean-and-admin
```

This will:
- Ask for confirmation
- Wipe database
- Run migrations
- Seed universities
- Let you create custom admin (name, email, password)
- Optionally create agent account
- Clear all caches

**Result:** Fresh start with your chosen admin credentials!

### Quick Reset (Skip Prompts):
```bash
php artisan db:wipe
php artisan migrate --seed
```

**Default users created:**
- `superadmin@sky.com` / `password`
- `agent.owner@sky.com` / `password`

---

## 🌐 SERVER DEPLOYMENT

### First Time Setup (Fresh Server):
```bash
# 1. Clone repo
git clone https://github.com/caseeracademy/sky-agent-platform.git
cd sky-agent-platform

# 2. Install dependencies
composer install --optimize-autoloader --no-dev
npm install
npm run build

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# Edit .env with your database credentials
nano .env

# 4. Setup database
php artisan migrate --seed

# 5. Create admin
php artisan db:clean-and-admin --force

# 6. Set permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 7. Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Update Existing Server:
```bash
# ⚠️  BACKUP FIRST!
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql

# Deploy
php artisan down
git pull origin main
composer install --optimize-autoloader --no-dev
npm install && npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan up
```

---

## 🎯 APPLICATION WORKFLOW

### 9 Statuses (Simplified):
1. `needs_review` - Admin selects Money/Scholarship
2. `submitted` - Ready for processing
3. `additional_documents_needed` - Agent uploads docs
4. `applied` - Sent to university
5. `offer_received` - Offer letter uploaded
6. `payment_approval` - Payment receipt uploaded
7. `ready_for_approval` - Payment verified
8. `approved` - ✅ FINAL (commission awarded)
9. `rejected` - ❌ FINAL

### Admin Actions:
- Select commission type → `submitted`
- Request documents → `additional_documents_needed`
- Apply to university → `applied`
- Upload offer letter → `offer_received`
- Verify payment → `ready_for_approval`
- Final approve → `approved` (commission created!)

### Agent Actions:
- Upload documents → `submitted`
- Upload payment receipt (from `offer_received`) → `payment_approval`

---

## 👥 DEFAULT USERS

**After running seeders:**
- Admin: `superadmin@sky.com` / `password`
- Agent: `agent.owner@sky.com` / `password`

**After `db:clean-and-admin`:**
- Admin: Your custom email / Your password
- Agent: `agent@sky.com` / `password` (if created)

---

## 🔍 FEATURES

### Branding:
- Name: "Sky Blue Consulting"
- Colors: Sky blue theme
- No icon issues (custom CSS)

### Search:
- Press `Cmd+K` or `Ctrl+K`
- Search students by name, email, passport
- Works from any page

### Commission System:
- Money commissions → Agent wallet
- Scholarship points → Free applications
- Auto-created on approval
- Progress tracking

### Documents:
- Offer letters
- Payment receipts
- Student documents
- All downloadable

---

## 🐛 TROUBLESHOOTING

### Database issues:
```bash
php artisan db:show        # Check connection
php artisan migrate:status # Check migrations
```

### Cache issues:
```bash
php artisan optimize:clear
```

### Permission issues:
```bash
chmod -R 775 storage bootstrap/cache
```

### View not updating:
```bash
npm run build
php artisan view:clear
# Force refresh browser: Ctrl+Shift+R
```

---

## 📞 QUICK COMMANDS

```bash
# Clean database & create admin (local only!)
php artisan db:clean-and-admin

# Check what's in database
php artisan tinker
>>> User::count()
>>> Application::count()

# View logs
tail -f storage/logs/laravel.log

# Run tests (when available)
php artisan test

# Code formatting
vendor/bin/pint

# Clear everything
php artisan optimize:clear
```

---

## 🎉 YOU'RE READY!

**Local:** http://sky.test/admin  
**Server:** https://your-domain.com/admin  

**All code is on GitHub and ready to deploy!**

