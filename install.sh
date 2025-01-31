#!/bin/bash

# Update system & install dependencies
sudo apt update
sudo apt install -y git apache2 php libapache2-mod-php php-mysql php-curl php-json php-cgi

# Enable Apache modules
sudo a2enmod rewrite actions cgi  

# Restart Apache 
sudo service apache2 restart

# Clone repo
git clone https://github.com/wakbijok/dims.git
sudo mv dims /var/www/html/

# Copy files
sudo cp inventory_api.php /var/www/html/dims/
sudo cp index.html /var/www/html/dims/
sudo cp script.js /var/www/html/dims/  
sudo cp styles.css /var/www/html/dims/

# Set permissions
sudo chown -R www-data:www-data /var/www/html/dims
sudo chmod -R 775 /var/www/html/dims

echo "Inventory system installed. Access at http://your_server_ip/dims"