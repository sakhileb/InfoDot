<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

class Obj extends Model
{
    use HasFactory, Searchable;

    public $asYouType = true;

    protected $table = 'objs';

    protected $fillable = ['parent_id', 'uuid', 'user_id', 'team_id', 'objectable_type', 'objectable_id'];

    /**
     * Get the user that owns the object.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the team that owns the object.
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the parent object.
     */
    public function parent()
    {
        return $this->belongsTo(Obj::class, 'parent_id');
    }

    /**
     * Get the child objects.
     */
    public function children()
    {
        return $this->hasMany(Obj::class, 'parent_id');
    }

    /**
     * Get all descendants recursively.
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get the owning objectable model (File or Folder).
     */
    public function objectable()
    {
        return $this->morphTo();
    }

    /**
     * Get the searchable data array for the model.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'team_id' => $this->team_id,
            'name' => $this->objectable?->name ?? '',
        ];
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
            // Delete the objectable model (File or Folder)
            $model->objectable?->delete();
            
            // Delete all descendants
            $model->children->each->delete();
        });
    }
}
