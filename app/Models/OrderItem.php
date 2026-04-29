<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'order_id',
        'menu_id',
        'qty',
        'price',
        'subtotal',
    ];

    /**
     * OrderItem belongs to order.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * OrderItem belongs to menu.
     */
    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
