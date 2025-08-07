#!/bin/bash

# Full installation script (for initial setup)
# This script handles both Docker setup and database deployment

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

# Run database deployment
echo "Setting up database..."
./deploy-db.sh "$DB_HOST" "$DB_PORT" "$DB_USER" "$DB_NAME" "$DB_PASSWORD"

echo "Full installation completed successfully!"
echo "You can access the application at http://localhost:8080"