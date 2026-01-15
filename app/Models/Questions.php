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

class Questions extends Model implements HasMedia
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
        'question',
        'description',
        'tags',
        'status',
    ];

    /**
     * The searchable attributes for full-text search.
     *
     * @var array<int, string>
     */
    protected $searchable = [
        'question',
        'description',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the question.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all answers for the question.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class, 'question_id');
    }

    /**
     * Get all likes for the question.
     */
    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likable');
    }

    /**
     * Get all comments for the question.
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
    }

    /**
     * Clear cache for this question
     */
    public function clearModelCache(): void
    {
        parent::clearModelCache();
        
        // Clear question-specific caches
        $this->invalidateCacheTags(['questions', 'popular', 'recent']);
    }

    /**
     * Get cache tags for questions
     */
    public function getCacheTags(): array
    {
        return ['questions'];
    }
}
