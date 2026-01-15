# File Migration Guide

This guide explains how to migrate files from the Laravel 8 InfoDot application to the Laravel 11 version using Spatie Media Library.

## Overview

The Laravel 11 version uses Spatie Media Library for file management, which provides:
- Better file organization
- Automatic file conversions
- Multiple storage driver support
- Media collections for different file types
- Automatic cleanup on model deletion

## Prerequisites

1. Ensure the media library migration has been run:
   ```bash
   php artisan migrate
   ```

2. Ensure the storage link is created:
   ```bash
   php artisan storage:link
   ```

3. Verify the media disk is configured in `config/filesystems.php`

## Migration Methods

### Method 1: Using the Migration Command (Recommended)

The `files:migrate` command will automatically migrate files from the old storage to the new Media Library system.

#### Dry Run (Preview)
```bash
php artisan files:migrate --dry-run
```

#### Full Migration
```bash
php artisan files:migrate
```

#### Custom Source Path
```bash
php artisan files:migrate --source-path=/path/to/laravel8/storage/app/public
```

### Method 2: Manual Migration

If you prefer to migrate files manually:

1. **Copy files to new storage:**
   ```bash
   cp -r /path/to/laravel8/storage/app/public/* storage/app/public/media/
   ```

2. **Update database records:**
   - The File model will automatically handle media library associations
   - Run the migration command to update database references

3. **Verify file access:**
   - Check that files are accessible via the media library
   - Test file downloads through the application

## File Storage Structure

### Old Structure (Laravel 8)
```
storage/app/public/
├── files/
│   ├── document1.pdf
│   └── image1.jpg
└── uploads/
    └── user_files/
```

### New Structure (Laravel 11)
```
storage/app/public/media/
├── 1/  (model ID)
│   ├── document1.pdf
│   └── conversions/
└── 2/
    └── image1.jpg
```

## Media Collections

The following media collections are configured:

### File Model
- **files**: General file uploads (10MB max)

### User Model
- **attachments**: General user attachments
- **documents**: PDF and Word documents only

### Questions Model
- **attachments**: Question attachments
- **images**: Image files only (JPEG, PNG, GIF, WebP)

### Solutions Model
- **attachments**: Solution attachments
- **images**: Image files only
- **videos**: Video files (MP4, MPEG, QuickTime)

## File Upload Validation

Default validation rules:
- Maximum file size: 10MB
- Allowed types: jpg, jpeg, png, gif, pdf, doc, docx, xls, xlsx, ppt, pptx, txt, zip, rar

These can be customized in the respective controllers.

## API Endpoints

### File Management
- `POST /files` - Upload a file
- `GET /files` - List user's files
- `GET /files/{file}/download` - Download a file
- `DELETE /files/{file}` - Delete a file

### Folder Management
- `POST /folders` - Create a folder
- `GET /folders` - List user's folders
- `PUT /folders/{folder}` - Update a folder
- `DELETE /folders/{folder}` - Delete a folder

## Troubleshooting

### Files not accessible after migration
1. Verify storage link: `php artisan storage:link`
2. Check file permissions: `chmod -R 755 storage/app/public/media`
3. Verify APP_URL in `.env` matches your domain

### Migration command fails
1. Check source path exists and is readable
2. Verify database connection
3. Ensure sufficient disk space
4. Check file permissions

### Media library errors
1. Verify GD or Imagick is installed for image processing
2. Check `config/media-library.php` configuration
3. Verify disk configuration in `config/filesystems.php`

## Post-Migration Verification

1. **Test file uploads:**
   - Upload a file through the application
   - Verify it appears in the media library
   - Test file download

2. **Verify existing files:**
   - Check that migrated files are accessible
   - Test file downloads for old files
   - Verify file metadata is correct

3. **Check storage usage:**
   ```bash
   du -sh storage/app/public/media
   ```

4. **Test file deletion:**
   - Delete a file through the application
   - Verify it's removed from storage
   - Check database record is deleted

## Configuration

### Environment Variables

Add to `.env`:
```env
MEDIA_DISK=media
IMAGE_DRIVER=gd
QUEUE_CONVERSIONS_BY_DEFAULT=true
```

### Storage Disk Configuration

The media disk is configured in `config/filesystems.php`:
```php
'media' => [
    'driver' => 'local',
    'root' => storage_path('app/public/media'),
    'url' => env('APP_URL').'/storage/media',
    'visibility' => 'public',
],
```

## Best Practices

1. **Always backup before migration:**
   ```bash
   tar -czf storage_backup.tar.gz storage/
   ```

2. **Test in staging first:**
   - Run migration on staging environment
   - Verify all files are accessible
   - Test file operations

3. **Monitor disk space:**
   - Media library stores original files
   - Conversions create additional files
   - Plan for increased storage needs

4. **Use queues for large files:**
   - Enable queue processing for conversions
   - Configure `QUEUE_CONVERSIONS_BY_DEFAULT=true`

5. **Regular cleanup:**
   - Remove orphaned media files
   - Clean up temporary files
   - Monitor storage usage

## Support

For issues or questions:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Review Spatie Media Library documentation
3. Check file permissions and ownership
4. Verify database migrations are complete
