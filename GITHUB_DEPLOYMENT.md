# GitHub Deployment Guide
## Sky Education Portal

This guide covers setting up automated deployment from GitHub for the Sky Education Portal using GitHub Actions and webhooks.

## 🎯 Overview

The GitHub deployment system provides:
- **Automated CI/CD pipeline** with GitHub Actions
- **Multi-environment support** (staging, production)
- **Security scanning** and dependency checks
- **Automated backups** and health monitoring
- **Webhook-based deployment** for instant updates
- **Rollback capabilities** and deployment notifications

## 🏗️ Deployment Architecture

```
┌─────────────────────────────────────────────────────────┐
│                    GitHub Repository                     │
│  ├── Push to main → Staging Deployment                  │
│  ├── Push to production → Production Deployment         │
│  └── Pull Request → Tests & Security Scan              │
└─────────────────────┬───────────────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────────────┐
│                 GitHub Actions                          │
│  ├── Run Tests & Code Quality Checks                   │
│  ├── Security Scanning & Dependency Review             │
│  ├── Build Assets & Optimize Code                      │
│  └── Deploy to Server via SSH                          │
└─────────────────────┬───────────────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────────────┐
│              Production Server                          │
│  ├── Automated Backup Before Deployment                │
│  ├── Pull Latest Code & Install Dependencies           │
│  ├── Run Migrations & Cache Optimization               │
│  └── Health Checks & Notification                      │
└─────────────────────────────────────────────────────────┘
```

## 🚀 Quick Setup

### 1. Create GitHub Repository

```bash
# Initialize git if not already done
git init
git add .
git commit -m "Initial commit"

# Add GitHub remote (replace with your repository URL)
git remote add origin https://github.com/yourusername/sky-portal.git
git branch -M main
git push -u origin main

# Create production branch
git checkout -b production
git push -u origin production
git checkout main
```

### 2. Configure GitHub Secrets

Go to your GitHub repository → Settings → Secrets and variables → Actions

#### Required Secrets:

**Staging Environment:**
```
STAGING_HOST=your-staging-server-ip
STAGING_USERNAME=skyapp
STAGING_SSH_KEY=your-private-ssh-key
STAGING_PORT=22
STAGING_PATH=/var/www/sky-portal
```

**Production Environment:**
```
PRODUCTION_HOST=your-production-server-ip
PRODUCTION_USERNAME=skyapp
PRODUCTION_SSH_KEY=your-private-ssh-key
PRODUCTION_PORT=22
PRODUCTION_PATH=/var/www/sky-portal
PRODUCTION_URL=https://yourdomain.com
```

**Database Credentials:**
```
DB_USERNAME=skyapp
DB_PASSWORD=your-database-password
DB_DATABASE=sky_production
```

**Optional Notifications:**
```
SLACK_WEBHOOK=your-slack-webhook-url
```

**Optional Cloud Backup:**
```
AWS_ACCESS_KEY_ID=your-aws-access-key
AWS_SECRET_ACCESS_KEY=your-aws-secret-key
AWS_REGION=us-east-1
AWS_BACKUP_BUCKET=your-backup-bucket
```

**Webhook Security:**
```
GITHUB_WEBHOOK_SECRET=your-webhook-secret
```

### 3. Server Preparation

On your server, set up SSH key authentication:

```bash
# On your local machine, generate SSH key if you don't have one
ssh-keygen -t rsa -b 4096 -C "deployment@yourdomain.com"

# Copy public key to server
ssh-copy-id skyapp@your-server-ip

# Test SSH connection
ssh skyapp@your-server-ip

# On the server, clone your repository
sudo -u skyapp git clone https://github.com/yourusername/sky-portal.git /var/www/sky-portal
cd /var/www/sky-portal

# Make deployment scripts executable
chmod +x deploy-from-github.sh
chmod +x deploy.sh
```

## 📋 Deployment Workflows

### 1. Continuous Integration (`.github/workflows/deploy.yml`)

**Triggers:**
- Push to `main` branch → Deploy to staging
- Push to `production` branch → Deploy to production
- Pull requests → Run tests only

