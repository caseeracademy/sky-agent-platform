#!/bin/bash

# =============================================================================
# Sky Education Portal - GitHub Deployment Script
# =============================================================================
# 
# This script handles deployment from GitHub repository to production server.
# It can be triggered by GitHub webhooks or run manually.
# 
# Usage: 
#   ./deploy-from-github.sh [branch] [--force]
# 
# Examples:
#   ./deploy-from-github.sh main
#   ./deploy-from-github.sh production --force
# 
# =============================================================================

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_status() { echo -e "${BLUE}[INFO]${NC} $1"; }
print_success() { echo -e "${GREEN}[SUCCESS]${NC} $1"; }
print_warning() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
print_error() { echo -e "${RED}[ERROR]${NC} $1"; }

# Configuration
REPO_URL="https://github.com/yourusername/sky-portal.git"  # Update this
APP_PATH="/var/www/sky-portal"
BACKUP_PATH="/var/backups/sky-portal"
LOG_FILE="/var/log/github-deployment.log"
LOCK_FILE="/tmp/sky-portal-deploy.lock"

# Default values
BRANCH="${1:-main}"
FORCE_DEPLOY="${2:-}"

# Function to log messages
log_message() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" | tee -a "$LOG_FILE"
}

# Function to send notifications (customize as needed)
send_notification() {
    local status="$1"
    local message="$2"
    
    log_message "NOTIFICATION: $status - $message"
    
    # Add your notification logic here (Slack, Discord, email, etc.)
    # Example for Slack:
    # curl -X POST -H 'Content-type: application/json' \
    #   --data "{\"text\":\"Deployment $status: $message\"}" \
    #   "$SLACK_WEBHOOK_URL"
}

# Function to cleanup on exit
cleanup() {
    rm -f "$LOCK_FILE"
}
trap cleanup EXIT

# Check if deployment is already running
if [ -f "$LOCK_FILE" ]; then
    print_error "Deployment already in progress (lock file exists)"
    exit 1
fi

# Create lock file
echo $$ > "$LOCK_FILE"

print_status "Starting GitHub deployment for Sky Education Portal..."
log_message "Starting deployment from GitHub - Branch: $BRANCH"

# =============================================================================
# PRE-DEPLOYMENT CHECKS
# =============================================================================

print_status "Running pre-deployment checks..."

# Check if we're running as the correct user
if [ "$(whoami)" != "skyapp" ] && [ "$EUID" -ne 0 ]; then
    print_error "This script should be run as 'skyapp' user or root"
    exit 1
fi

# Check if application directory exists
if [ ! -d "$APP_PATH" ]; then
    print_error "Application directory not found: $APP_PATH"
    exit 1
fi

# Check if git is available
if ! command -v git >/dev/null 2>&1; then
    print_error "Git is not installed"
    exit 1
fi

# Check if we're in a git repository
cd "$APP_PATH"
if [ ! -d ".git" ]; then
    print_error "Application directory is not a git repository"
    exit 1
fi

print_success "Pre-deployment checks passed"

# =============================================================================
# BACKUP CURRENT VERSION
# =============================================================================

print_status "Creating backup of current version..."

# Create backup directory
mkdir -p "$BACKUP_PATH"

# Get current commit hash
CURRENT_COMMIT=$(git rev-parse HEAD)
BACKUP_NAME="backup-$(date +%Y%m%d-%H%M%S)-${CURRENT_COMMIT:0:8}"

# Create backup
tar -czf "$BACKUP_PATH/$BACKUP_NAME.tar.gz" \
    --exclude='.git' \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='storage/logs/*' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    .

log_message "Backup created: $BACKUP_PATH/$BACKUP_NAME.tar.gz"
print_success "Backup created successfully"

# =============================================================================
# FETCH LATEST CHANGES
# =============================================================================

print_status "Fetching latest changes from GitHub..."

# Stash any local changes
if ! git diff --quiet; then
    print_warning "Local changes detected, stashing them"
    git stash push -m "Auto-stash before deployment $(date)"
fi

# Fetch latest changes
git fetch origin

# Check if branch exists
if ! git show-ref --verify --quiet "refs/remotes/origin/$BRANCH"; then
    print_error "Branch '$BRANCH' does not exist on remote"
    exit 1
fi

# Get the commit hash we're about to deploy
NEW_COMMIT=$(git rev-parse "origin/$BRANCH")
log_message "Deploying commit: $NEW_COMMIT"

# Check if we're already on the latest commit
if [ "$CURRENT_COMMIT" = "$NEW_COMMIT" ] && [ "$FORCE_DEPLOY" != "--force" ]; then
    print_warning "Already on the latest commit. Use --force to redeploy."
    exit 0
fi

# Checkout the specified branch
git checkout "$BRANCH"
git reset --hard "origin/$BRANCH"

print_success "Code updated to latest version"
send_notification "INFO" "Deploying commit ${NEW_COMMIT:0:8} to $BRANCH"

