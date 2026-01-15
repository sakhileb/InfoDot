<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Steps extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'solutions_step';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'solution_id',
        'solution_heading',
        'solution_body',
    ];

    /**
     * The searchable attributes for full-text search.
     *
     * @var array<int, string>
     */
    protected $searchable = [
        'solution_heading',
        'solution_body',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'solution_heading' => 'string',
            'solution_body' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the solution that owns the step.
     */
    public function solution(): BelongsTo
    {
        return $this->belongsTo(Solutions::class, 'solution_id');
    }

    /**
     * Get the user that created the step.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the solution heading as an array.
     */
    public function getHeadingArrayAttribute(): array
    {
        $decoded = json_decode($this->solution_heading, true);
        return $decoded ? $decoded : [];
    }

    /**
     * Get the solution body as an array.
     */
    public function getBodyArrayAttribute(): array
    {
        $decoded = json_decode($this->solution_body, true);
        return $decoded ? $decoded : [];
    }

    /**
     * Set the solution heading from an array.
     */
    public function setHeadingArrayAttribute(array $value): void
    {
        $this->attributes['solution_heading'] = json_encode($value);
    }

    /**
     * Set the solution body from an array.
     */
    public function setBodyArrayAttribute(array $value): void
    {
        $this->attributes['solution_body'] = json_encode($value);
    }
}
