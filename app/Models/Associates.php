<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Associates extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'associate_id',
    ];

    public function user()
    {
        return $this->belongsToMany(User::class);
    }
}
