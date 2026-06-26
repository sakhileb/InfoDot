<?php

namespace App\Models;

use Laravel\Cashier\Billable;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Scout\Searchable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Billable;
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use Searchable;
    use TwoFactorAuthenticatable;

    protected $fillable = [
        'name', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function toSearchableArray(): array
    {
        return [
            'name'  => $this->name,
            'email' => $this->email,
        ];
    }

    public function avatar(): string
    {
        return 'https://www.gravatar.com/avatar/' . md5($this->email) . '?d=mp';
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function questions()
    {
        return $this->hasMany(Questions::class);
    }

    public function solutions()
    {
        return $this->hasMany(Solutions::class);
    }

    public function associates()
    {
        return $this->hasMany(Associates::class);
    }
}
