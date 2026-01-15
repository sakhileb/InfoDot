#!/bin/bash

###############################################################################
# InfoDot Laravel 11 Deployment Script
# 
# This script automates the deployment process for the InfoDot application.
# It handles code updates, dependency installation, database migrations,
# cache optimization, and service restarts.
#
# Usage: ./deploy.sh [environment]
# Example: ./deploy.sh production
###############################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
ENVIRONMENT=${1:-production}
APP_DIR="/var/www/infodot-laravel11"
BACKUP_DIR="/backups/infodot"
LOG_FILE="/var/log/infodot-deploy.log"

# Functions
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ERROR:${NC} $1" | tee -a "$LOG_FILE"
    exit 1
}

warning() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] WARNING:${NC} $1" | tee -a "$LOG_FILE"
}

# Check if running as correct user
if [ "$EUID" -eq 0 ]; then 
    error "Do not run this script as root. Run as the application user."
fi

# Start deployment
log "========================================="
log "Starting deployment to $ENVIRONMENT"
log "========================================="

# Step 1: Backup current state
log "Step 1: Creating backup..."
BACKUP_NAME="backup_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"

# Backup database
log "Backing up database..."
php "$APP_DIR/artisan" backup:database "$BACKUP_DIR/$BACKUP_NAME.sql" || warning "Database backup failed"

# Backup .env file
log "Backing up environment file..."
cp "$APP_DIR/.env" "$BACKUP_DIR/$BACKUP_NAME.env" || warning "Environment backup failed"

# Step 2: Enable maintenance mode
log "Step 2: Enabling maintenance mode..."
php "$APP_DIR/artisan" down --retry=60 || error "Failed to enable maintenance mode"

# Step 3: Pull latest code
log "Step 3: Pulling latest code from repository..."
cd "$APP_DIR"
git fetch origin || error "Failed to fetch from repository"
git reset --hard origin/main || error "Failed to reset to origin/main"

# Step 4: Install/Update dependencies
log "Step 4: Installing dependencies..."

# Composer dependencies
log "Installing Composer dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev || error "Composer install failed"

# NPM dependencies
log "Installing NPM dependencies..."
npm ci --production || error "NPM install failed"

# Step 5: Build frontend assets
log "Step 5: Building frontend assets..."
npm run build || error "Asset build failed"

# Step 6: Run database migrations
log "Step 6: Running database migrations..."
php artisan migrate --force || error "Database migration failed"

# Step 7: Clear and optimize caches
log "Step 7: Optimizing application..."

# Clear all caches
log "Clearing caches..."
php artisan optimize:clear || warning "Cache clear failed"

# Cache configuration
log "Caching configuration..."
php artisan config:cache || warning "Config cache failed"

# Cache routes
log "Caching routes..."
php artisan route:cache || warning "Route cache failed"

# Cache views
log "Caching views..."
php artisan view:cache || warning "View cache failed"

# Cache events
log "Caching events..."
php artisan event:cache || warning "Event cache failed"

# Step 8: Update search indexes
log "Step 8: Updating search indexes..."
php artisan scout:import "App\Models\Question" || warning "Question index import failed"
php artisan scout:import "App\Models\Solution" || warning "Solution index import failed"
php artisan scout:import "App\Models\User" || warning "User index import failed"

# Step 9: Restart services
log "Step 9: Restarting services..."

# Restart queue workers
log "Restarting queue workers..."
php artisan queue:restart || warning "Queue restart failed"

# Restart Reverb server (if using supervisor)
if command -v supervisorctl &> /dev/null; then
    log "Restarting Reverb server..."
    sudo supervisorctl restart infodot-reverb || warning "Reverb restart failed"
    
    log "Restarting queue workers..."
    sudo supervisorctl restart infodot-worker:* || warning "Worker restart failed"
fi

# Step 10: Disable maintenance mode
log "Step 10: Disabling maintenance mode..."
php artisan up || error "Failed to disable maintenance mode"

# Step 11: Verify deployment
log "Step 11: Verifying deployment..."

# Check if application is responding
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost)
if [ "$HTTP_CODE" -eq 200 ]; then
    log "Application is responding correctly (HTTP $HTTP_CODE)"
else
    warning "Application returned HTTP $HTTP_CODE"
fi

# Check queue workers
if command -v supervisorctl &> /dev/null; then
    WORKER_STATUS=$(sudo supervisorctl status infodot-worker:* | grep -c RUNNING || true)
    log "Queue workers running: $WORKER_STATUS"
fi

# Step 12: Cleanup
log "Step 12: Cleaning up..."

# Remove old backups (keep last 10)
log "Removing old backups..."
cd "$BACKUP_DIR"
ls -t | tail -n +11 | xargs -r rm -- || warning "Backup cleanup failed"

# Deployment complete
log "========================================="
log "Deployment completed successfully!"
log "========================================="
log "Environment: $ENVIRONMENT"
log "Backup: $BACKUP_DIR/$BACKUP_NAME"
log "Log: $LOG_FILE"
log "========================================="

# Send notification (optional)
if [ -n "${SLACK_WEBHOOK_URL:-}" ]; then
    curl -X POST -H 'Content-type: application/json' \
        --data "{\"text\":\"✅ InfoDot deployment to $ENVIRONMENT completed successfully\"}" \
        "$SLACK_WEBHOOK_URL" || warning "Slack notification failed"
fi

exit 0
