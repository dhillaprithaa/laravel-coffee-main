<?php

namespace App\Services;

use App\Models\Order;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        MidtransConfig::$serverKey = config('midtrans.server_key');
        MidtransConfig::$isProduction = config('midtrans.is_production');
        MidtransConfig::$isSanitized = config('midtrans.is_sanitized');
        MidtransConfig::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * Generate a Midtrans Snap payment token.
     */
    public function generateSnapToken(Order $order, array $items, string $customer, ?string $finishUrl = null): string
    {
        $params = [
            'transaction_details' => [
                'order_id' => $order->invoice,
                'gross_amount' => $order->grand_total,
            ],
            'item_details' => $this->buildItemDetails($items),
            'customer_details' => [
                'first_name' => $customer,
            ],
        ];

        if ($finishUrl) {
            $params['callbacks'] = ['finish' => $finishUrl];
        }

        return Snap::getSnapToken($params);
    }

    /**
     * Build item details array for Midtrans payload.
     */
    private function buildItemDetails(array $items): array
    {
        return array_map(fn ($item) => [
            'id' => $item['menu']->id,
            'price' => $item['menu']->price,
            'quantity' => $item['qty'],
            'name' => substr($item['menu']->name, 0, 50),
        ], $items);
    }
}
