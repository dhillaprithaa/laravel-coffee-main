<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'number',
    ];

    /**
     * Table has many orders.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
