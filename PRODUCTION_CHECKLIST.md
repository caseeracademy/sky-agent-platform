# Production Deployment Checklist
## Sky Education Portal

This comprehensive checklist ensures your Sky Education Portal is production-ready and deployed securely.

## Pre-Deployment Checklist

### ✅ Development Environment
- [ ] All tests passing (`php artisan test`)
- [ ] Code formatted with Pint (`vendor/bin/pint`)
- [ ] No uncommitted changes in Git
- [ ] Environment-specific configurations separated
- [ ] Database migrations tested and documented
- [ ] Application tested on staging environment

### ✅ Security Configuration
- [ ] Strong `APP_KEY` generated
- [ ] `APP_DEBUG=false` in production
- [ ] Database credentials secured with strong passwords
- [ ] Default admin credentials will be changed post-deployment
- [ ] File permissions properly configured (755 for directories, 644 for files)
- [ ] Sensitive files not publicly accessible (`.env`, `storage/`)
- [ ] Web server security headers configured
- [ ] SSL certificate ready for installation

### ✅ Server Requirements
- [ ] PHP 8.2+ installed with required extensions
- [ ] Web server (Nginx/Apache) configured
- [ ] Database server (MySQL/PostgreSQL) ready
- [ ] Redis installed for caching (recommended)
- [ ] Node.js 18+ for asset compilation
- [ ] Composer installed
- [ ] Git installed for deployment

### ✅ Database Preparation
- [ ] Production database created
- [ ] Database user with limited privileges created
- [ ] Database connection tested
- [ ] Backup strategy planned
- [ ] Migration strategy confirmed

### ✅ Performance Optimization
- [ ] Asset optimization configured in Vite
- [ ] OPcache enabled and configured
- [ ] Database query optimization reviewed
- [ ] Caching strategy implemented (Redis recommended)
- [ ] CDN configuration planned (if applicable)

## Deployment Process

### Step 1: Server Setup
```bash
# Run the server setup script (Ubuntu/Debian)
sudo ./server-setup.sh

# Or manually install requirements:
# - PHP 8.2 with extensions
# - Nginx/Apache
# - MySQL/PostgreSQL
# - Redis
# - Node.js
# - Composer
```

### Step 2: Application Deployment
```bash
# Clone or upload application
git clone https://github.com/yourusername/sky-portal.git /var/www/sky-portal
cd /var/www/sky-portal

# Run automated deployment
sudo -u skyapp ./deploy.sh

# Or follow manual deployment steps in PRODUCTION_DEPLOYMENT.md
```

### Step 3: Environment Configuration
```bash
# Copy and configure environment file
cp env.production.example .env

# Edit .env with production values:
# - APP_URL
# - Database credentials
# - Mail configuration
# - SSL settings
```

### Step 4: Database Setup
```bash
# Run database setup script
mysql -u root -p < database-production-setup.sql

# Run Laravel migrations
php artisan migrate --force

# Seed initial data
php artisan db:seed --class=UserSeeder --force
```

### Step 5: Web Server Configuration
```bash
# Copy Nginx configuration
sudo cp nginx.production.conf /etc/nginx/sites-available/sky-portal
sudo ln -s /etc/nginx/sites-available/sky-portal /etc/nginx/sites-enabled/

# Update server_name in configuration
sudo nano /etc/nginx/sites-available/sky-portal

# Test and reload Nginx
sudo nginx -t
sudo systemctl reload nginx
```

### Step 6: SSL Certificate
```bash
# Install SSL certificate with Let's Encrypt
sudo certbot --nginx -d yourdomain.com
```

### Step 7: Monitoring Setup
```bash
# Run monitoring setup script
sudo ./monitoring-setup.sh
```

## Post-Deployment Verification

### ✅ Application Testing
- [ ] Home page loads correctly
- [ ] Admin panel accessible at `/admin`
- [ ] Agent panel accessible at `/agent`
- [ ] Super admin login works (`superadmin@sky.com` / `password`)
- [ ] Database connectivity confirmed
- [ ] File uploads working
- [ ] Email functionality tested
- [ ] Asset files loading (CSS, JS, images)

