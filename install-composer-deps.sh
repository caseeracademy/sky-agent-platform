#!/bin/bash

# =============================================================================
# Composer Dependencies Installation Script
# =============================================================================
# 
# This script installs Composer dependencies for the Sky Education Portal
# with comprehensive error handling and multiple fallback methods.
# 
# Usage: 
#   sudo ./install-composer-deps.sh
# =============================================================================

# Configuration
APP_USER="skyapp"
DEPLOY_PATH="/var/www/sky-agent-platform"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

print_status() { echo -e "${BLUE}[INFO]${NC} $1"; }
print_success() { echo -e "${GREEN}[SUCCESS]${NC} $1"; }
print_warning() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
print_error() { echo -e "${RED}[ERROR]${NC} $1"; }

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    print_error "Please run this script as root (use sudo)"
    exit 1
fi

# Check if application directory exists
if [ ! -d "$DEPLOY_PATH" ]; then
    print_error "Application directory not found: $DEPLOY_PATH"
    exit 1
fi

# Change to application directory
cd $DEPLOY_PATH

print_status "Installing Composer dependencies in: $(pwd)"

# Check if composer.json exists
if [ ! -f "composer.json" ]; then
    print_error "composer.json not found in $DEPLOY_PATH"
    print_status "Directory contents:"
    ls -la
    exit 1
fi

# Check Composer installation
if ! command -v composer &> /dev/null; then
    print_error "Composer not found. Please install Composer first."
    exit 1
fi

print_status "Composer version: $(composer --version | head -n1)"

# Method 1: Try with application user
print_status "Method 1: Installing with application user ($APP_USER)..."
if sudo -u $APP_USER composer install --no-dev --optimize-autoloader --no-interaction; then
    print_success "Composer dependencies installed successfully with application user"
    exit 0
fi

print_warning "Method 1 failed, trying Method 2..."

# Method 2: Try with root user
print_status "Method 2: Installing with root user..."
if composer install --no-dev --optimize-autoloader --no-interaction; then
    print_success "Composer dependencies installed with root user"
    print_status "Fixing file ownership..."
    chown -R $APP_USER:www-data vendor composer.lock
    print_success "File ownership fixed"
    exit 0
fi

print_warning "Method 2 failed, trying Method 3..."

# Method 3: Try with verbose output for debugging
print_status "Method 3: Installing with verbose output..."
if composer install --no-dev --optimize-autoloader --no-interaction --verbose; then
    print_success "Composer dependencies installed with verbose output"
    chown -R $APP_USER:www-data vendor composer.lock
    exit 0
fi

print_error "All installation methods failed"
print_status "Troubleshooting information:"
print_status "1. Check internet connectivity: ping -c 1 packagist.org"
print_status "2. Check PHP memory limit: php -r \"echo ini_get('memory_limit');\""
print_status "3. Check disk space: df -h"
print_status "4. Check Composer configuration: composer config --list"
print_status "5. Try manual installation: cd $DEPLOY_PATH && composer install --verbose"

exit 1
