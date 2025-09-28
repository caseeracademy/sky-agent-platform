#!/bin/bash

# =============================================================================
# Sky Education Portal - Installation Status Checker
# =============================================================================
# 
# This script checks what components are already installed and shows
# the current installation status of the Sky Education Portal.
# 
# Usage: 
#   sudo ./check-installation-status.sh
# =============================================================================

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

print_status() { echo -e "${BLUE}[INFO]${NC} $1"; }
print_success() { echo -e "${GREEN}[✓]${NC} $1"; }
print_warning() { echo -e "${YELLOW}[!]${NC} $1"; }
print_error() { echo -e "${RED}[✗]${NC} $1"; }
print_header() { echo -e "${CYAN}[INFO]${NC} $1"; }

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    print_error "Please run this script as root (use sudo)"
    exit 1
fi

echo ""
echo "🔍 ==============================================================================="
echo "🔍                    SKY EDUCATION PORTAL - INSTALLATION STATUS"
echo "🔍 ==============================================================================="
echo ""

# Check system packages
print_header "System Packages"
if command -v curl &> /dev/null; then
    print_success "curl is installed"
else
    print_error "curl is not installed"
fi

if command -v wget &> /dev/null; then
    print_success "wget is installed"
else
    print_error "wget is not installed"
fi

if command -v git &> /dev/null; then
    print_success "git is installed"
else
    print_error "git is not installed"
fi

# Check PHP
print_header "PHP Installation"
if command -v php &> /dev/null; then
    PHP_VERSION=$(php -v | head -n1 | awk '{print $2}')
    print_success "PHP is installed (Version: $PHP_VERSION)"
    
    # Check PHP extensions
    if php -m | grep -q "mysql"; then
        print_success "PHP MySQL extension is installed"
    else
        print_error "PHP MySQL extension is not installed"
    fi
    
    if php -m | grep -q "curl"; then
        print_success "PHP cURL extension is installed"
    else
        print_error "PHP cURL extension is not installed"
    fi
else
    print_error "PHP is not installed"
fi

# Check Composer
print_header "Composer"
if command -v composer &> /dev/null; then
    COMPOSER_VERSION=$(composer --version | awk '{print $3}')
    print_success "Composer is installed (Version: $COMPOSER_VERSION)"
else
    print_error "Composer is not installed"
fi

# Check Node.js
print_header "Node.js"
if command -v node &> /dev/null; then
    NODE_VERSION=$(node --version)
    print_success "Node.js is installed (Version: $NODE_VERSION)"
    
    if command -v npm &> /dev/null; then
        NPM_VERSION=$(npm --version)
        print_success "NPM is installed (Version: $NPM_VERSION)"
    else
        print_error "NPM is not installed"
    fi
else
    print_error "Node.js is not installed"
fi

# Check MySQL
print_header "MySQL Database"
if command -v mysql &> /dev/null; then
    MYSQL_VERSION=$(mysql --version | awk '{print $3}' | cut -d',' -f1)
    print_success "MySQL is installed (Version: $MYSQL_VERSION)"
    
    if systemctl is-active --quiet mysql; then
        print_success "MySQL service is running"
    else
        print_warning "MySQL service is not running"
    fi
else
    print_error "MySQL is not installed"
fi

# Check Nginx
print_header "Nginx Web Server"
if command -v nginx &> /dev/null; then
    NGINX_VERSION=$(nginx -v 2>&1 | awk '{print $3}' | cut -d'/' -f2)
    print_success "Nginx is installed (Version: $NGINX_VERSION)"
    
    if systemctl is-active --quiet nginx; then
        print_success "Nginx service is running"
    else
        print_warning "Nginx service is not running"
    fi
else
    print_error "Nginx is not installed"
fi

# Check application user
print_header "Application User"
if id "skyapp" &>/dev/null; then
    print_success "Application user 'skyapp' exists"
    USER_UID=$(id -u skyapp)
    print_status "User UID: $USER_UID"
else
    print_error "Application user 'skyapp' does not exist"
fi