# =============================================================================
# INSTALL DEPENDENCIES
# =============================================================================

print_status "Installing/updating dependencies..."

# Install Composer dependencies
if [ -f "composer.json" ]; then
    print_status "Installing Composer dependencies..."
    composer install --no-dev --optimize-autoloader --no-interaction
fi

# Install NPM dependencies and build assets
if [ -f "package.json" ]; then
    print_status "Installing NPM dependencies and building assets..."
    npm ci --production
    npm run build
fi

print_success "Dependencies installed successfully"

# =============================================================================
# LARAVEL APPLICATION SETUP
# =============================================================================

print_status "Configuring Laravel application..."

# Ensure .env file exists
if [ ! -f ".env" ]; then
    print_error ".env file not found. Please create it before deployment."
    exit 1
fi

# Run database migrations
print_status "Running database migrations..."
php artisan migrate --force

# Clear and cache configuration
print_status "Optimizing application..."
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan route:cache
php artisan view:clear
php artisan view:cache

# Cache events and optimize
php artisan event:cache
php artisan optimize

# Restart queue workers if they exist
if pgrep -f "artisan queue:work" > /dev/null; then
    print_status "Restarting queue workers..."
    php artisan queue:restart
fi

print_success "Laravel application configured"

# =============================================================================
# FILE PERMISSIONS AND OWNERSHIP
# =============================================================================

print_status "Setting file permissions..."

# Set ownership
if [ "$EUID" -eq 0 ]; then
    chown -R skyapp:www-data "$APP_PATH"
    chown -R skyapp:www-data "$APP_PATH/storage"
    chown -R skyapp:www-data "$APP_PATH/bootstrap/cache"
fi

# Set permissions
chmod -R 755 "$APP_PATH"
chmod -R 775 "$APP_PATH/storage"
chmod -R 775 "$APP_PATH/bootstrap/cache"

print_success "File permissions set"

# =============================================================================
# RESTART SERVICES
# =============================================================================

print_status "Restarting services..."

# Restart PHP-FPM
if [ "$EUID" -eq 0 ] || sudo -n true 2>/dev/null; then
    sudo systemctl reload php8.2-fpm
    sudo systemctl reload nginx
    print_success "Services restarted"
else
    print_warning "Cannot restart services (no sudo access)"
fi

# =============================================================================
# HEALTH CHECK
# =============================================================================

print_status "Running health checks..."

# Wait a moment for services to restart
sleep 5

# Check if application is responding
APP_URL=$(php -r "echo config('app.url');")
HEALTH_URL="$APP_URL/health"

if curl -f -s "$HEALTH_URL" >/dev/null 2>&1; then
    print_success "Application health check passed"
else
    print_error "Application health check failed"
    send_notification "ERROR" "Health check failed after deployment"
    
    # Rollback option
    if [ "$FORCE_DEPLOY" != "--force" ]; then
        print_warning "Would you like to rollback? (y/N)"
        read -r -n 1 response
        if [[ $response =~ ^[Yy]$ ]]; then
            print_status "Rolling back to previous version..."
            git checkout "$CURRENT_COMMIT"
            ./deploy.sh --skip-backup
            exit 1
        fi
    fi
fi

# =============================================================================
# CLEANUP
# =============================================================================

print_status "Cleaning up..."

# Remove old backups (keep last 10)
find "$BACKUP_PATH" -name "backup-*.tar.gz" -type f | sort -r | tail -n +11 | xargs rm -f

# Clear temporary files
php artisan cache:clear

print_success "Cleanup completed"

# =============================================================================
# DEPLOYMENT COMPLETE
# =============================================================================

DEPLOY_TIME=$(date '+%Y-%m-%d %H:%M:%S')
log_message "Deployment completed successfully at $DEPLOY_TIME"

print_success "🎉 Deployment completed successfully!"
print_status "Deployed commit: ${NEW_COMMIT:0:8}"
print_status "Branch: $BRANCH"
print_status "Time: $DEPLOY_TIME"

send_notification "SUCCESS" "Deployment completed successfully - Commit: ${NEW_COMMIT:0:8}"

# =============================================================================
# POST-DEPLOYMENT SUMMARY
# =============================================================================

echo ""
echo "==============================================================================="
echo "DEPLOYMENT SUMMARY"
echo "==============================================================================="
echo "Application URL: $APP_URL"
echo "Deployed Branch: $BRANCH"
echo "Commit Hash: $NEW_COMMIT"
echo "Backup Created: $BACKUP_PATH/$BACKUP_NAME.tar.gz"
echo "Log File: $LOG_FILE"
echo ""
echo "NEXT STEPS:"
echo "1. Test the application thoroughly"
echo "2. Monitor application logs for any errors"
echo "3. Check monitoring dashboard"
echo ""
print_success "Deployment completed successfully! 🚀"