**Process:**
1. **Test Phase:**
   - Run PHPUnit tests
   - Check code style with Laravel Pint
   - Build and verify assets

2. **Staging Deployment:**
   - Install dependencies
   - Build production assets
   - Deploy via SSH
   - Run health checks

3. **Production Deployment:**
   - Create deployment artifact
   - Deploy with backup
   - Verify deployment success
   - Send notifications

### 2. Security Scanning (`.github/workflows/security-scan.yml`)

**Features:**
- **Dependency Audit:** Checks for known vulnerabilities
- **Secret Scanning:** Detects accidentally committed secrets
- **Code Analysis:** Reviews code for security issues
- **Daily Scans:** Automated security monitoring

### 3. Automated Backups (`.github/workflows/backup.yml`)

**Schedule:**
- Daily automated backups at 1 AM UTC
- Manual backup triggering
- Multiple backup types (full, database-only, files-only)
- Cloud storage integration (AWS S3)

## 🔧 Deployment Methods

### Method 1: GitHub Actions (Recommended)

**Automatic deployment on push:**
```bash
# Deploy to staging
git push origin main

# Deploy to production
git push origin production
```

**Manual deployment trigger:**
1. Go to Actions tab in GitHub
2. Select "Deploy Sky Education Portal"
3. Click "Run workflow"
4. Choose branch and options

### Method 2: Webhook Deployment

**Setup webhook endpoint:**
```bash
# On your server, configure webhook handler
sudo cp webhook-deploy.php /var/www/html/
sudo chown www-data:www-data /var/www/html/webhook-deploy.php

# Set webhook secret in environment
echo "GITHUB_WEBHOOK_SECRET=your-secret-here" >> /var/www/sky-portal/.env
```

**Configure GitHub webhook:**
1. Go to Repository → Settings → Webhooks
2. Add webhook: `https://yourdomain.com/webhook-deploy.php`
3. Content type: `application/json`
4. Secret: Your webhook secret
5. Events: Just push events

### Method 3: Manual Deployment

**Direct deployment on server:**
```bash
# SSH to your server
ssh skyapp@your-server-ip

# Navigate to application directory
cd /var/www/sky-portal

# Deploy from specific branch
./deploy-from-github.sh main
./deploy-from-github.sh production
```

## 🔒 Security Configuration

### SSH Key Setup

```bash
# Generate deployment key
ssh-keygen -t rsa -b 4096 -f ~/.ssh/sky-portal-deploy

# Add public key to server
cat ~/.ssh/sky-portal-deploy.pub | ssh skyapp@your-server "mkdir -p ~/.ssh && cat >> ~/.ssh/authorized_keys"

# Add private key to GitHub secrets
cat ~/.ssh/sky-portal-deploy  # Copy this to STAGING_SSH_KEY and PRODUCTION_SSH_KEY
```

### Server Security

```bash
# Restrict SSH access (in /etc/ssh/sshd_config)
AllowUsers skyapp
PasswordAuthentication no
PubkeyAuthentication yes

# Restart SSH service
sudo systemctl restart ssh

# Configure firewall
sudo ufw allow from github-ip-ranges to any port 22
```

### Environment Protection

In GitHub repository settings:
1. Go to Settings → Environments
2. Create "production" environment
3. Add protection rules:
   - Required reviewers
   - Wait timer
   - Deployment branches (only `production`)

## 📊 Monitoring & Notifications

### Slack Integration

```bash
# Create Slack webhook
# Add webhook URL to SLACK_WEBHOOK secret
# Notifications will be sent for:
# - Deployment success/failure
# - Backup completion
# - Security scan results
```

### Log Monitoring

```bash
# View deployment logs
tail -f /var/log/github-deployment.log
tail -f /var/log/webhook-deployment.log

# View application logs
tail -f /var/www/sky-portal/storage/logs/laravel.log
```

### Health Monitoring

The deployment process includes automatic health checks:
- Application response verification
- Database connectivity check
- File permission validation
- Service status verification

## 🔄 Rollback Procedures

### Automatic Rollback

