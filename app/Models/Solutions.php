<?php

namespace App\Models;

use App\Models\Traits\Cacheable;
use App\Models\Traits\Search;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Solutions extends Model implements HasMedia
{
    use HasFactory;
    use SoftDeletes;
    use Search;
    use InteractsWithMedia;
    use Cacheable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'solution_title',
        'solution_description',
        'tags',
        'duration',
        'duration_type',
        'steps',
    ];

    /**
     * The searchable attributes for full-text search.
     *
     * @var array<int, string>
     */
    protected $searchable = [
        'solution_title',
        'solution_description',
        'tags',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'duration' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the solution.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all steps for the solution.
     */
    public function steps(): HasMany
    {
        return $this->hasMany(Steps::class, 'solution_id')->orderBy('created_at');
    }

    /**
     * Get all likes for the solution.
     */
    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likable');
    }

    /**
     * Get all comments for the solution.
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Register media collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments')
            ->useDisk('media');

        $this->addMediaCollection('images')
            ->useDisk('media')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

        $this->addMediaCollection('videos')
            ->useDisk('media')
            ->acceptsMimeTypes(['video/mp4', 'video/mpeg', 'video/quicktime']);
    }

    /**
     * Clear cache for this solution
     */
    public function clearModelCache(): void
    {
        parent::clearModelCache();
        
        // Clear solution-specific caches
        $this->invalidateCacheTags(['solutions', 'popular']);
    }

    /**
     * Get cache tags for solutions
     */
    public function getCacheTags(): array
    {
        return ['solutions'];
    }
}
