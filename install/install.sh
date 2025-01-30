#!/bin/bash

# Exit on error
set -e

echo "Starting Data Center Inventory Management System (DCIMS) installation..."

# Update system
apt-get update
apt-get upgrade -y

# Install required packages
apt-get install -y apache2 php php-mysql php-json php-mbstring mariadb-server git

# Enable Apache modules
a2enmod rewrite
systemctl restart apache2

# Secure MySQL installation
mysql_secure_installation

# Create database and user
DB_NAME="dcims_db"
DB_USER="dcims_user"
DB_PASS=$(openssl rand -base64 12)

mysql -e "CREATE DATABASE ${DB_NAME};"
mysql -e "CREATE USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

# Create application directory structure
mkdir -p /var/www/dcims
mkdir -p /var/www/dcims/install
mkdir -p /var/www/dcims/includes
mkdir -p /var/www/dcims/css

# Copy installation files
cp schema.sql /var/www/dcims/install/

# Execute database schema
mysql ${DB_NAME} < /var/www/dcims/install/schema.sql

# Configure Apache virtual host
cat > /etc/apache2/sites-available/dcims.conf << EOF
<VirtualHost *:80>
    ServerName inventory.local
    DocumentRoot /var/www/dcims
    
    <Directory /var/www/dcims>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/dcims_error.log
    CustomLog \${APACHE_LOG_DIR}/dcims_access.log combined
</VirtualHost>
EOF

# Enable the site
a2ensite dcims.conf
systemctl restart apache2

# Create config file
cat > /var/www/dcims/config.php << EOF
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', '${DB_NAME}');
define('DB_USER', '${DB_USER}');
define('DB_PASS', '${DB_PASS}');
define('SITE_URL', 'http://inventory.local');
define('SITE_NAME', 'Data Center Inventory Management System');
define('SITE_SHORT_NAME', 'DCIMS');
EOF

# Set proper permissions
chown -R www-data:www-data /var/www/dcims
chmod -R 755 /var/www/dcims

echo "Installation completed!"
echo "Database Name: ${DB_NAME}"
echo "Database User: ${DB_USER}"
echo "Database Password: ${DB_PASS}"
echo "Please update your hosts file or DNS settings to point inventory.local to your server"