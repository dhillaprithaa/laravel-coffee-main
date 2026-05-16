<?php

namespace App\Enums;

enum MenuCategory: string
{
    case MINUMAN = 'minuman';
    case MAKANAN = 'makanan';

    /**
     * Get the enum values
     */
    public static function values(): array
    {
        $cases = self::cases();

        return array_values($cases);
    }

    /**
     * Get the enum labels
     */
    public function label(): string
    {
        return match ($this) {
            self::MINUMAN => 'Minuman',
            self::MAKANAN => 'Makanan',
        };
    }

    /**
     * Get the enum emoji
     */
    public function emoji(): string
    {
        return match ($this) {
            self::MINUMAN => '☕',
            self::MAKANAN => '🍽️',
        };
    }

    /**
     * Get the enum combined emoji and label
     */
    public function combined(): string
    {
        return match ($this) {
            self::MINUMAN => '☕ Minuman',
            self::MAKANAN => '🍔 Makanan',
        };
    }

    /**
     * Get the enum badge style
     */
    public function style(): string
    {
        return match ($this) {
            self::MINUMAN => 'badge-minuman',
            self::MAKANAN => 'badge-makanan',
        };
    }
}
