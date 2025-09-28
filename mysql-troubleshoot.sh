#!/bin/bash

# =============================================================================
# MySQL Troubleshooting Script for Sky Education Portal
# =============================================================================
# 
# This script provides comprehensive MySQL troubleshooting and recovery options
# for the Sky Education Portal installation process.
# 
# Usage: 
#   sudo ./mysql-troubleshoot.sh
# 
# Features:
# - Multiple MySQL connection methods
# - Password reset capabilities
# - Service status checking
# - Configuration validation
# - Recovery procedures
# =============================================================================

set -e

# Configuration
DB_PASSWORD="SkySecure2024!"
DB_NAME="sky_production"
DB_USER="skyapp"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
NC='\033[0m' # No Color

print_status() { echo -e "${BLUE}[INFO]${NC} $1"; }
print_success() { echo -e "${GREEN}[SUCCESS]${NC} $1"; }
print_warning() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
print_error() { echo -e "${RED}[ERROR]${NC} $1"; }
print_debug() { echo -e "${PURPLE}[DEBUG]${NC} $1"; }

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    print_error "Please run this script as root (use sudo)"
    exit 1
fi

# Check MySQL service status
check_mysql_service() {
    print_status "Checking MySQL service status..."
    
    if systemctl is-active --quiet mysql; then
        print_success "MySQL service is running"
        return 0
    else
        print_warning "MySQL service is not running. Attempting to start..."
        systemctl start mysql
        sleep 3
        
        if systemctl is-active --quiet mysql; then
            print_success "MySQL service started successfully"
            return 0
        else
            print_error "Failed to start MySQL service"
            return 1
        fi
    fi
}

# Test MySQL connection with multiple methods
test_mysql_connections() {
    print_status "Testing MySQL connections..."
    
    local connection_found=false
    
    # Method 1: Try with password
    print_debug "Testing connection with password..."
    if mysql -u root -p$DB_PASSWORD -e "SELECT 1;" 2>/dev/null; then
        print_success "MySQL connection successful with password"
        connection_found=true
    fi
    
    # Method 2: Try without password
    if [ "$connection_found" = false ]; then
        print_debug "Testing connection without password..."
        if mysql -u root -e "SELECT 1;" 2>/dev/null; then
            print_success "MySQL connection successful without password"
            connection_found=true
        fi
    fi
    
    # Method 3: Try with debian-sys-maint user
    if [ "$connection_found" = false ]; then
        print_debug "Testing connection with debian-sys-maint user..."
        DEBIAN_PASSWORD=$(grep password /etc/mysql/debian.cnf | head -1 | awk '{print $3}' 2>/dev/null)
        if [ ! -z "$DEBIAN_PASSWORD" ]; then
            if mysql -u debian-sys-maint -p$DEBIAN_PASSWORD -e "SELECT 1;" 2>/dev/null; then
                print_success "MySQL connection successful with debian-sys-maint user"
                connection_found=true
            fi
        fi
    fi
    
    if [ "$connection_found" = false ]; then
        print_error "All MySQL connection methods failed"
        return 1
    fi
    
    return 0
}

# Reset MySQL root password
reset_mysql_password() {
    print_status "Resetting MySQL root password..."
    
    # Stop MySQL
    systemctl stop mysql
    
    # Start MySQL in safe mode
    print_debug "Starting MySQL in safe mode..."
    mysqld_safe --skip-grant-tables --skip-networking &
    MYSQL_PID=$!
    
    # Wait for MySQL to start
    sleep 5
    
    # Connect and reset password
    print_debug "Resetting root password..."
    mysql -u root << EOF
USE mysql;
UPDATE user SET authentication_string=PASSWORD('$DB_PASSWORD') WHERE User='root';
UPDATE user SET plugin='mysql_native_password' WHERE User='root';
FLUSH PRIVILEGES;
EOF
    
    # Stop the safe mode MySQL
    kill $MYSQL_PID
    sleep 2
    
    # Start MySQL normally
    systemctl start mysql
    
    # Test the new password
    if mysql -u root -p$DB_PASSWORD -e "SELECT 1;" 2>/dev/null; then
        print_success "MySQL root password reset successful"
        return 0
    else
        print_error "Failed to reset MySQL root password"
        return 1
    fi
}

# Secure MySQL installation
secure_mysql() {
    print_status "Securing MySQL installation..."
    
    # Create secure installation script
    cat > /tmp/mysql_secure.sql << EOF
DELETE FROM mysql.user WHERE User='';
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';
FLUSH PRIVILEGES;
EOF
    
    # Execute secure installation
    if mysql -u root -p$DB_PASSWORD < /tmp/mysql_secure.sql; then
        print_success "MySQL installation secured"
        rm -f /tmp/mysql_secure.sql
        return 0
    else
        print_error "Failed to secure MySQL installation"
        rm -f /tmp/mysql_secure.sql
        return 1
    fi
}

