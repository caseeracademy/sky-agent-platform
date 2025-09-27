#!/bin/bash

# =============================================================================
# Sky Education Portal - Monitoring and Logging Setup
# =============================================================================
# 
# This script sets up comprehensive monitoring and logging for the
# Sky Education Portal in production environment.
# 
# Usage: ./monitoring-setup.sh
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

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    print_error "Please run this script as root (use sudo)"
    exit 1
fi

print_status "Setting up monitoring and logging for Sky Education Portal..."

# =============================================================================
# SYSTEM MONITORING WITH HTOP AND ATOP
# =============================================================================

print_status "Installing system monitoring tools..."

apt update
apt install -y htop atop iotop nethogs vnstat sysstat

# Configure sysstat for system statistics
sed -i 's/ENABLED="false"/ENABLED="true"/' /etc/default/sysstat

systemctl enable sysstat
systemctl start sysstat

print_success "System monitoring tools installed"

# =============================================================================
# LOG ROTATION CONFIGURATION
# =============================================================================

print_status "Configuring log rotation..."

# Laravel application logs
cat > /etc/logrotate.d/sky-portal << 'EOF'
/var/www/sky-portal/storage/logs/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 0644 skyapp www-data
    postrotate
        systemctl reload php8.2-fpm > /dev/null 2>&1 || true
    endscript
}
EOF

# Nginx logs
cat > /etc/logrotate.d/nginx-sky-portal << 'EOF'
/var/log/nginx/sky-portal.*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 0644 www-data adm
    prerotate
        if [ -d /etc/logrotate.d/httpd-prerotate ]; then \
            run-parts /etc/logrotate.d/httpd-prerotate; \
        fi \
    endscript
    postrotate
        systemctl reload nginx > /dev/null 2>&1 || true
    endscript
}
EOF

# MySQL logs (if using MySQL)
cat > /etc/logrotate.d/mysql-sky-portal << 'EOF'
/var/log/mysql/mysql-slow.log {
    daily
    missingok
    rotate 7
    compress
    delaycompress
    notifempty
    create 0644 mysql mysql
    postrotate
        mysqladmin flush-logs
    endscript
}
EOF

print_success "Log rotation configured"

# =============================================================================
# SYSTEM RESOURCE MONITORING
# =============================================================================

print_status "Setting up system resource monitoring..."

# Create monitoring script
cat > /usr/local/bin/sky-portal-monitor.sh << 'EOF'
#!/bin/bash

# Sky Portal System Monitor Script

LOG_FILE="/var/log/sky-portal-monitor.log"
ALERT_EMAIL="admin@yourdomain.com"  # Change this to your admin email
APP_PATH="/var/www/sky-portal"

# Thresholds
CPU_THRESHOLD=80
MEMORY_THRESHOLD=85
DISK_THRESHOLD=90
LOAD_THRESHOLD=10

# Function to log messages
log_message() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" >> "$LOG_FILE"
}

# Function to send alerts (requires mail command)
send_alert() {
    local subject="$1"
    local message="$2"
    
    # Log the alert
    log_message "ALERT: $subject - $message"
    
    # Send email if mail command is available
    if command -v mail >/dev/null 2>&1; then
        echo "$message" | mail -s "[Sky Portal] $subject" "$ALERT_EMAIL"
    fi
}

# Check CPU usage
cpu_usage=$(top -bn1 | grep "Cpu(s)" | sed "s/.*, *\([0-9.]*\)%* id.*/\1/" | awk '{print 100 - $1}' | cut -d. -f1)
if [ "$cpu_usage" -gt "$CPU_THRESHOLD" ]; then
    send_alert "High CPU Usage" "CPU usage is at ${cpu_usage}%"
fi

# Check memory usage
memory_usage=$(free | grep Mem | awk '{printf("%.0f", $3/$2 * 100.0)}')
if [ "$memory_usage" -gt "$MEMORY_THRESHOLD" ]; then
    send_alert "High Memory Usage" "Memory usage is at ${memory_usage}%"
fi

# Check disk usage
disk_usage=$(df -h "$APP_PATH" | awk 'NR==2 {print $5}' | sed 's/%//')
if [ "$disk_usage" -gt "$DISK_THRESHOLD" ]; then
    send_alert "High Disk Usage" "Disk usage is at ${disk_usage}%"
fi

# Check system load
load_avg=$(uptime | awk -F'[a-z]:' '{ print $2}' | awk '{print $1}' | sed 's/,//')
load_check=$(echo "$load_avg > $LOAD_THRESHOLD" | bc -l)
if [ "$load_check" -eq 1 ]; then
    send_alert "High System Load" "System load is at $load_avg"
