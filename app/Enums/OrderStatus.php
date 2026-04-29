<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case DIPROSES = 'diproses';
    case SELESAI = 'selesai';

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
            self::PENDING => 'Pending',
            self::DIPROSES => 'Diproses',
            self::SELESAI => 'Selesai',
        };
    }

    /**
     * Get the font awesome icon
     */
    public function icon(): string
    {
        return match ($this) {
            self::PENDING => 'fas fa-hourglass-half',
            self::DIPROSES => 'fas fa-blender',
            self::SELESAI => 'fas fa-check-double',
        };
    }
}