# Create database and user
setup_database() {
    print_status "Setting up database and user..."
    
    # Create database setup script
    cat > /tmp/database_setup.sql << EOF
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF
    
    # Execute database setup
    if mysql -u root -p$DB_PASSWORD < /tmp/database_setup.sql; then
        print_success "Database and user created successfully"
    else
        print_error "Failed to create database and user"
        rm -f /tmp/database_setup.sql
        return 1
    fi
    
    # Test database connection
    if mysql -u $DB_USER -p$DB_PASSWORD -e "SELECT 1;" $DB_NAME 2>/dev/null; then
        print_success "Database connection test passed"
    else
        print_error "Database connection test failed"
        rm -f /tmp/database_setup.sql
        return 1
    fi
    
    # Clean up
    rm -f /tmp/database_setup.sql
    return 0
}

# Check MySQL configuration
check_mysql_config() {
    print_status "Checking MySQL configuration..."
    
    # Check if MySQL configuration file exists
    if [ -f "/etc/mysql/mysql.conf.d/mysqld.cnf" ]; then
        print_success "MySQL configuration file found"
    else
        print_warning "MySQL configuration file not found"
    fi
    
    # Check MySQL version
    MYSQL_VERSION=$(mysql --version 2>/dev/null | awk '{print $3}' | cut -d',' -f1)
    if [ ! -z "$MYSQL_VERSION" ]; then
        print_success "MySQL version: $MYSQL_VERSION"
    else
        print_error "Could not determine MySQL version"
    fi
    
    # Check MySQL data directory
    if [ -d "/var/lib/mysql" ]; then
        print_success "MySQL data directory found"
    else
        print_error "MySQL data directory not found"
    fi
}

# Repair MySQL installation
repair_mysql() {
    print_status "Repairing MySQL installation..."
    
    # Stop MySQL
    systemctl stop mysql
    
    # Check for corrupted tables
    print_debug "Checking for corrupted tables..."
    mysql_upgrade --force 2>/dev/null || true
    
    # Start MySQL
    systemctl start mysql
    
    # Test connection
    if test_mysql_connections; then
        print_success "MySQL repair completed successfully"
        return 0
    else
        print_error "MySQL repair failed"
        return 1
    fi
}

# Reinstall MySQL
reinstall_mysql() {
    print_status "Reinstalling MySQL..."
    
    # Stop MySQL
    systemctl stop mysql
    
    # Remove MySQL packages
    apt remove --purge -y mysql-server mysql-client mysql-common mysql-server-core-* mysql-client-core-*
    
    # Remove MySQL data directory
    rm -rf /var/lib/mysql
    rm -rf /var/log/mysql
    rm -rf /etc/mysql
    
    # Clean up
    apt autoremove -y
    apt autoclean
    
    # Reinstall MySQL
    apt update
    apt install -y mysql-server
    
    # Start MySQL
    systemctl start mysql
    systemctl enable mysql
    
    print_success "MySQL reinstalled successfully"
}

# Main troubleshooting menu
main_menu() {
    echo ""
    echo "==============================================================================="
    echo "MySQL Troubleshooting Script for Sky Education Portal"
    echo "==============================================================================="
    echo ""
    echo "1. Check MySQL service status"
    echo "2. Test MySQL connections"
    echo "3. Reset MySQL root password"
    echo "4. Secure MySQL installation"
    echo "5. Setup database and user"
    echo "6. Check MySQL configuration"
    echo "7. Repair MySQL installation"
    echo "8. Reinstall MySQL (DESTRUCTIVE)"
    echo "9. Run all checks and fixes"
    echo "0. Exit"
    echo ""
    read -p "Select an option (0-9): " choice
    
    case $choice in
        1)
            check_mysql_service
            ;;
        2)
            test_mysql_connections
            ;;
        3)
            reset_mysql_password
            ;;
        4)
            secure_mysql
            ;;
        5)
            setup_database
            ;;
        6)
            check_mysql_config
            ;;
        7)
            repair_mysql
            ;;
        8)
            print_warning "This will completely remove and reinstall MySQL!"
            read -p "Are you sure? (yes/no): " confirm
            if [ "$confirm" = "yes" ]; then
                reinstall_mysql
            else
                print_status "Reinstall cancelled"
            fi
            ;;
        9)
            print_status "Running all checks and fixes..."
            check_mysql_service
            test_mysql_connections
            if [ $? -ne 0 ]; then
                reset_mysql_password
                secure_mysql
            fi
            setup_database
            check_mysql_config
            ;;
        0)
            print_status "Exiting..."
            exit 0
            ;;
        *)
            print_error "Invalid option. Please try again."
            main_menu
            ;;
    esac
}

# Run main menu
main_menu