fi

# Check if Laravel application is responding
if [ -f "$APP_PATH/artisan" ]; then
    cd "$APP_PATH"
    
    # Check if the app is up
    if ! curl -f -s -o /dev/null http://localhost/health 2>/dev/null; then
        send_alert "Application Down" "Sky Portal application is not responding"
    fi
    
    # Check database connectivity
    if ! sudo -u skyapp php artisan migrate:status >/dev/null 2>&1; then
        send_alert "Database Connection Failed" "Cannot connect to the database"
    fi
    
    # Check storage directory permissions
    if [ ! -w "$APP_PATH/storage/logs" ]; then
        send_alert "Storage Permission Issue" "Storage directory is not writable"
    fi
fi

# Check PHP-FPM status
if ! systemctl is-active --quiet php8.2-fpm; then
    send_alert "PHP-FPM Down" "PHP-FPM service is not running"
fi

# Check Nginx status
if ! systemctl is-active --quiet nginx; then
    send_alert "Nginx Down" "Nginx service is not running"
fi

# Check MySQL status
if ! systemctl is-active --quiet mysql; then
    send_alert "MySQL Down" "MySQL service is not running"
fi

# Check Redis status (if using Redis)
if systemctl list-unit-files | grep -q redis-server; then
    if ! systemctl is-active --quiet redis-server; then
        send_alert "Redis Down" "Redis service is not running"
    fi
fi

log_message "System check completed successfully"
EOF

chmod +x /usr/local/bin/sky-portal-monitor.sh

# Create cron job for monitoring
(crontab -l 2>/dev/null; echo "*/5 * * * * /usr/local/bin/sky-portal-monitor.sh") | crontab -

print_success "System resource monitoring configured"

# =============================================================================
# APPLICATION PERFORMANCE MONITORING
# =============================================================================

print_status "Setting up application performance monitoring..."

# Create Laravel performance monitoring script
cat > /usr/local/bin/laravel-performance-monitor.sh << 'EOF'
#!/bin/bash

APP_PATH="/var/www/sky-portal"
LOG_FILE="/var/log/laravel-performance.log"

if [ ! -f "$APP_PATH/artisan" ]; then
    echo "Laravel application not found at $APP_PATH"
    exit 1
fi

cd "$APP_PATH"

# Function to log with timestamp
log_with_timestamp() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" >> "$LOG_FILE"
}

# Check queue status
queue_failed=$(sudo -u skyapp php artisan queue:failed --format=json 2>/dev/null | jq length 2>/dev/null || echo "0")
if [ "$queue_failed" -gt 0 ]; then
    log_with_timestamp "WARNING: $queue_failed failed jobs in queue"
fi

# Check cache status
cache_info=$(sudo -u skyapp php artisan cache:table 2>/dev/null || echo "Cache table not available")
log_with_timestamp "Cache status: $cache_info"

# Check storage usage
storage_size=$(du -sh storage/ | cut -f1)
log_with_timestamp "Storage directory size: $storage_size"

# Check log file sizes
log_size=$(du -sh storage/logs/ | cut -f1)
log_with_timestamp "Log directory size: $log_size"

# Check for large log files (>100MB)
find storage/logs/ -name "*.log" -size +100M -exec basename {} \; | while read large_log; do
    log_with_timestamp "WARNING: Large log file detected: $large_log"
done

log_with_timestamp "Laravel performance check completed"
EOF

chmod +x /usr/local/bin/laravel-performance-monitor.sh

# Add to cron (runs every hour)
(crontab -l 2>/dev/null; echo "0 * * * * /usr/local/bin/laravel-performance-monitor.sh") | crontab -

print_success "Application performance monitoring configured"

# =============================================================================
# SECURITY MONITORING
# =============================================================================

print_status "Setting up security monitoring..."

# Install and configure fail2ban
apt install -y fail2ban

# Configure fail2ban for Laravel application
cat > /etc/fail2ban/jail.d/sky-portal.conf << 'EOF'
[sky-portal-auth]
enabled = true
port = http,https
filter = sky-portal-auth
logpath = /var/www/sky-portal/storage/logs/laravel.log
maxretry = 5
bantime = 3600
findtime = 600

[nginx-sky-portal]
enabled = true
port = http,https
filter = nginx-sky-portal
logpath = /var/log/nginx/sky-portal.access.log
maxretry = 10
bantime = 3600
findtime = 600
EOF

