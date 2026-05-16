<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class OrderItem extends Model
{
    use HasUlids;
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

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

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
