#!/bin/bash

# Exit immediately if a command exits with a non-zero status
set -e

echo "🚀 Starting Automated Deployment..."

# 1. Pull latest changes from Git
echo "📥 Pulling latest code from repository..."
git pull origin main

# 2. Run Database Migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# 3. Seed Bank Soal Sesi 5
echo "🌱 Seeding Bank Soal Sesi 5..."
php artisan db:seed --class=BankSoalSesi5Seeder --force

# 4. Clear and rebuild Laravel caches
echo "🧹 Rebuilding application cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "🎉 Deployment successfully completed!"
