#!/bin/bash

set -e

# Check for Docker
if ! command -v docker &> /dev/null; then
  echo "❌ Docker is not installed. Please install Docker and try again."
  exit 1
fi

# Check for Docker Compose (both plugin and legacy)
if ! docker compose version &> /dev/null && ! command -v docker-compose &> /dev/null; then
  echo "❌ Docker Compose is not installed. Please install Docker Compose and try again."
  exit 1
fi

# Check if Docker daemon is running
if ! docker info &> /dev/null; then
  echo "❌ Docker daemon is not running."
  exit 1
fi

# Fix permissions
echo "🔧 Fixing storage folder permissions..."
sudo chown -R 33:33 ./storage
sudo chmod -R ug+rwX ./storage

# Check if any containers are running from your project (docker-compose)
if docker compose ps -q | grep -q .; then
  echo "🛑 Containers are running. Taking them down..."
  
  # Prefer 'docker compose' if available
  if docker compose version &> /dev/null; then
    docker compose down -v
    rm -rf .database
  else
    docker-compose down -v
    rm -rf .database
  fi
else
  echo "✅ No running containers to stop."
fi

# Launch containers
echo "🚀 Starting Docker containers..."

# Prefer 'docker compose' if available, else fallback to 'docker-compose'
if docker compose version &> /dev/null; then
  docker compose up -d --build
else
  docker-compose up -d --build
fi

echo "✅ All done!"
