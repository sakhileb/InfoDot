#!/bin/bash

###############################################################################
# InfoDot Post-Deployment Verification Script
# 
# This script performs automated verification checks after deployment.
# It tests critical functionality and reports any issues.
#
# Usage: ./verify-deployment.sh [environment]
# Example: ./verify-deployment.sh production
###############################################################################

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Configuration
ENVIRONMENT=${1:-production}
APP_URL=${APP_URL:-"https://infodot.com"}
API_URL="${APP_URL}/api"
TIMEOUT=10
PASSED=0
FAILED=0
WARNINGS=0

# Functions
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ✗ FAILED:${NC} $1"
    FAILED=$((FAILED + 1))
}

success() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] ✓ PASSED:${NC} $1"
    PASSED=$((PASSED + 1))
}

warning() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] ⚠ WARNING:${NC} $1"
    WARNINGS=$((WARNINGS + 1))
}

info() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')] INFO:${NC} $1"
}

check_http() {
    local url=$1
    local expected_code=${2:-200}
    local description=$3
    
    local http_code=$(curl -s -o /dev/null -w "%{http_code}" --max-time $TIMEOUT "$url")
    
    if [ "$http_code" -eq "$expected_code" ]; then
        success "$description (HTTP $http_code)"
        return 0
    else
        error "$description (Expected HTTP $expected_code, got $http_code)"
        return 1
    fi
}

check_ssl() {
    local domain=$1
    
    local expiry=$(echo | openssl s_client -servername "$domain" -connect "$domain:443" 2>/dev/null | openssl x509 -noout -enddate 2>/dev/null | cut -d= -f2)
    
    if [ -n "$expiry" ]; then
        local expiry_epoch=$(date -d "$expiry" +%s 2>/dev/null || date -j -f "%b %d %H:%M:%S %Y %Z" "$expiry" +%s 2>/dev/null)
        local now_epoch=$(date +%s)
        local days_until_expiry=$(( ($expiry_epoch - $now_epoch) / 86400 ))
        
        if [ $days_until_expiry -gt 30 ]; then
            success "SSL certificate valid (expires in $days_until_expiry days)"
        elif [ $days_until_expiry -gt 7 ]; then
            warning "SSL certificate expires soon (in $days_until_expiry days)"
        else
            error "SSL certificate expires very soon (in $days_until_expiry days)"
        fi
    else
        error "Could not verify SSL certificate"
    fi
}

check_security_headers() {
    local url=$1
    
    local headers=$(curl -s -I "$url")
    
    if echo "$headers" | grep -q "X-Frame-Options"; then
        success "X-Frame-Options header present"
    else
        warning "X-Frame-Options header missing"
    fi
    
    if echo "$headers" | grep -q "X-Content-Type-Options"; then
        success "X-Content-Type-Options header present"
    else
        warning "X-Content-Type-Options header missing"
    fi
    
    if echo "$headers" | grep -q "X-XSS-Protection"; then
        success "X-XSS-Protection header present"
    else
        warning "X-XSS-Protection header missing"
    fi
}

check_response_time() {
    local url=$1
    local max_time=${2:-2}
    local description=$3
    
    local response_time=$(curl -s -o /dev/null -w "%{time_total}" --max-time $TIMEOUT "$url")
    local response_time_ms=$(echo "$response_time * 1000" | bc | cut -d. -f1)
    local max_time_ms=$(echo "$max_time * 1000" | bc | cut -d. -f1)
    
    if [ $response_time_ms -lt $max_time_ms ]; then
        success "$description (${response_time_ms}ms < ${max_time_ms}ms)"
    else
        warning "$description (${response_time_ms}ms >= ${max_time_ms}ms)"
    fi
}

# Start verification
log "========================================="
log "InfoDot Post-Deployment Verification"
log "========================================="
info "Environment: $ENVIRONMENT"
info "Application URL: $APP_URL"
info "API URL: $API_URL"
log "========================================="

# Section 1: Basic Connectivity
log ""
log "Section 1: Basic Connectivity"
log "----------------------------"

check_http "$APP_URL" 200 "Homepage accessible"
check_http "$APP_URL/login" 200 "Login page accessible"
check_http "$APP_URL/register" 200 "Register page accessible"
check_http "$APP_URL/questions" 200 "Questions page accessible"
check_http "$APP_URL/solutions" 200 "Solutions page accessible"

