<?php

namespace App\Enums;

enum RoleType: string
{
    case PIMPINAN = 'pimpinan';
    case STAFF = 'staff';

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
            self::PIMPINAN => 'Pimpinan',
            self::STAFF => 'Staff',
        };
    }

    /**
     * Get the enum emoji
     */
    public function emoji(): string
    {
        return match ($this) {
            self::PIMPINAN => '👨‍🍳',
            self::STAFF => '👨‍💻',
        };
    }

    /**
     * Get the enum combined emoji and label
     */
    public function combined(): string
    {
        return match ($this) {
            self::PIMPINAN => self::emoji().' '.self::label(),
            self::STAFF => self::emoji().' '.self::label(),
        };
    }

    /**
     * Get the enum style
     */
    public function style(): string
    {
        return match ($this) {
            self::PIMPINAN => 'badge-pimpinan',
            self::STAFF => 'badge-staff',
        };
    }
}