# Create custom fail2ban filter for Laravel authentication failures
cat > /etc/fail2ban/filter.d/sky-portal-auth.conf << 'EOF'
[Definition]
failregex = .*authentication.failed.*ip.*<HOST>
            .*Unauthenticated.*<HOST>
            .*403.*Forbidden.*<HOST>
ignoreregex =
EOF

# Create custom fail2ban filter for Nginx
cat > /etc/fail2ban/filter.d/nginx-sky-portal.conf << 'EOF'
[Definition]
failregex = ^<HOST> -.*"(GET|POST).*" (404|403|400|401) .*$
            ^<HOST> -.*".*" (404|403|400|401) .*$
ignoreregex =
EOF

systemctl restart fail2ban

# Create security monitoring script
cat > /usr/local/bin/security-monitor.sh << 'EOF'
#!/bin/bash

LOG_FILE="/var/log/security-monitor.log"
APP_PATH="/var/www/sky-portal"

log_message() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" >> "$LOG_FILE"
}

# Check for suspicious IP addresses in logs
suspicious_ips=$(grep -i "failed\|error\|forbidden" /var/log/nginx/sky-portal.access.log | awk '{print $1}' | sort | uniq -c | sort -nr | head -10)
if [ -n "$suspicious_ips" ]; then
    log_message "Top suspicious IPs: $suspicious_ips"
fi

# Check for file permission changes
find "$APP_PATH" -type f -newer /tmp/last_security_check 2>/dev/null | while read file; do
    log_message "File modified: $file"
done

# Update timestamp
touch /tmp/last_security_check

# Check for failed login attempts in Laravel logs
failed_logins=$(grep -i "authentication.failed" "$APP_PATH/storage/logs/laravel.log" | wc -l)
if [ "$failed_logins" -gt 0 ]; then
    log_message "Failed login attempts in the last period: $failed_logins"
fi

log_message "Security check completed"
EOF

chmod +x /usr/local/bin/security-monitor.sh

# Add security monitoring to cron (runs every 15 minutes)
(crontab -l 2>/dev/null; echo "*/15 * * * * /usr/local/bin/security-monitor.sh") | crontab -

print_success "Security monitoring configured"

# =============================================================================
# DATABASE MONITORING
# =============================================================================

print_status "Setting up database monitoring..."

# Create database monitoring script
cat > /usr/local/bin/mysql-monitor.sh << 'EOF'
#!/bin/bash

LOG_FILE="/var/log/mysql-monitor.log"
MYSQL_USER="monitor_user"
MYSQL_PASS="CHANGE_THIS_MONITOR_PASSWORD"  # Change this password

log_message() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" >> "$LOG_FILE"
}

# Check MySQL connection
if ! mysql -u "$MYSQL_USER" -p"$MYSQL_PASS" -e "SELECT 1;" >/dev/null 2>&1; then
    log_message "ERROR: Cannot connect to MySQL"
    exit 1
fi

# Check database size
db_size=$(mysql -u "$MYSQL_USER" -p"$MYSQL_PASS" -e "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS 'Size in MB' FROM information_schema.tables WHERE table_schema='sky_production';" | tail -1)
log_message "Database size: ${db_size} MB"

# Check slow queries
slow_queries=$(mysql -u "$MYSQL_USER" -p"$MYSQL_PASS" -e "SHOW GLOBAL STATUS LIKE 'Slow_queries';" | tail -1 | awk '{print $2}')
log_message "Slow queries: $slow_queries"

# Check connections
connections=$(mysql -u "$MYSQL_USER" -p"$MYSQL_PASS" -e "SHOW GLOBAL STATUS LIKE 'Threads_connected';" | tail -1 | awk '{print $2}')
max_connections=$(mysql -u "$MYSQL_USER" -p"$MYSQL_PASS" -e "SHOW VARIABLES LIKE 'max_connections';" | tail -1 | awk '{print $2}')
log_message "Current connections: $connections / $max_connections"

# Check table locks
table_locks=$(mysql -u "$MYSQL_USER" -p"$MYSQL_PASS" -e "SHOW GLOBAL STATUS LIKE 'Table_locks_waited';" | tail -1 | awk '{print $2}')
log_message "Table locks waited: $table_locks"

log_message "MySQL monitoring completed"
EOF

chmod +x /usr/local/bin/mysql-monitor.sh

# Add to cron (runs every 30 minutes)
(crontab -l 2>/dev/null; echo "*/30 * * * * /usr/local/bin/mysql-monitor.sh") | crontab -

