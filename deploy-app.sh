#!/bin/bash

# App-only deployment script
# This script redeploys only the application without touching the database

echo "Starting app-only deployment..."

# Load environment variables from .env file
if [ -f .env ]; then
    export $(cat .env | xargs)
    echo "Loaded environment variables from .env"
else
    echo "Warning: .env file not found. Using default values."
fi

# Build and restart only the web container
docker-compose up -d --build web

echo "App-only deployment completed successfully!"
echo "You can access the application at http://localhost:8080"