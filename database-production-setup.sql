-- ============================================================================
-- Sky Education Portal - Production Database Setup
-- ============================================================================
-- 
-- This script sets up the production database with proper users, permissions,
-- and optimizations for the Sky Education Portal Laravel application.
-- 
-- Usage (MySQL):
--   mysql -u root -p < database-production-setup.sql
-- 
-- Usage (PostgreSQL):
--   psql -U postgres -f database-production-setup.sql
-- ============================================================================

-- MYSQL SETUP
-- ============================================================================

-- Create production database
CREATE DATABASE IF NOT EXISTS sky_production 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;

-- Create application user with limited privileges
CREATE USER IF NOT EXISTS 'skyapp'@'localhost' IDENTIFIED BY 'CHANGE_THIS_PASSWORD';
CREATE USER IF NOT EXISTS 'skyapp'@'%' IDENTIFIED BY 'CHANGE_THIS_PASSWORD';

-- Grant necessary privileges to application user
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP, REFERENCES 
ON sky_production.* TO 'skyapp'@'localhost';

GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP, REFERENCES 
ON sky_production.* TO 'skyapp'@'%';

-- Create read-only user for reporting/analytics (optional)
CREATE USER IF NOT EXISTS 'skyapp_readonly'@'localhost' IDENTIFIED BY 'CHANGE_THIS_READONLY_PASSWORD';
GRANT SELECT ON sky_production.* TO 'skyapp_readonly'@'localhost';

-- Create backup user (optional)
CREATE USER IF NOT EXISTS 'skyapp_backup'@'localhost' IDENTIFIED BY 'CHANGE_THIS_BACKUP_PASSWORD';
GRANT SELECT, LOCK TABLES, SHOW VIEW, EVENT, TRIGGER ON sky_production.* TO 'skyapp_backup'@'localhost';

-- Flush privileges
FLUSH PRIVILEGES;

-- Use the production database
USE sky_production;

-- ============================================================================
-- DATABASE OPTIMIZATION SETTINGS
-- ============================================================================

-- Set MySQL configuration for optimal Laravel performance
-- Add these to your /etc/mysql/mysql.conf.d/mysqld.cnf file:

/*
[mysqld]
# Basic Settings
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_log_buffer_size = 64M
innodb_flush_log_at_trx_commit = 2
innodb_file_per_table = 1

# Connection Settings
max_connections = 500
connect_timeout = 30
wait_timeout = 600
interactive_timeout = 600

# Query Cache (if using MySQL 5.7 or earlier)
query_cache_type = 1
query_cache_size = 64M
query_cache_limit = 2M

# Binary Logging
log_bin = /var/log/mysql/mysql-bin.log
expire_logs_days = 7
max_binlog_size = 100M
binlog_format = ROW

# Performance Schema
performance_schema = ON

# Character Set
character_set_server = utf8mb4
collation_server = utf8mb4_unicode_ci

# Table Settings
table_open_cache = 4000
table_definition_cache = 2000

# Thread Settings
thread_cache_size = 50
thread_stack = 256K

# MyISAM Settings (if using MyISAM tables)
key_buffer_size = 256M
myisam_sort_buffer_size = 64M

# InnoDB Settings
innodb_flush_method = O_DIRECT
innodb_lock_wait_timeout = 50
innodb_thread_concurrency = 0
innodb_concurrency_tickets = 5000

# Slow Query Log
slow_query_log = 1
slow_query_log_file = /var/log/mysql/mysql-slow.log
long_query_time = 2

# General Log (disable in production for performance)
general_log = 0
*/

-- ============================================================================
-- POSTGRESQL SETUP (Alternative to MySQL)
-- ============================================================================

/*
-- Create production database
CREATE DATABASE sky_production 
    WITH ENCODING 'UTF8' 
    LC_COLLATE='en_US.UTF-8' 
    LC_CTYPE='en_US.UTF-8' 
    TEMPLATE=template0;

-- Create application user
CREATE USER skyapp WITH PASSWORD 'CHANGE_THIS_PASSWORD';

-- Grant privileges
GRANT ALL PRIVILEGES ON DATABASE sky_production TO skyapp;

-- Connect to the database
\c sky_production;

-- Grant schema privileges
GRANT ALL ON SCHEMA public TO skyapp;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO skyapp;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO skyapp;

-- Set default privileges for future objects
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO skyapp;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO skyapp;

-- Create read-only user (optional)
CREATE USER skyapp_readonly WITH PASSWORD 'CHANGE_THIS_READONLY_PASSWORD';
GRANT CONNECT ON DATABASE sky_production TO skyapp_readonly;
GRANT USAGE ON SCHEMA public TO skyapp_readonly;
GRANT SELECT ON ALL TABLES IN SCHEMA public TO skyapp_readonly;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT ON TABLES TO skyapp_readonly;
*/

-- ============================================================================
-- INDEXES FOR PERFORMANCE OPTIMIZATION
-- ============================================================================

-- Note: These indexes will be created automatically when you run Laravel migrations
-- but they're documented here for reference

