#!/bin/bash

###############################################################################
# InfoDot Data Migration Script
# 
# This script migrates data from Laravel 8 to Laravel 11 database.
# It handles database export, import, and verification.
#
# Usage: ./migrate-data.sh [options]
# Options:
#   --source-db=NAME      Source database name (default: infodot_laravel8)
#   --target-db=NAME      Target database name (default: infodot_laravel11)
#   --backup-dir=PATH     Backup directory (default: /backups/infodot)
#   --verify              Verify data after migration
#   --dry-run             Show what would be done without executing
###############################################################################

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Default configuration
SOURCE_DB="infodot_laravel8"
TARGET_DB="infodot_laravel11"
BACKUP_DIR="/backups/infodot"
VERIFY=false
DRY_RUN=false
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Parse arguments
for arg in "$@"; do
    case $arg in
        --source-db=*)
            SOURCE_DB="${arg#*=}"
            ;;
        --target-db=*)
            TARGET_DB="${arg#*=}"
            ;;
        --backup-dir=*)
            BACKUP_DIR="${arg#*=}"
            ;;
        --verify)
            VERIFY=true
            ;;
        --dry-run)
            DRY_RUN=true
            ;;
        --help)
            echo "Usage: $0 [options]"
            echo "Options:"
            echo "  --source-db=NAME      Source database name"
            echo "  --target-db=NAME      Target database name"
            echo "  --backup-dir=PATH     Backup directory"
            echo "  --verify              Verify data after migration"
            echo "  --dry-run             Show what would be done"
            exit 0
            ;;
        *)
            echo "Unknown option: $arg"
            exit 1
            ;;
    esac
done

# Functions
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ERROR:${NC} $1"
    exit 1
}

warning() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] WARNING:${NC} $1"
}

info() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')] INFO:${NC} $1"
}

# Check if running in dry-run mode
if [ "$DRY_RUN" = true ]; then
    warning "Running in DRY-RUN mode - no changes will be made"
fi

# Start migration
log "========================================="
log "InfoDot Data Migration"
log "========================================="
info "Source Database: $SOURCE_DB"
info "Target Database: $TARGET_DB"
info "Backup Directory: $BACKUP_DIR"
log "========================================="

# Step 1: Verify source database exists
log "Step 1: Verifying source database..."
if ! mysql -e "USE $SOURCE_DB" 2>/dev/null; then
    error "Source database '$SOURCE_DB' does not exist"
fi
log "Source database verified"

# Step 2: Verify target database exists
log "Step 2: Verifying target database..."
if ! mysql -e "USE $TARGET_DB" 2>/dev/null; then
    error "Target database '$TARGET_DB' does not exist"
fi
log "Target database verified"

# Step 3: Create backup directory
log "Step 3: Creating backup directory..."
if [ "$DRY_RUN" = false ]; then
    mkdir -p "$BACKUP_DIR"
fi
log "Backup directory ready: $BACKUP_DIR"

# Step 4: Backup target database (before migration)
log "Step 4: Backing up target database..."
BACKUP_FILE="$BACKUP_DIR/pre_migration_${TARGET_DB}_${TIMESTAMP}.sql"
if [ "$DRY_RUN" = false ]; then
    mysqldump --single-transaction --routines --triggers "$TARGET_DB" > "$BACKUP_FILE"
    gzip "$BACKUP_FILE"
    log "Target database backed up to: ${BACKUP_FILE}.gz"
else
    info "Would backup to: ${BACKUP_FILE}.gz"
fi

# Step 5: Export source database
log "Step 5: Exporting source database..."
EXPORT_FILE="$BACKUP_DIR/export_${SOURCE_DB}_${TIMESTAMP}.sql"
if [ "$DRY_RUN" = false ]; then
    mysqldump --no-create-info \
              --skip-add-drop-table \
              --complete-insert \
              --single-transaction \
              "$SOURCE_DB" \
              users questions answers solutions steps \
              likes comments associates followers \
              teams team_user team_invitations \
              files folders objs \
              > "$EXPORT_FILE"
    log "Source database exported to: $EXPORT_FILE"
else
    info "Would export to: $EXPORT_FILE"
fi

# Step 6: Get record counts from source
log "Step 6: Getting source database statistics..."
if [ "$DRY_RUN" = false ]; then
    SOURCE_USERS=$(mysql -N -e "SELECT COUNT(*) FROM users" "$SOURCE_DB")
    SOURCE_QUESTIONS=$(mysql -N -e "SELECT COUNT(*) FROM questions" "$SOURCE_DB")
    SOURCE_ANSWERS=$(mysql -N -e "SELECT COUNT(*) FROM answers" "$SOURCE_DB")
    SOURCE_SOLUTIONS=$(mysql -N -e "SELECT COUNT(*) FROM solutions" "$SOURCE_DB")
    SOURCE_STEPS=$(mysql -N -e "SELECT COUNT(*) FROM steps" "$SOURCE_DB")
    
    info "Source counts:"
    info "  Users: $SOURCE_USERS"
    info "  Questions: $SOURCE_QUESTIONS"
    info "  Answers: $SOURCE_ANSWERS"
    info "  Solutions: $SOURCE_SOLUTIONS"
    info "  Steps: $SOURCE_STEPS"
