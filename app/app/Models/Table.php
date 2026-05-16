<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class Table extends Model
{
    use HasUlids;
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
