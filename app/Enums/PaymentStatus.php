<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case UNPAID = 'unpaid';
    case PAID = 'paid';

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
            self::UNPAID => 'Unpaid',
            self::PAID => 'Paid',
        };
    }
}