print_success "Database monitoring configured"

# =============================================================================
# LOG ANALYSIS TOOLS
# =============================================================================

print_status "Installing log analysis tools..."

# Install goaccess for web log analysis
apt install -y goaccess

# Create goaccess configuration for nginx logs
cat > /etc/goaccess/goaccess.conf << 'EOF'
time-format %H:%M:%S
date-format %d/%b/%Y
log-format %h %^[%d:%t %^] "%r" %s %b "%R" "%u"

real-time-html true
origin http://localhost
port 7890
ws-url wss://localhost:7890

html-prefs {"theme":"bright","perPage":5,"layout":"horizontal","showTables":true,"autoHideTables":false,"showBreadCrumb":true,"hideCols":false,"hideColsSmallScreen":true,"autoHideGraph":false,"autoHidePanels":false,"selectedGraph":0,"selectedPanel":0}
EOF

print_success "Log analysis tools installed"

# =============================================================================
# BACKUP MONITORING
# =============================================================================

print_status "Setting up backup monitoring..."

# Create backup monitoring script
cat > /usr/local/bin/backup-monitor.sh << 'EOF'
#!/bin/bash

BACKUP_DIR="/backups"
LOG_FILE="/var/log/backup-monitor.log"
ALERT_EMAIL="admin@yourdomain.com"

log_message() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" >> "$LOG_FILE"
}

send_alert() {
    local subject="$1"
    local message="$2"
    
    log_message "ALERT: $subject - $message"
    
    if command -v mail >/dev/null 2>&1; then
        echo "$message" | mail -s "[Sky Portal Backup] $subject" "$ALERT_EMAIL"
    fi
}

# Check if backup directory exists
if [ ! -d "$BACKUP_DIR" ]; then
    send_alert "Backup Directory Missing" "Backup directory $BACKUP_DIR does not exist"
    exit 1
fi

# Check for recent database backups (within last 24 hours)
recent_db_backup=$(find "$BACKUP_DIR" -name "*.sql*" -mtime -1 | wc -l)
if [ "$recent_db_backup" -eq 0 ]; then
    send_alert "No Recent Database Backup" "No database backup found in the last 24 hours"
fi

# Check backup directory size
backup_size=$(du -sh "$BACKUP_DIR" | cut -f1)
log_message "Total backup size: $backup_size"

# Check disk space in backup directory
backup_disk_usage=$(df -h "$BACKUP_DIR" | awk 'NR==2 {print $5}' | sed 's/%//')
if [ "$backup_disk_usage" -gt 90 ]; then
    send_alert "Backup Disk Full" "Backup disk usage is at ${backup_disk_usage}%"
fi

# Clean old backups (older than 30 days)
old_backups=$(find "$BACKUP_DIR" -type f -mtime +30)
if [ -n "$old_backups" ]; then
    echo "$old_backups" | xargs rm -f
    log_message "Cleaned old backups older than 30 days"
fi

log_message "Backup monitoring completed"
EOF

chmod +x /usr/local/bin/backup-monitor.sh

# Add to cron (runs daily at 6 AM)
(crontab -l 2>/dev/null; echo "0 6 * * * /usr/local/bin/backup-monitor.sh") | crontab -

print_success "Backup monitoring configured"

# =============================================================================
# HEALTH CHECK ENDPOINT
# =============================================================================

print_status "Setting up health check monitoring..."

# Create health check script
cat > /usr/local/bin/health-check.sh << 'EOF'
#!/bin/bash

APP_URL="http://localhost"
LOG_FILE="/var/log/health-check.log"

log_message() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" >> "$LOG_FILE"
}

# Check application health endpoint
response_code=$(curl -s -o /dev/null -w "%{http_code}" "$APP_URL/health" || echo "000")
response_time=$(curl -s -o /dev/null -w "%{time_total}" "$APP_URL/health" || echo "0")

if [ "$response_code" != "200" ]; then
    log_message "ERROR: Health check failed with response code $response_code"
else
    log_message "Health check passed (${response_time}s)"
fi

# Check SSL certificate expiry (if using HTTPS)
if [[ "$APP_URL" == https* ]]; then
    cert_days=$(echo | openssl s_client -servername $(echo "$APP_URL" | sed 's/https:\/\///') -connect $(echo "$APP_URL" | sed 's/https:\/\///'):443 2>/dev/null | openssl x509 -noout -dates | grep notAfter | cut -d= -f2 | xargs -I {} date -d {} +%s)
    current_date=$(date +%s)
    days_until_expiry=$(( (cert_days - current_date) / 86400 ))
    
    if [ "$days_until_expiry" -lt 30 ]; then
        log_message "WARNING: SSL certificate expires in $days_until_expiry days"
    fi