### ✅ Security Verification
- [ ] HTTPS redirects working
- [ ] Security headers present (check with security scanners)
- [ ] Sensitive files not accessible (test `.env`, `/storage/logs/`)
- [ ] Admin credentials changed from defaults
- [ ] Database access restricted to application user
- [ ] Firewall rules configured
- [ ] Fail2Ban active and configured

### ✅ Performance Verification
- [ ] Page load times acceptable
- [ ] Database queries optimized
- [ ] Caching working (check response headers)
- [ ] Asset compression enabled
- [ ] OPcache functioning

### ✅ Monitoring Setup
- [ ] System monitoring active
- [ ] Application logs being written
- [ ] Log rotation configured
- [ ] Health checks responding
- [ ] Backup monitoring active
- [ ] Security monitoring active
- [ ] Alert email addresses configured

## Ongoing Maintenance

### Daily Tasks
- [ ] Monitor application logs for errors
- [ ] Check system resource usage
- [ ] Verify backup completion
- [ ] Review security alerts

### Weekly Tasks
- [ ] Review monitoring dashboard
- [ ] Check SSL certificate status
- [ ] Analyze web server logs
- [ ] Review database performance

### Monthly Tasks
- [ ] Update system packages
- [ ] Review and rotate logs
- [ ] Test backup restoration
- [ ] Security audit
- [ ] Performance optimization review

## Emergency Procedures

### Application Down
1. Check system services status
2. Review error logs
3. Check disk space and memory
4. Restart services if needed
5. Rollback if necessary

### Database Issues
1. Check database connectivity
2. Review MySQL/PostgreSQL logs
3. Check disk space
4. Restart database service
5. Restore from backup if needed

### Security Breach
1. Isolate affected systems
2. Review access logs
3. Change all passwords
4. Apply security patches
5. Notify stakeholders

## Rollback Procedure

### Quick Rollback
```bash
# Revert to previous Git commit
git log --oneline -10
git checkout [previous-commit-hash]
./deploy.sh --skip-migrate --skip-backup

# Or restore from backup
mysql -u root -p sky_production < /backups/backup_latest.sql
```

### Full Rollback
1. Stop web server
2. Restore database from backup
3. Restore application files
4. Restore configuration files
5. Start services
6. Test functionality

## Troubleshooting

### Common Issues
- **Permission errors**: Check file ownership and permissions
- **Database connection**: Verify credentials and network access
- **Asset loading**: Ensure build process completed successfully
- **Session issues**: Check session configuration and storage
- **Email not working**: Verify SMTP configuration and credentials

### Log Locations
- **Application**: `/var/www/sky-portal/storage/logs/laravel.log`
- **Web Server**: `/var/log/nginx/sky-portal.*.log`
- **System**: `/var/log/syslog`
- **Database**: `/var/log/mysql/error.log`
- **Security**: `/var/log/fail2ban.log`

### Useful Commands
```bash
# Check service status
sudo systemctl status nginx php8.2-fpm mysql redis-server

# View recent logs
sudo tail -f /var/www/sky-portal/storage/logs/laravel.log

# Check disk usage
df -h

# Check memory usage
free -h

# Check running processes
htop

# Test database connection
php artisan migrate:status

# Clear application caches
php artisan optimize:clear
```

## Support Contacts

- **Technical Lead**: [email]
- **System Administrator**: [email]
- **Security Team**: [email]
- **Emergency Contact**: [phone]

## Documentation References

- [PRODUCTION_DEPLOYMENT.md](PRODUCTION_DEPLOYMENT.md) - Detailed deployment guide
- [ARCHITECTURE.md](ARCHITECTURE.md) - Application architecture
- [TESTING_STRATEGY.md](TESTING_STRATEGY.md) - Testing approach
- [CODING_STANDARDS.md](CODING_STANDARDS.md) - Code standards

---

**Remember**: Always test deployment procedures in a staging environment before applying to production!
