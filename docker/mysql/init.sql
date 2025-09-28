-- Initialize database for Sky Education Portal
CREATE DATABASE IF NOT EXISTS sky_production;
CREATE USER IF NOT EXISTS 'sky_user'@'%' IDENTIFIED BY 'sky_password';
GRANT ALL PRIVILEGES ON sky_production.* TO 'sky_user'@'%';
FLUSH PRIVILEGES;
