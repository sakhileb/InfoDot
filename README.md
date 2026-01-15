# InfoDot Q&A Platform - Laravel 11

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3%2F8.4-blue.svg)](https://php.net)
[![Tests](https://img.shields.io/badge/tests-286%20passing-brightgreen.svg)](tests/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

InfoDot is a modern Q&A platform built with Laravel 11, featuring real-time interactions, comprehensive search capabilities, and a rich social experience. This is the modernized version migrated from Laravel 8.

## Features

- **Question & Answer System**: Ask questions, provide answers, and accept the best solutions
- **Step-by-Step Solutions**: Create detailed guides with multiple steps
- **Real-time Updates**: Live notifications and updates using Laravel Reverb
- **Advanced Search**: Full-text search across questions, solutions, and users
- **Social Interactions**: Like, comment, follow users, and build connections
- **Team Collaboration**: Team-based access control and collaboration
- **File Management**: Upload and organize files with media library integration
- **API Access**: RESTful API with Sanctum authentication
- **Responsive Design**: Mobile-friendly interface with Tailwind CSS and DaisyUI

## Technology Stack

### Backend
- **Framework**: Laravel 11.x
- **PHP**: 8.3 or 8.4
- **Database**: MySQL 8.0+
- **Cache/Queue**: Redis
- **Authentication**: Laravel Jetstream 5.x with Sanctum 4.x
- **Real-time**: Laravel Reverb (WebSocket server)
- **Search**: Laravel Scout with Meilisearch/TNTSearch
- **Media**: Spatie Media Library 11.x

### Frontend
- **Build Tool**: Vite
- **CSS**: Tailwind CSS 4.x with DaisyUI
- **JavaScript**: Alpine.js 3.x
- **Components**: Livewire 3.x for reactive components

## Requirements

- PHP 8.3 or 8.4
- Composer 2.x
- Node.js 18+ and NPM
- MySQL 8.0+
- Redis (optional but recommended)

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd infodot-laravel11
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install NPM dependencies
npm install
```

### 3. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Configure Environment Variables

Edit `.env` file with your configuration:

```env
# Application
APP_NAME="InfoDot"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=infodot
DB_USERNAME=root
DB_PASSWORD=

# Redis (for cache and queues)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@infodot.com"
MAIL_FROM_NAME="${APP_NAME}"

# Broadcasting (Reverb)
BROADCAST_DRIVER=reverb
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http

# Search (Scout)
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://127.0.0.1:7700
MEILISEARCH_KEY=

# Queue
QUEUE_CONNECTION=redis
```

### 5. Database Setup

```bash
# Run migrations
php artisan migrate

# Seed database with sample data (optional)
php artisan db:seed
```

### 6. Build Frontend Assets

```bash
# Development build
npm run dev

# Production build
npm run build
```

### 7. Start Services

```bash
# Start Laravel development server
php artisan serve

# Start Reverb WebSocket server (in separate terminal)
php artisan reverb:start

# Start queue worker (in separate terminal)
php artisan queue:work

# Start Vite dev server (in separate terminal, for development)
npm run dev
```

The application will be available at `http://localhost:8000`

## Configuration

### Search Configuration

InfoDot supports multiple search drivers:

**Meilisearch (Recommended)**:
```bash
# Install Meilisearch
# See: https://www.meilisearch.com/docs/learn/getting_started/installation

# Configure in .env
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://127.0.0.1:7700

# Import searchable models
php artisan scout:import "App\Models\Question"
php artisan scout:import "App\Models\Solution"
php artisan scout:import "App\Models\User"
```

**TNTSearch (Alternative)**:
```bash
# Configure in .env
SCOUT_DRIVER=tntsearch

# Import searchable models
php artisan scout:import "App\Models\Question"
php artisan scout:import "App\Models\Solution"
php artisan scout:import "App\Models\User"
```

**MySQL FULLTEXT (Fallback)**:
The application automatically falls back to MySQL FULLTEXT search if Scout is unavailable.

### Broadcasting Configuration

**Laravel Reverb (Default)**:
```bash
# Install Reverb
php artisan reverb:install

# Start Reverb server
php artisan reverb:start
```

**Pusher (Alternative)**:
```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=mt1
```

### File Storage Configuration

**Local Storage (Default)**:
Files are stored in `storage/app/public`. Create symbolic link:
```bash
php artisan storage:link
```

**S3 Storage (Production)**:
```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name
```

## Testing

### Run All Tests

```bash
php artisan test
```

### Run Specific Test Suites

```bash
# Feature tests
php artisan test --testsuite=Feature

# Unit tests
php artisan test --testsuite=Unit

# Specific test file
php artisan test tests/Feature/QuestionManagementTest.php
```

### Test Coverage

```bash
# Generate coverage report
php artisan test --coverage

# Generate HTML coverage report
php artisan test --coverage-html coverage
```

Current test coverage: **286 tests, 1,024 assertions, 100% pass rate**

## API Documentation

### Authentication

All API endpoints require authentication using Sanctum tokens.

**Generate Token**:
```bash
POST /api/tokens/create
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}
```

**Use Token**:
```bash
Authorization: Bearer {your-token}
```

### Endpoints

#### Answers

```bash
# Get answers for a question
GET /api/answers/question/{questionId}

# Create answer
POST /api/answers
{
  "question_id": 1,
  "content": "Answer content"
}

# Delete answer
DELETE /api/answers/{id}

# Toggle like
POST /api/answers/{id}/like

# Add comment
POST /api/answers/{id}/comments
{
  "body": "Comment text"
}

# Get comments
GET /api/answers/{id}/comments

# Toggle acceptance
POST /api/answers/{id}/accept
```

#### User

```bash
# Get authenticated user
GET /api/user
```

### Rate Limiting

API endpoints are rate-limited:
- **Authenticated requests**: 60 requests per minute
- **Unauthenticated requests**: 10 requests per minute

## Deployment

### Production Checklist

1. **Environment Configuration**
   - Set `APP_ENV=production`
   - Set `APP_DEBUG=false`
   - Configure production database
   - Set up Redis for cache and queues
   - Configure mail server
   - Set up search service (Meilisearch)
   - Configure file storage (S3)

2. **Optimize Application**
   ```bash
   # Cache configuration
   php artisan config:cache
   
   # Cache routes
   php artisan route:cache
   
   # Cache views
   php artisan view:cache
   
   # Optimize autoloader
   composer install --optimize-autoloader --no-dev
   
   # Build production assets
   npm run build
   ```

3. **Database Migration**
   ```bash
   # Run migrations
   php artisan migrate --force
   
   # Import search indexes
   php artisan scout:import "App\Models\Question"
   php artisan scout:import "App\Models\Solution"
   php artisan scout:import "App\Models\User"
   ```

4. **Start Services**
   ```bash
   # Queue worker (use supervisor)
   php artisan queue:work --tries=3
   
   # Reverb server (use supervisor)
   php artisan reverb:start
   ```

5. **Security**
   - Enable HTTPS
   - Configure security headers
   - Set up firewall rules
   - Enable rate limiting
   - Regular security updates

### Deployment Scripts

See `docs/DEPLOYMENT_GUIDE.md` for detailed deployment instructions and scripts.

## Migration from Laravel 8

If you're migrating from the Laravel 8 version:

1. **Backup Data**
   ```bash
   # Backup Laravel 8 database
   mysqldump -u root -p infodot_old > backup.sql
   ```

2. **Import Data**
   ```bash
   # Import to Laravel 11 database
   mysql -u root -p infodot < backup.sql
   ```

3. **Migrate Files**
   ```bash
   # Copy storage files
   php artisan migrate:files
   ```

4. **Verify Migration**
   ```bash
   # Run verification tests
   php artisan test
   ```

See `docs/MIGRATION_GUIDE.md` for complete migration instructions.

## Performance Optimization

### Caching Strategy

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Clear all caches
php artisan optimize:clear
```

### Query Optimization

- All controllers use `EagerLoadingOptimizer` trait to prevent N+1 queries
- Database indexes on frequently queried columns
- Redis caching for frequently accessed data
- Query result caching for expensive operations

### Asset Optimization

- Vite for optimized asset bundling
- Lazy loading for images
- Code splitting for JavaScript
- CSS purging with Tailwind

## Monitoring

### Laravel Telescope

```bash
# Install Telescope (development only)
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

Access at: `http://localhost:8000/telescope`

### Laravel Horizon

```bash
# Install Horizon (for queue monitoring)
composer require laravel/horizon
php artisan horizon:install
php artisan migrate
```

Access at: `http://localhost:8000/horizon`

### Logs

Application logs are stored in `storage/logs/laravel.log`

```bash
# View logs
tail -f storage/logs/laravel.log

# Clear logs
> storage/logs/laravel.log
```

## Troubleshooting

### Common Issues

**Database Connection Error**:
```bash
# Check database credentials in .env
# Ensure MySQL is running
# Test connection: php artisan migrate:status
```

**Queue Jobs Not Processing**:
```bash
# Ensure queue worker is running
php artisan queue:work

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

**Search Not Working**:
```bash
# Check Scout configuration
php artisan scout:status

# Reimport indexes
php artisan scout:flush "App\Models\Question"
php artisan scout:import "App\Models\Question"
```

**WebSocket Connection Failed**:
```bash
# Ensure Reverb is running
php artisan reverb:start

# Check Reverb configuration in .env
# Verify firewall allows WebSocket connections
```

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Code Style

```bash
# Run PHP CS Fixer
./vendor/bin/php-cs-fixer fix

# Run PHPStan
./vendor/bin/phpstan analyse
```

## Security

If you discover a security vulnerability, please email security@infodot.com. All security vulnerabilities will be promptly addressed.

## License

The InfoDot platform is open-sourced software licensed under the [MIT license](LICENSE).

## Support

- **Documentation**: See `docs/` directory
- **Issues**: GitHub Issues
- **Email**: support@infodot.com

## Acknowledgments

- Built with [Laravel](https://laravel.com)
- UI components from [DaisyUI](https://daisyui.com)
- Icons from [Heroicons](https://heroicons.com)
- Migrated from Laravel 8 to Laravel 11 in January 2026

---

**Version**: 2.0.0 (Laravel 11)  
**Last Updated**: January 15, 2026
