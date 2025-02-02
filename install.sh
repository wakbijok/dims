#!/bin/bash

# Database Configuration
DB_HOST=${1:-"192.168.0.30"}    # Database host
DB_PORT=${2:-"3306"}            # Database port
DB_USER=${3:-"admin"}           # Admin username
DB_NAME=${4:-"dims_db"}         # Database name
DB_PASSWORD=${5:-"admin_password"} # Admin password

# Export variables for docker-compose
export DB_HOST DB_USER DB_PASSWORD DB_NAME

# Function to create database and import schema
create_database() {
    echo "Setting up database..."
    
    # Import schema
    SCHEMA_FILE="database/schema.sql"
    
    # Check if mysql client is installed
    if ! command -v mysql &> /dev/null; then
        echo "MySQL client not found. Installing..."
        apt-get update && apt-get install -y default-mysql-client
    fi
    
    # Create database
    echo "Creating database..."
    mysql -h"${DB_HOST}" -P"${DB_PORT}" -u"${DB_USER}" -p"${DB_PASSWORD}" -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME};"
    
    if [ $? -eq 0 ]; then
        echo "Database created successfully."
        
        # Import schema
        echo "Importing schema..."
        mysql -h"${DB_HOST}" -P"${DB_PORT}" -u"${DB_USER}" -p"${DB_PASSWORD}" "${DB_NAME}" < "$SCHEMA_FILE"
        
        if [ $? -eq 0 ]; then
            echo "Schema imported successfully."
        else
            echo "Error importing schema."
            exit 1
        fi
    else
        echo "Error creating database."
        exit 1
    fi
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