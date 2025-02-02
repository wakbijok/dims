#!/bin/bash

# Database Configuration
DB_HOST=${1:-"192.168.0.30"}    # Database host
DB_PORT=${2:-"3306"}            # Database port
DB_ROOT_PASSWORD=${3:-"rootpass"}  # Root password for database access
DB_NAME=${4:-"dims_db"}         # Database name
DB_USER=${5:-"dims_user"}       # Database user
DB_PASSWORD=${6:-"dims_password"} # Database user password

# Export variables for docker-compose
export DB_HOST DB_USER DB_PASSWORD DB_NAME

# Function to create database and user
create_database() {
    echo "Creating database and user..."
    
    # SQL commands
    SQL_COMMANDS="
    CREATE DATABASE IF NOT EXISTS ${DB_NAME};
    CREATE USER IF NOT EXISTS '${DB_USER}'@'%' IDENTIFIED BY '${DB_PASSWORD}';
    GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'%';
    FLUSH PRIVILEGES;
    "
    
    # Import schema
    SCHEMA_FILE="database/schema.sql"
    
    # Check if mysql client is installed
    if ! command -v mysql &> /dev/null; then
        echo "MySQL client not found. Installing..."
        apt-get update && apt-get install -y default-mysql-client
    fi
    
    # Create database and user
    echo "$SQL_COMMANDS" | mysql -h"${DB_HOST}" -P"${DB_PORT}" -uroot -p"${DB_ROOT_PASSWORD}"
    
    # Import schema
    mysql -h"${DB_HOST}" -P"${DB_PORT}" -u"${DB_USER}" -p"${DB_PASSWORD}" "${DB_NAME}" < "$SCHEMA_FILE"
}

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "Docker not found. Installing Docker..."
    curl -fsSL https://get.docker.com -o get-docker.sh
    sudo sh get-docker.sh
    sudo usermod -aG docker $USER
    rm get-docker.sh
fi

# Check if Docker Compose is installed
if ! command -v docker-compose &> /dev/null; then
    echo "Docker Compose not found. Installing Docker Compose..."
    sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
    sudo chmod +x /usr/local/bin/docker-compose
fi

# Create necessary directories
mkdir -p src/
mkdir -p src/config
mkdir -p src/assets
mkdir -p src/includes
mkdir -p database

# Build and start containers
docker-compose up -d --build

# Create database and import schema
create_database

echo "Installation completed successfully!"
echo "You can access the application at http://localhost:8080"