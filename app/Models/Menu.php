<?php

namespace App\Models;

use App\Enums\MenuCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'category',
        'price',
        'stock',
        'image',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected function casts(): array
    {
        return [
            'category' => MenuCategory::class,
        ];
    }

    /**
     * Custom attributes to check if the item is instock
     */
    protected function available(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->stock > 0,
        );
    }

    /**
     * Order has many order items.
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scope for menus with available stock.
     */
    public function scopeInstock(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
    }
}
