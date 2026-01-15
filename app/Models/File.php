<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class File extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = ['name', 'size', 'path', 'uuid', 'user_id'];

    protected $casts = [
        'size' => 'integer',
    ];

    /**
     * Get the user that owns the file.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the objectable relationship.
     */
    public function objectable()
    {
        return $this->morphOne(Obj::class, 'objectable');
    }

    /**
     * Format file size for human readability.
     */
    public function sizeForHumans(): string
    {
        $bytes = $this->size;
        $units = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Register media collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('files')
            ->useDisk('media');
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });

        static::deleting(function ($model) {
            // Delete associated media
            $model->clearMediaCollection('files');
            
            // Delete file from storage if path exists
            if ($model->path && Storage::disk('local')->exists($model->path)) {
                Storage::disk('local')->delete($model->path);
            }
        });
    }
}
