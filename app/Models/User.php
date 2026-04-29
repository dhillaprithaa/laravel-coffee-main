<?php

namespace App\Models;

use App\Enums\RoleType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'username',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for arrays.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => RoleType::class,
        ];
    }

    /**
     * Scope: only staff-role users.
     */
    public function scopeStaff(Builder $query): Builder
    {
        return $query->where('role', RoleType::STAFF);
    }

    /**
     * User has many orders.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
