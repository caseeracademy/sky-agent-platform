# MySQL Installation Guide for Sky Education Portal

## Overview

This guide provides comprehensive solutions for MySQL authentication issues during the Sky Education Portal installation process. The installation scripts have been enhanced with multiple fallback methods to handle various MySQL authentication scenarios.

## Common MySQL Authentication Issues

### Error: `ERROR 1045 (28000): Access denied for user 'root'@'localhost' (using password: NO)`

This error occurs when:
- MySQL root user requires a password but none is provided
- MySQL authentication method has changed
- MySQL service is not properly configured
- Previous installation left MySQL in an inconsistent state

## Solutions Implemented

### 1. Multiple Authentication Methods

The enhanced installation scripts now try multiple authentication methods in order:

#### Method 1: No Password (Fresh Install)
```bash
mysql -u root -e "SELECT 1;"
```
- Used for fresh MySQL installations
- Most common scenario for new servers

#### Method 2: With Password
```bash
mysql -u root -p$DB_PASSWORD -e "SELECT 1;"
```
- Used when MySQL has already been secured
- Handles cases where password was set previously

#### Method 3: Debian System Maintenance User
```bash
mysql -u debian-sys-maint -p$DEBIAN_PASSWORD -e "SELECT 1;"
```
- Ubuntu/Debian specific fallback
- Uses system maintenance credentials from `/etc/mysql/debian.cnf`

#### Method 4: Password Reset (Last Resort)
- Stops MySQL service
- Starts MySQL in safe mode (`--skip-grant-tables`)
- Resets root password
- Restarts MySQL normally

### 2. Enhanced Error Handling

The installation scripts now include:
- Connection testing before database operations
- Graceful fallback between authentication methods
- Comprehensive error logging
- Rollback capabilities

### 3. MySQL Troubleshooting Script

A dedicated troubleshooting script (`mysql-troubleshoot.sh`) provides:
- Service status checking
- Connection testing
- Password reset capabilities
- Configuration validation
- Repair and reinstallation options

## Installation Script Improvements

### Enhanced MySQL Installation Function

```bash
install_mysql() {
    print_step "Installing MySQL with secure authentication..."
    
    # Install MySQL server
    apt install -y mysql-server
    
    # Start MySQL service
    systemctl start mysql
    systemctl enable mysql
    
    # Wait for MySQL to be ready
    print_status "Waiting for MySQL to start..."
    sleep 5
    
    # Test MySQL connection and handle authentication
    setup_mysql_authentication
    
    MYSQL_INSTALLED=true
    print_success "MySQL installed and secured"
}
```

### Robust Authentication Setup

```bash
setup_mysql_authentication() {
    print_status "Setting up MySQL authentication..."
    
    # Method 1: Try connecting without password (fresh install)
    if mysql -u root -e "SELECT 1;" 2>/dev/null; then
        # Secure the installation
        mysql -u root -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '$DB_PASSWORD';"
        # ... additional security commands
        return 0
    fi
    
    # Method 2: Try connecting with the password we set
    if mysql -u root -p$DB_PASSWORD -e "SELECT 1;" 2>/dev/null; then
        print_success "MySQL already secured"
        return 0
    fi
    
    # ... additional fallback methods
}
```

### Enhanced Database Setup

```bash
setup_database() {
    print_step "Setting up database..."
    
    # Test MySQL connection first
    if ! test_mysql_connection; then
        print_error "Cannot connect to MySQL. Database setup failed."
        return 1
    fi
    
    # Create database and user with error handling
    # ... database creation logic
}
```

## Troubleshooting Guide

### Using the MySQL Troubleshooting Script

1. **Run the troubleshooting script:**
   ```bash
   sudo ./mysql-troubleshoot.sh
   ```

2. **Select from the menu:**
   - Option 1: Check MySQL service status
   - Option 2: Test MySQL connections
   - Option 3: Reset MySQL root password
   - Option 4: Secure MySQL installation
   - Option 5: Setup database and user
   - Option 6: Check MySQL configuration
   - Option 7: Repair MySQL installation
   - Option 8: Reinstall MySQL (destructive)
   - Option 9: Run all checks and fixes

### Manual Troubleshooting Steps

#### 1. Check MySQL Service Status
```bash
sudo systemctl status mysql
sudo systemctl start mysql
```

