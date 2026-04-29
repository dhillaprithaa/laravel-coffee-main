<?php

namespace App\Enums;

enum OrderType: string
{
    case SELF = 'self';
    case KASIR = 'kasir';

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
            self::SELF => 'Self checkout',
            self::KASIR => 'Kasir',
        };
    }

    /**
     * Get the enum style
     */
    public function style(): string
    {
        return match ($this) {
            self::SELF => 'badge-tipe-qr',
            self::KASIR => 'badge-tipe-kasir',
        };
    }

    /**
     * Get the font awesome icon
     */
    public function icon(): string
    {
        return match ($this) {
            self::SELF => 'fas fa-qrcode',
            self::KASIR => 'fas fa-cash-register',
        };
    }
}
