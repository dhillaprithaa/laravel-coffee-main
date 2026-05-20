<?php

namespace App\Models;

use App\Traits\HasImage;
use App\Enums\MenuCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class Menu extends Model
{
    use HasUlids, HasImage;

    protected $fillable = [
        'name',
        'category',
        'price',
        'stock',
        'description',
        'image',
    ];

    /**
     * Image columns and their storage config.
     */
    public function images(): array
    {
        return [
            'image' => ['disk' => 'public', 'folder' => 'menus'],
        ];
    }

    /**
     * The attributes that should be cast to native types.
     */
    protected function casts(): array
    {
        return [
            'category' => MenuCategory::class,
            'price' => 'decimal:2',
        ];
    }

    /**
     * Get the full URL for the image column, or null if empty.
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->getImageUrl('image');
    }

    /**
     * Custom attributes to check if the item is instock
     */
    protected function available(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->stock > 0,
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