GitHub Actions can automatically rollback on health check failure:
```yaml
# This is already configured in deploy.yml
- name: Health check and rollback
  run: |
    if ! curl -f ${{ secrets.PRODUCTION_URL }}/health; then
      git checkout ${{ github.event.before }}
      ./deploy.sh
    fi
```

### Manual Rollback

```bash
# Via GitHub Actions
# 1. Go to Actions tab
# 2. Find successful previous deployment
# 3. Re-run that workflow

# Via command line on server
ssh skyapp@your-server-ip
cd /var/www/sky-portal

# Find previous commit
git log --oneline -10

# Rollback to specific commit
git checkout COMMIT_HASH
./deploy.sh --force
```

### Database Rollback

```bash
# Restore from backup
mysql -u root -p sky_production < /var/backups/sky-portal/database_backup_YYYYMMDD.sql
```

## 🚨 Troubleshooting

### Common Issues

**1. SSH Connection Failed**
```bash
# Test SSH connection
ssh -v skyapp@your-server-ip

# Check SSH key in GitHub secrets
# Verify server SSH configuration
```

**2. Permission Denied**
```bash
# Fix file permissions
sudo chown -R skyapp:www-data /var/www/sky-portal
sudo chmod -R 755 /var/www/sky-portal
sudo chmod -R 775 /var/www/sky-portal/storage
```

**3. Deployment Hanging**
```bash
# Check for lock files
rm -f /tmp/sky-portal-deploy.lock
rm -f /tmp/webhook-deploy.lock

# Check running processes
ps aux | grep deploy
```

**4. Health Check Failed**
```bash
# Check application logs
tail -100 /var/www/sky-portal/storage/logs/laravel.log

# Check web server status
sudo systemctl status nginx php8.2-fpm

# Test health endpoint manually
curl -v https://yourdomain.com/health
```

### Debug Mode

Enable debug logging in deployment scripts:
```bash
# Edit deploy-from-github.sh
set -x  # Add this line for verbose output

# Check deployment logs
tail -f /var/log/github-deployment.log
```

## 📚 Advanced Configuration

### Multi-Environment Setup

Create separate branches for different environments:
```bash
# Development environment
git checkout -b development
git push -u origin development

# Staging environment  
git checkout -b staging
git push -u origin staging

# Production environment
git checkout -b production
git push -u origin production
```

### Custom Deployment Hooks

Add custom deployment hooks in `deploy-from-github.sh`:
```bash
# Pre-deployment hook
pre_deploy_hook() {
    echo "Running pre-deployment tasks..."
    # Add your custom logic here
}

# Post-deployment hook
post_deploy_hook() {
    echo "Running post-deployment tasks..."
    # Add your custom logic here
}
```

### Database Migration Strategy

For zero-downtime deployments:
```bash
# In deploy-from-github.sh, modify migration strategy
php artisan migrate --force --isolated
php artisan queue:restart
```

## 📋 Checklist

### Initial Setup
- [ ] GitHub repository created and configured
- [ ] SSH keys generated and added to server
- [ ] GitHub secrets configured
- [ ] Server prepared with deployment scripts
- [ ] Webhook endpoint configured (if using webhooks)
- [ ] Slack/notification integration set up

### Before Each Deployment
- [ ] All tests passing locally
- [ ] Code reviewed and approved
- [ ] Database migrations tested
- [ ] Environment-specific configurations updated
- [ ] Backup verified and recent

### After Each Deployment
- [ ] Health checks passed
- [ ] Application functionality verified
- [ ] Performance monitoring reviewed
- [ ] Logs checked for errors
- [ ] Team notified of deployment

## 🆘 Emergency Procedures

### Immediate Rollback
```bash
# 1. Stop new deployments
# 2. Rollback application code
git checkout HEAD~1
./deploy.sh --force

# 3. Restore database if needed
mysql -u root -p sky_production < /var/backups/latest.sql

# 4. Notify team
```

### Contact Information
- **Development Team**: [email]
- **DevOps Team**: [email]  
- **Emergency Contact**: [phone]

---

**Remember**: Always test deployment procedures in staging before applying to production!
