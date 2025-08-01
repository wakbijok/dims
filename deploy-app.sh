#!/bin/bash

# App-only deployment script
# This script redeploys only the application without touching the database

echo "Starting app-only deployment..."

# Build and restart only the web container
docker-compose up -d --build web

echo "App-only deployment completed successfully!"
echo "You can access the application at http://localhost:8080"