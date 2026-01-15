<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Answer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'question_id',
        'content',
        'is_accepted',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_accepted' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the answer.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the question that this answer belongs to.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Questions::class, 'question_id');
    }

    /**
     * Get all likes for this answer.
     */
    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likable');
    }

    /**
     * Get all comments for this answer.
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Get the count of likes for this answer.
     */
    public function getLikesCountAttribute(): int
    {
        return $this->likes()->where('like', true)->count();
    }

    /**
     * Get the count of dislikes for this answer.
     */
    public function getDislikesCountAttribute(): int
    {
        return $this->likes()->where('like', false)->count();
    }

    /**
     * Get the count of comments for this answer.
     */
    public function getCommentsCountAttribute(): int
    {
        return $this->comments()->count();
    }

    /**
     * Check if a user has liked this answer.
     */
    public function isLikedBy(int $userId): bool
    {
        return $this->likes()
            ->where('user_id', $userId)
            ->where('like', true)
            ->exists();
    }

    /**
     * Check if a user has disliked this answer.
     */
    public function isDislikedBy(int $userId): bool
    {
        return $this->likes()
            ->where('user_id', $userId)
            ->where('like', false)
            ->exists();
    }
}