# Check application directory
print_header "Application Directory"
APP_PATH="/var/www/sky-agent-platform"
if [ -d "$APP_PATH" ]; then
    print_success "Application directory exists: $APP_PATH"
    
    if [ -f "$APP_PATH/composer.json" ]; then
        print_success "Laravel application files found"
    else
        print_warning "Application directory exists but no Laravel files found"
    fi
    
    if [ -f "$APP_PATH/.env" ]; then
        print_success "Environment file (.env) exists"
    else
        print_warning "Environment file (.env) not found"
    fi
else
    print_error "Application directory does not exist: $APP_PATH"
fi

# Check database connection
print_header "Database Connection"
if command -v mysql &> /dev/null && systemctl is-active --quiet mysql; then
    if mysql -u root -e "SELECT 1;" 2>/dev/null; then
        print_success "MySQL root connection (no password) works"
    elif mysql -u root -pSkySecure2024! -e "SELECT 1;" 2>/dev/null; then
        print_success "MySQL root connection (with password) works"
    else
        print_error "Cannot connect to MySQL"
    fi
    
    # Check if database exists
    if mysql -u root -e "USE sky_production;" 2>/dev/null; then
        print_success "Database 'sky_production' exists"
    else
        print_warning "Database 'sky_production' does not exist"
    fi
else
    print_error "Cannot check database connection (MySQL not running)"
fi

# Check services
print_header "Service Status"
services=("mysql" "nginx" "php8.3-fpm")
for service in "${services[@]}"; do
    if systemctl is-active --quiet $service; then
        print_success "$service service is running"
    else
        print_warning "$service service is not running"
    fi
done

# Check ports
print_header "Port Status"
if netstat -tlnp | grep -q ":80 "; then
    print_success "Port 80 is in use (likely Nginx)"
else
    print_warning "Port 80 is not in use"
fi

if netstat -tlnp | grep -q ":3306 "; then
    print_success "Port 3306 is in use (likely MySQL)"
else
    print_warning "Port 3306 is not in use"
fi

# Summary
echo ""
echo "📊 ==============================================================================="
echo "📊                                SUMMARY"
echo "📊 ==============================================================================="

# Count installed components
TOTAL_CHECKS=0
PASSED_CHECKS=0

# System packages
if command -v curl &> /dev/null; then ((PASSED_CHECKS++)); fi; ((TOTAL_CHECKS++))
if command -v wget &> /dev/null; then ((PASSED_CHECKS++)); fi; ((TOTAL_CHECKS++))
if command -v git &> /dev/null; then ((PASSED_CHECKS++)); fi; ((TOTAL_CHECKS++))

# PHP
if command -v php &> /dev/null; then ((PASSED_CHECKS++)); fi; ((TOTAL_CHECKS++))

# Composer
if command -v composer &> /dev/null; then ((PASSED_CHECKS++)); fi; ((TOTAL_CHECKS++))

# Node.js
if command -v node &> /dev/null; then ((PASSED_CHECKS++)); fi; ((TOTAL_CHECKS++))

# MySQL
if command -v mysql &> /dev/null; then ((PASSED_CHECKS++)); fi; ((TOTAL_CHECKS++))

# Nginx
if command -v nginx &> /dev/null; then ((PASSED_CHECKS++)); fi; ((TOTAL_CHECKS++))

# Application
if [ -d "$APP_PATH" ]; then ((PASSED_CHECKS++)); fi; ((TOTAL_CHECKS++))

PERCENTAGE=$((PASSED_CHECKS * 100 / TOTAL_CHECKS))

echo ""
print_status "Installation Progress: $PASSED_CHECKS/$TOTAL_CHECKS components installed ($PERCENTAGE%)"

if [ $PERCENTAGE -eq 100 ]; then
    print_success "🎉 Installation appears to be complete!"
elif [ $PERCENTAGE -ge 75 ]; then
    print_warning "⚠️  Installation is mostly complete, but some components are missing"
elif [ $PERCENTAGE -ge 50 ]; then
    print_warning "⚠️  Installation is partially complete"
else
    print_error "❌ Installation is incomplete or not started"
fi

echo ""
print_status "For detailed installation logs, check: /var/log/sky-installer.log"
print_status "To continue installation, run: sudo ./sky-one-click-installer-v2.sh"
echo ""
