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
mkdir -p /var/www/dcims/css
mkdir -p /var/www/dcims/includes

# Copy source files to web directory
cp *.php /var/www/dcims/
cp css/* /var/www/dcims/css/
cp includes/* /var/www/dcims/includes/

# Execute database schema
mysql ${DB_NAME} < install/schema.sql

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

# Enable the site and disable default
a2ensite dcims.conf
a2dissite 000-default.conf
systemctl restart apache2

# Set proper permissions
chown -R www-data:www-data /var/www/dcims
chmod -R 755 /var/www/dcims

echo "Installation completed!"
echo "Database Name: ${DB_NAME}"
echo "Database User: ${DB_USER}"
echo "Database Password: ${DB_PASS}"
echo "Please update your hosts file or DNS settings to point inventory.local to your server"

# Save database credentials
echo "Saving database credentials to /root/dcims_credentials.txt"
cat > /root/dcims_credentials.txt << EOF
Database Name: ${DB_NAME}
Database User: ${DB_USER}
Database Password: ${DB_PASS}
EOF
chmod 600 /root/dcims_credentials.txt