<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case MIDTRANS = 'midtrans';

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
            self::CASH => 'Pembayaran Tunai',
            self::MIDTRANS => 'Pembayaran Digital',
        };
    }

    /**
     * Get the enum description
     */
    public function desc(): string
    {
        return match ($this) {
            self::CASH => 'Bayar ke kasir setelah pesanan jadi',
            self::MIDTRANS => 'Transfer, GoPay, OVO, QRIS, Kartu Kredit',
        };
    }

    /**
     * Get the enum emoji
     */
    public function emoji(): string
    {
        return match ($this) {
            self::CASH => '💵',
            self::MIDTRANS => '💳',
        };
    }

    /**
     * Get the enum combined emoji and label
     */
    public function combined(): string
    {
        return match ($this) {
            self::CASH => self::emoji() . ' ' . self::label(),
            self::MIDTRANS => self::emoji() . ' ' . self::label(),
        };
    }

    /**
     * Get the enum badge style
     */
    public function style(): string
    {
        return match ($this) {
            self::CASH => 'metode-badge-cash',
            self::MIDTRANS => 'metode-badge-midtrans',
        };
    }
}