else
    info "Would display source counts"
fi

# Step 7: Clear target database tables
log "Step 7: Clearing target database tables..."
if [ "$DRY_RUN" = false ]; then
    mysql "$TARGET_DB" << EOF
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE users;
TRUNCATE TABLE questions;
TRUNCATE TABLE answers;
TRUNCATE TABLE solutions;
TRUNCATE TABLE steps;
TRUNCATE TABLE likes;
TRUNCATE TABLE comments;
TRUNCATE TABLE associates;
TRUNCATE TABLE followers;
TRUNCATE TABLE teams;
TRUNCATE TABLE team_user;
TRUNCATE TABLE team_invitations;
TRUNCATE TABLE files;
TRUNCATE TABLE folders;
TRUNCATE TABLE objs;
SET FOREIGN_KEY_CHECKS = 1;
EOF
    log "Target database tables cleared"
else
    info "Would clear target database tables"
fi

# Step 8: Import data to target database
log "Step 8: Importing data to target database..."
if [ "$DRY_RUN" = false ]; then
    mysql "$TARGET_DB" < "$EXPORT_FILE"
    log "Data imported successfully"
else
    info "Would import data from: $EXPORT_FILE"
fi

# Step 9: Verify data migration
if [ "$VERIFY" = true ] && [ "$DRY_RUN" = false ]; then
    log "Step 9: Verifying data migration..."
    
    TARGET_USERS=$(mysql -N -e "SELECT COUNT(*) FROM users" "$TARGET_DB")
    TARGET_QUESTIONS=$(mysql -N -e "SELECT COUNT(*) FROM questions" "$TARGET_DB")
    TARGET_ANSWERS=$(mysql -N -e "SELECT COUNT(*) FROM answers" "$TARGET_DB")
    TARGET_SOLUTIONS=$(mysql -N -e "SELECT COUNT(*) FROM solutions" "$TARGET_DB")
    TARGET_STEPS=$(mysql -N -e "SELECT COUNT(*) FROM steps" "$TARGET_DB")
    
    info "Target counts:"
    info "  Users: $TARGET_USERS"
    info "  Questions: $TARGET_QUESTIONS"
    info "  Answers: $TARGET_ANSWERS"
    info "  Solutions: $TARGET_SOLUTIONS"
    info "  Steps: $TARGET_STEPS"
    
    # Compare counts
    ERRORS=0
    
    if [ "$SOURCE_USERS" != "$TARGET_USERS" ]; then
        error "User count mismatch: Source=$SOURCE_USERS, Target=$TARGET_USERS"
        ERRORS=$((ERRORS + 1))
    fi
    
    if [ "$SOURCE_QUESTIONS" != "$TARGET_QUESTIONS" ]; then
        error "Question count mismatch: Source=$SOURCE_QUESTIONS, Target=$TARGET_QUESTIONS"
        ERRORS=$((ERRORS + 1))
    fi
    
    if [ "$SOURCE_ANSWERS" != "$TARGET_ANSWERS" ]; then
        error "Answer count mismatch: Source=$SOURCE_ANSWERS, Target=$TARGET_ANSWERS"
        ERRORS=$((ERRORS + 1))
    fi
    
    if [ "$SOURCE_SOLUTIONS" != "$TARGET_SOLUTIONS" ]; then
        error "Solution count mismatch: Source=$SOURCE_SOLUTIONS, Target=$TARGET_SOLUTIONS"
        ERRORS=$((ERRORS + 1))
    fi
    
    if [ "$SOURCE_STEPS" != "$TARGET_STEPS" ]; then
        error "Step count mismatch: Source=$SOURCE_STEPS, Target=$TARGET_STEPS"
        ERRORS=$((ERRORS + 1))
    fi
    
    if [ $ERRORS -eq 0 ]; then
        log "✓ Data verification passed - all counts match"
    else
        error "Data verification failed with $ERRORS errors"
    fi
else
    info "Skipping verification (use --verify to enable)"
fi

# Step 10: Cleanup
log "Step 10: Cleaning up..."
if [ "$DRY_RUN" = false ]; then
    # Keep export file for reference
    info "Export file kept at: $EXPORT_FILE"
    
    # Remove old backups (keep last 10)
    cd "$BACKUP_DIR"
    ls -t export_*.sql 2>/dev/null | tail -n +11 | xargs -r rm --
    ls -t pre_migration_*.sql.gz 2>/dev/null | tail -n +11 | xargs -r rm --
    
    log "Cleanup completed"
else
    info "Would clean up old backups"
fi

# Migration complete
log "========================================="
log "Data migration completed successfully!"
log "========================================="
info "Source: $SOURCE_DB"
info "Target: $TARGET_DB"
info "Backup: ${BACKUP_FILE}.gz"
info "Export: $EXPORT_FILE"
log "========================================="

# Next steps
echo ""
log "Next steps:"
echo "1. Verify data integrity in target database"
echo "2. Run application tests: php artisan test"
echo "3. Check search indexes: php artisan scout:import"
echo "4. Verify file migrations"
echo "5. Test application functionality"
echo ""

exit 0