# Section 2: SSL/TLS
log ""
log "Section 2: SSL/TLS Configuration"
log "--------------------------------"

DOMAIN=$(echo "$APP_URL" | sed -e 's|^[^/]*//||' -e 's|/.*$||')
check_ssl "$DOMAIN"

# Section 3: Security Headers
log ""
log "Section 3: Security Headers"
log "--------------------------"

check_security_headers "$APP_URL"

# Section 4: Performance
log ""
log "Section 4: Performance"
log "---------------------"

check_response_time "$APP_URL" 2 "Homepage response time"
check_response_time "$APP_URL/questions" 2 "Questions page response time"
check_response_time "$APP_URL/solutions" 2 "Solutions page response time"

# Section 5: API Endpoints
log ""
log "Section 5: API Endpoints"
log "-----------------------"

# Note: These checks require authentication, so they may fail without a valid token
# Uncomment and configure if you have a test token

# check_http "$API_URL/user" 401 "API authentication required"
info "API endpoint checks require authentication (skipped)"

# Section 6: Static Assets
log ""
log "Section 6: Static Assets"
log "-----------------------"

check_http "$APP_URL/favicon.ico" 200 "Favicon accessible"
check_http "$APP_URL/robots.txt" 200 "Robots.txt accessible"

# Section 7: Error Pages
log ""
log "Section 7: Error Pages"
log "---------------------"

check_http "$APP_URL/nonexistent-page-12345" 404 "404 page works"

# Section 8: Server Health
log ""
log "Section 8: Server Health"
log "-----------------------"

if command -v supervisorctl &> /dev/null; then
    if supervisorctl status infodot-worker:* | grep -q RUNNING; then
        success "Queue workers running"
    else
        error "Queue workers not running"
    fi
    
    if supervisorctl status infodot-reverb | grep -q RUNNING; then
        success "Reverb server running"
    else
        error "Reverb server not running"
    fi
else
    info "Supervisor not available (skipped)"
fi

# Section 9: Database Connectivity
log ""
log "Section 9: Database Connectivity"
log "--------------------------------"

if command -v php &> /dev/null; then
    if php artisan tinker --execute="DB::connection()->getPdo();" &> /dev/null; then
        success "Database connection working"
    else
        error "Database connection failed"
    fi
else
    info "PHP not available (skipped)"
fi

# Section 10: Cache Connectivity
log ""
log "Section 10: Cache Connectivity"
log "------------------------------"

if command -v redis-cli &> /dev/null; then
    if redis-cli ping | grep -q PONG; then
        success "Redis connection working"
    else
        error "Redis connection failed"
    fi
else
    info "Redis CLI not available (skipped)"
fi

# Section 11: File Permissions
log ""
log "Section 11: File Permissions"
log "---------------------------"

if [ -d "/var/www/infodot-laravel11" ]; then
    if [ -w "/var/www/infodot-laravel11/storage" ]; then
        success "Storage directory writable"
    else
        error "Storage directory not writable"
    fi
    
    if [ -w "/var/www/infodot-laravel11/bootstrap/cache" ]; then
        success "Bootstrap cache directory writable"
    else
        error "Bootstrap cache directory not writable"
    fi
else
    info "Application directory not found (skipped)"
fi

# Section 12: Log Files
log ""
log "Section 12: Log Files"
log "--------------------"

if [ -d "/var/www/infodot-laravel11/storage/logs" ]; then
    ERROR_COUNT=$(grep -c "ERROR" /var/www/infodot-laravel11/storage/logs/laravel.log 2>/dev/null || echo 0)
    
    if [ $ERROR_COUNT -eq 0 ]; then
        success "No errors in application log"
    elif [ $ERROR_COUNT -lt 10 ]; then
        warning "$ERROR_COUNT errors found in application log"
    else
        error "$ERROR_COUNT errors found in application log"
    fi
else
    info "Log directory not found (skipped)"
fi

# Summary
log ""
log "========================================="
log "Verification Summary"
log "========================================="
success "Passed: $PASSED"
warning "Warnings: $WARNINGS"
error "Failed: $FAILED"
log "========================================="

# Exit code
if [ $FAILED -eq 0 ]; then
    log "✓ All critical checks passed!"
    exit 0
else
    log "✗ Some checks failed. Please review the results above."
    exit 1
fi