#### 2. Test MySQL Connection
```bash
# Try without password
mysql -u root

# Try with password
mysql -u root -p

# Try with debian-sys-maint user
mysql -u debian-sys-maint -p$(grep password /etc/mysql/debian.cnf | head -1 | awk '{print $3}')
```

#### 3. Reset MySQL Root Password

If all connection methods fail:

```bash
# Stop MySQL
sudo systemctl stop mysql

# Start MySQL in safe mode
sudo mysqld_safe --skip-grant-tables --skip-networking &

# Connect and reset password
mysql -u root << EOF
USE mysql;
UPDATE user SET authentication_string=PASSWORD('SkySecure2024!') WHERE User='root';
UPDATE user SET plugin='mysql_native_password' WHERE User='root';
FLUSH PRIVILEGES;
EOF

# Stop safe mode MySQL
sudo pkill mysqld

# Start MySQL normally
sudo systemctl start mysql
```

#### 4. Secure MySQL Installation

```bash
mysql -u root -p << EOF
DELETE FROM mysql.user WHERE User='';
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';
FLUSH PRIVILEGES;
EOF
```

## Prevention Strategies

### 1. Pre-Installation Checks

The installation scripts now include comprehensive pre-flight checks:
- System requirements validation
- Available disk space verification
- Memory requirements checking
- Internet connectivity testing
- Existing service detection

### 2. Service Management

- Proper service startup sequencing
- Service status validation
- Automatic service restart on failure
- Service dependency management

### 3. Configuration Validation

- MySQL configuration file validation
- Database connection testing
- User privilege verification
- Database creation confirmation

## Backup Solutions

### 1. Automated Rollback

The installation scripts include comprehensive rollback capabilities:
- Service cleanup
- Database removal
- User account cleanup
- Configuration file removal
- Log file cleanup

### 2. Manual Recovery

If automated rollback fails:
```bash
# Stop all services
sudo systemctl stop mysql nginx php8.3-fpm

# Remove application files
sudo rm -rf /var/www/sky-agent-platform

# Remove MySQL databases and users
sudo mysql -u root -p -e "DROP DATABASE IF EXISTS sky_production; DROP USER IF EXISTS 'skyapp'@'localhost';"

# Remove configuration files
sudo rm -f /etc/nginx/sites-enabled/sky-agent-platform
sudo rm -f /etc/nginx/sites-available/sky-agent-platform

# Restart services
sudo systemctl start mysql nginx php8.3-fpm
```

## Best Practices

### 1. Installation Order

1. System packages
2. Application user creation
3. PHP installation
4. Composer installation
5. Node.js installation
6. Nginx installation
7. MySQL installation and configuration
8. Database setup
9. Application deployment
10. Service configuration

### 2. Error Handling

- Always test connections before operations
- Use multiple fallback methods
- Implement comprehensive logging
- Provide clear error messages
- Include recovery instructions

### 3. Security

- Use strong passwords
- Implement proper user privileges
- Remove default databases and users
- Enable MySQL security features
- Regular security updates

## Monitoring and Maintenance

### 1. Service Monitoring

```bash
# Check service status
sudo systemctl status mysql nginx php8.3-fpm

# Check service logs
sudo journalctl -u mysql
sudo journalctl -u nginx
sudo journalctl -u php8.3-fpm
```

### 2. Database Monitoring

```bash
# Check database connections
mysql -u root -p -e "SHOW PROCESSLIST;"

# Check database status
mysql -u root -p -e "SHOW DATABASES;"

# Check user privileges
mysql -u root -p -e "SELECT User, Host FROM mysql.user;"
```

### 3. Performance Monitoring

```bash
# Check MySQL performance
mysql -u root -p -e "SHOW STATUS;"

# Check slow queries
mysql -u root -p -e "SHOW VARIABLES LIKE 'slow_query_log';"
```

## Conclusion

The enhanced MySQL installation process now provides:

- ✅ **Multiple authentication methods** for various scenarios
- ✅ **Comprehensive error handling** with fallback options
- ✅ **Automated troubleshooting** capabilities
- ✅ **Rollback and recovery** procedures
- ✅ **Detailed logging** for debugging
- ✅ **Security best practices** implementation

These improvements ensure that MySQL authentication issues are handled gracefully, with multiple backup solutions available when problems occur.

For additional support, use the MySQL troubleshooting script or refer to the comprehensive installation logs.