/*
-- Users table indexes
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_parent_agent_id ON users(parent_agent_id);
CREATE INDEX idx_users_active ON users(is_active);

-- Students table indexes  
CREATE INDEX idx_students_agent_id ON students(agent_id);
CREATE INDEX idx_students_email ON students(email);
CREATE INDEX idx_students_name ON students(first_name, last_name);

-- Applications table indexes
CREATE INDEX idx_applications_student_id ON applications(student_id);
CREATE INDEX idx_applications_program_id ON applications(program_id);
CREATE INDEX idx_applications_agent_id ON applications(agent_id);
CREATE INDEX idx_applications_status ON applications(status);
CREATE INDEX idx_applications_assigned_admin ON applications(assigned_admin_id);
CREATE INDEX idx_applications_created_at ON applications(created_at);

-- Application documents indexes
CREATE INDEX idx_application_documents_application_id ON application_documents(application_id);
CREATE INDEX idx_application_documents_type ON application_documents(document_type);

-- Application logs indexes
CREATE INDEX idx_application_logs_application_id ON application_logs(application_id);
CREATE INDEX idx_application_logs_created_at ON application_logs(created_at);

-- Commissions table indexes
CREATE INDEX idx_commissions_agent_id ON commissions(agent_id);
CREATE INDEX idx_commissions_application_id ON commissions(application_id);
CREATE INDEX idx_commissions_status ON commissions(status);
CREATE INDEX idx_commissions_created_at ON commissions(created_at);

-- Payouts table indexes
CREATE INDEX idx_payouts_agent_id ON payouts(agent_id);
CREATE INDEX idx_payouts_status ON payouts(status);
CREATE INDEX idx_payouts_created_at ON payouts(created_at);

-- Programs table indexes
CREATE INDEX idx_programs_university_id ON programs(university_id);
CREATE INDEX idx_programs_active ON programs(is_active);

-- Universities table indexes
CREATE INDEX idx_universities_active ON universities(is_active);
CREATE INDEX idx_universities_name ON universities(name);

-- Session table indexes (if using database sessions)
CREATE INDEX idx_sessions_user_id ON sessions(user_id);
CREATE INDEX idx_sessions_last_activity ON sessions(last_activity);

-- Cache table indexes (if using database cache)
CREATE INDEX idx_cache_key ON cache(key);
CREATE INDEX idx_cache_expiration ON cache(expiration);

-- Jobs table indexes (if using database queues)
CREATE INDEX idx_jobs_queue ON jobs(queue);
CREATE INDEX idx_jobs_available_at ON jobs(available_at);
CREATE INDEX idx_jobs_created_at ON jobs(created_at);

-- Failed jobs table indexes
CREATE INDEX idx_failed_jobs_failed_at ON failed_jobs(failed_at);
*/

-- ============================================================================
-- MAINTENANCE PROCEDURES
-- ============================================================================

-- Create a stored procedure for database maintenance (MySQL)
DELIMITER $$

CREATE PROCEDURE OptimizeDatabase()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE table_name VARCHAR(128);
    DECLARE cur CURSOR FOR 
        SELECT TABLE_NAME 
        FROM information_schema.TABLES 
        WHERE TABLE_SCHEMA = 'sky_production' 
        AND TABLE_TYPE = 'BASE TABLE';
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    OPEN cur;
    
    read_loop: LOOP
        FETCH cur INTO table_name;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        SET @sql = CONCAT('OPTIMIZE TABLE ', table_name);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END LOOP;
    
    CLOSE cur;
    
    -- Analyze tables for better query planning
    ANALYZE TABLE applications, users, students, commissions, payouts;
    
END$$

DELIMITER ;

-- ============================================================================
-- BACKUP STRATEGY SETUP
-- ============================================================================

-- Create backup user with minimal required privileges
CREATE USER IF NOT EXISTS 'backup_user'@'localhost' IDENTIFIED BY 'CHANGE_THIS_BACKUP_PASSWORD';

GRANT SELECT, LOCK TABLES, SHOW VIEW, EVENT, TRIGGER, RELOAD ON *.* TO 'backup_user'@'localhost';
GRANT REPLICATION CLIENT ON *.* TO 'backup_user'@'localhost';

-- Flush privileges
FLUSH PRIVILEGES;

-- ============================================================================
-- MONITORING SETUP
-- ============================================================================

-- Create monitoring user with minimal privileges
CREATE USER IF NOT EXISTS 'monitor_user'@'localhost' IDENTIFIED BY 'CHANGE_THIS_MONITOR_PASSWORD';

GRANT PROCESS, SELECT ON *.* TO 'monitor_user'@'localhost';
GRANT SELECT ON performance_schema.* TO 'monitor_user'@'localhost';
GRANT SELECT ON information_schema.* TO 'monitor_user'@'localhost';

-- Flush privileges
FLUSH PRIVILEGES;

-- ============================================================================
-- INITIAL DATA VERIFICATION
-- ============================================================================

-- After running Laravel migrations and seeders, verify the setup:

/*
-- Check that all required tables exist
SELECT TABLE_NAME, TABLE_ROWS 
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'sky_production' 
ORDER BY TABLE_NAME;

-- Verify user roles are properly set up
SELECT role, COUNT(*) as count 
FROM users 
GROUP BY role;

-- Check that indexes are created
SELECT TABLE_NAME, INDEX_NAME, COLUMN_NAME 
FROM information_schema.STATISTICS 
WHERE TABLE_SCHEMA = 'sky_production' 
ORDER BY TABLE_NAME, INDEX_NAME;
*/

-- ============================================================================
-- SECURITY NOTES
-- ============================================================================

/*
IMPORTANT SECURITY REMINDERS:

1. Change all default passwords in this script before running
2. Use strong, unique passwords for each database user
3. Limit database access to specific IP addresses if possible
4. Enable SSL/TLS for database connections
5. Regularly update database software
6. Monitor database access logs
7. Implement proper backup encryption
8. Use database firewall rules
9. Regular security audits
10. Keep this file secure and do not commit passwords to version control

Example of creating users with IP restrictions:
CREATE USER 'skyapp'@'192.168.1.100' IDENTIFIED BY 'password';
CREATE USER 'skyapp'@'10.0.0.0/255.255.255.0' IDENTIFIED BY 'password';
*/
