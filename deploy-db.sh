#!/bin/bash

# Database deployment script
# This script handles database setup and schema updates

# Load environment variables from .env file if it exists
if [ -f .env ]; then
    export $(cat .env | xargs)
    echo "Loaded environment variables from .env"
fi

# Database Configuration (command line arguments override .env values)
DB_HOST=${1:-${DB_HOST:-"192.168.0.30"}}    # Database host
DB_PORT=${2:-${DB_PORT:-"3306"}}            # Database port
DB_USER=${3:-${DB_USER:-"admin"}}           # Admin username
DB_NAME=${4:-${DB_NAME:-"dims_db"}}         # Database name
DB_PASSWORD=${5:-${DB_PASSWORD}}            # Admin password

# Export variables for docker-compose
export DB_HOST DB_USER DB_PASSWORD DB_NAME

# Function to create database and import schema
create_database() {
    echo "Setting up database..."
    
    # Import schema
    SCHEMA_FILE="database/schema.sql"
    
    # Check if mysql client is installed
    if ! command -v mysql &> /dev/null; then
        echo "MySQL client not found. Please install MySQL client:"
        echo "Ubuntu/Debian: sudo apt-get install mysql-client"
        echo "macOS: brew install mysql"
        echo "CentOS/RHEL: sudo yum install mysql"
        exit 1
    fi
    
    # Test database connection first
    echo "Testing database connection..."
    mysql -h"${DB_HOST}" -P"${DB_PORT}" -u"${DB_USER}" -p"${DB_PASSWORD}" -e "SELECT 1;" 2>/dev/null
    if [ $? -ne 0 ]; then
        echo "Database connection failed. Please check:"
        echo "- Host: ${DB_HOST}"
        echo "- Port: ${DB_PORT}"
        echo "- User: ${DB_USER}"
        echo "- Password: [hidden]"
        echo "- Database server is running and accessible"
        exit 1
    fi
    
    # Create database
    echo "Creating database ${DB_NAME}..."
    mysql -h"${DB_HOST}" -P"${DB_PORT}" -u"${DB_USER}" -p"${DB_PASSWORD}" -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\`;"
    
    if [ $? -eq 0 ]; then
        echo "Database created successfully."
        
        # Import schema with verbose output
        echo "Importing schema from ${SCHEMA_FILE}..."
        mysql -h"${DB_HOST}" -P"${DB_PORT}" -u"${DB_USER}" -p"${DB_PASSWORD}" "${DB_NAME}" < "$SCHEMA_FILE" 2>&1
        
        if [ $? -eq 0 ]; then
            echo "Schema imported successfully."
            
            # Run migration for server_type column if migration file exists
            MIGRATION_FILE="migrate-server-type.sql"
            if [ -f "$MIGRATION_FILE" ]; then
                echo "Running server_type migration..."
                mysql -h"${DB_HOST}" -P"${DB_PORT}" -u"${DB_USER}" -p"${DB_PASSWORD}" "${DB_NAME}" < "$MIGRATION_FILE" 2>&1
                echo "Migration completed."
            fi
            
            # Run migration for hardware fields if migration file exists
            HARDWARE_MIGRATION_FILE="migrate-hardware-fields.sql"
            if [ -f "$HARDWARE_MIGRATION_FILE" ]; then
                echo "Running hardware fields migration..."
                mysql -h"${DB_HOST}" -P"${DB_PORT}" -u"${DB_USER}" -p"${DB_PASSWORD}" "${DB_NAME}" < "$HARDWARE_MIGRATION_FILE" 2>&1
                echo "Hardware migration completed."
            fi
            
            # Cleanup old hardware table if cleanup file exists
            CLEANUP_FILE="cleanup-hardware-table.sql"
            if [ -f "$CLEANUP_FILE" ]; then
                echo "Running hardware table cleanup..."
                mysql -h"${DB_HOST}" -P"${DB_PORT}" -u"${DB_USER}" -p"${DB_PASSWORD}" "${DB_NAME}" < "$CLEANUP_FILE" 2>&1
                echo "Hardware table cleanup completed."
            fi
            
            # Verify tables were created
            echo "Verifying tables..."
            mysql -h"${DB_HOST}" -P"${DB_PORT}" -u"${DB_USER}" -p"${DB_PASSWORD}" "${DB_NAME}" -e "SHOW TABLES;"
        else
            echo "Error importing schema. Check the SQL syntax in ${SCHEMA_FILE}"
            exit 1
        fi
    else
        echo "Error creating database ${DB_NAME}."
        echo "This might be due to:"
        echo "- Insufficient privileges"
        echo "- Database already exists"
        echo "- Syntax error in database name"
        exit 1
    fi
}

# Create database and import schema
create_database

echo "Database deployment completed successfully!"
echo "Database: ${DB_NAME} at ${DB_HOST}:${DB_PORT}"