fi
EOF

chmod +x /usr/local/bin/health-check.sh

# Add to cron (runs every 5 minutes)
(crontab -l 2>/dev/null; echo "*/5 * * * * /usr/local/bin/health-check.sh") | crontab -

print_success "Health check monitoring configured"

# =============================================================================
# MONITORING DASHBOARD SETUP
# =============================================================================

print_status "Creating monitoring dashboard..."

# Create a simple monitoring dashboard
cat > /var/www/html/monitoring.html << 'EOF'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sky Portal Monitoring Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .card { background: white; padding: 20px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .status-good { color: #28a745; }
        .status-warning { color: #ffc107; }
        .status-error { color: #dc3545; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
    <script>
        function refreshData() {
            fetch('/monitoring-api.php')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('system-info').innerHTML = data.system;
                    document.getElementById('app-status').innerHTML = data.application;
                    document.getElementById('db-status').innerHTML = data.database;
                    document.getElementById('logs').innerHTML = data.logs;
                })
                .catch(error => console.error('Error:', error));
        }
        
        setInterval(refreshData, 30000); // Refresh every 30 seconds
        window.onload = refreshData;
    </script>
</head>
<body>
    <div class="container">
        <h1>Sky Portal Monitoring Dashboard</h1>
        
        <div class="grid">
            <div class="card">
                <h3>System Status</h3>
                <div id="system-info">Loading...</div>
            </div>
            
            <div class="card">
                <h3>Application Status</h3>
                <div id="app-status">Loading...</div>
            </div>
            
            <div class="card">
                <h3>Database Status</h3>
                <div id="db-status">Loading...</div>
            </div>
            
            <div class="card">
                <h3>Recent Logs</h3>
                <div id="logs">Loading...</div>
            </div>
        </div>
        
        <div class="card">
            <h3>Quick Actions</h3>
            <p>
                <a href="/goaccess.html" target="_blank">View Access Logs</a> |
                <a href="#" onclick="refreshData()">Refresh Data</a>
            </p>
        </div>
    </div>
</body>
</html>
EOF

print_success "Monitoring dashboard created at /var/www/html/monitoring.html"

# =============================================================================
# FINAL CONFIGURATION
# =============================================================================

print_status "Finalizing monitoring setup..."

# Create log directories with proper permissions
mkdir -p /var/log/sky-portal
chown skyapp:www-data /var/log/sky-portal
chmod 755 /var/log/sky-portal

# Set up log file permissions
touch /var/log/sky-portal-monitor.log
touch /var/log/laravel-performance.log
touch /var/log/security-monitor.log
touch /var/log/mysql-monitor.log
touch /var/log/backup-monitor.log
touch /var/log/health-check.log

chown skyapp:www-data /var/log/*sky*
chmod 644 /var/log/*sky*

# Restart services
systemctl restart fail2ban
systemctl restart rsyslog

print_success "Monitoring setup completed!"

# =============================================================================
# SUMMARY
# =============================================================================

echo ""
echo "==============================================================================="
echo "MONITORING SETUP SUMMARY"
echo "==============================================================================="
echo ""
print_success "✓ System resource monitoring (CPU, Memory, Disk, Load)"
print_success "✓ Application performance monitoring"
print_success "✓ Security monitoring with Fail2Ban"
print_success "✓ Database monitoring"
print_success "✓ Log rotation and analysis tools"
print_success "✓ Backup monitoring"
print_success "✓ Health check monitoring"
print_success "✓ Monitoring dashboard"
echo ""
echo "MONITORING LOCATIONS:"
echo "- System logs: /var/log/sky-portal-monitor.log"
echo "- Performance logs: /var/log/laravel-performance.log"
echo "- Security logs: /var/log/security-monitor.log"
echo "- Database logs: /var/log/mysql-monitor.log"
echo "- Backup logs: /var/log/backup-monitor.log"
echo "- Health check logs: /var/log/health-check.log"
echo "- Dashboard: http://your-server/monitoring.html"
echo ""
echo "NEXT STEPS:"
print_warning "1. Update email addresses in monitoring scripts"
print_warning "2. Configure mail server for alerts"
print_warning "3. Customize alert thresholds as needed"
print_warning "4. Set up external monitoring services"
print_warning "5. Review and test all monitoring scripts"
echo ""
print_success "Monitoring setup completed successfully!"
