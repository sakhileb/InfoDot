<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Traits\Cacheable;
use App\Models\Traits\Search;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements HasMedia
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use Search;
    use InteractsWithMedia;
    use Cacheable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The searchable attributes for full-text search.
     *
     * @var array<int, string>
     */
    protected $searchable = [
        'name',
        'email',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's avatar URL using Gravatar.
     */
    public function avatar(): string
    {
        return 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($this->email))) . '?d=mp';
    }

    /**
     * Get all likes created by the user.
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Get all questions created by the user.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Questions::class);
    }

    /**
     * Get all answers created by the user.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * Get all solutions created by the user.
     */
    public function solutions(): HasMany
    {
        return $this->hasMany(Solutions::class);
    }

    /**
     * Get all comments created by the user.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get all associates (connections) for the user.
     */
    public function associates(): HasMany
    {
        return $this->hasMany(Associates::class);
    }

    /**
     * Get all users that follow this user.
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'followers', 'following_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Get all users that this user is following.
     */
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'followers', 'user_id', 'following_id')
            ->withTimestamps();
    }

    /**
     * Register media collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments')
            ->useDisk('media');

        $this->addMediaCollection('documents')
            ->useDisk('media')
            ->acceptsMimeTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
    }

    /**
     * Clear cache for this user
     */
    public function clearModelCache(): void
    {
        parent::clearModelCache();
        
        // Clear user-specific caches
        $this->invalidateCacheTags(['users', "user:{$this->id}"]);
    }

    /**
     * Get cache tags for users
     */
    public function getCacheTags(): array
    {
        return ['users', "user:{$this->id}"];
    }
}
