#!/bin/bash

# Database deployment script
# This script handles database setup and schema updates

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

# Create database and import schema
create_database

echo "Database deployment completed successfully!"
echo "Database: ${DB_NAME} at ${DB_HOST}:${DB_PORT}"