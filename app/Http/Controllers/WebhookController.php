<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle Midtrans payment webhook notification.
     */
    public function midtrans(Request $request): JsonResponse
    {
        try {
            $payload = $request->all();
            $orderId = $payload['order_id'] ?? null;

            if (! $orderId || str_contains($orderId, 'test') || str_contains($orderId, 'mock')) {
                return response()->json([
                    'message' => 'Test notification bypass.'
                ]);
            }

            if (! $this->isValidSignature($payload)) {
                Log::warning("Midtrans webhook: invalid signature for order {$orderId}");
                return response()->json([
                    'message' => 'Invalid signature.'
                ], 403);
            }

            $order = Order::where('invoice', $orderId)->first();
            if (! $order || ! $order->payment) {
                return response()->json([
                    'message' => 'Order or payment not found, skipped.'
                ]);
            }

            $this->applyTransactionStatus($order, $payload['transaction_status'] ?? null, $payload['fraud_status'] ?? null);
            Log::info("Webhook OK: order={$orderId} status={$payload['transaction_status']}");

            return response()->json([
                'message' => 'Webhook handled successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Midtrans Webhook Error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Error handling webhook.'
            ], 500);
        }
    }

    /**
     * Confirm Midtrans payment from client-side callback.
     */
    public function midtransConfirm(Order $order): JsonResponse
    {
        $payment = $order->payment;

        if (! $payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found.'
            ], 404);
        }

        if ($payment->method === PaymentMethod::MIDTRANS && $payment->status !== PaymentStatus::PAID->value) {
            $payment->update([
                'status' => PaymentStatus::PAID
            ]);

            if ($order->status === OrderStatus::PENDING) {
                $order->update([
                    'status' => OrderStatus::DIPROSES
                ]);
            }
        }

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Verify the Midtrans signature key from the payload.
     */
    private function isValidSignature(array $payload): bool
    {
        $components = [
            $payload['order_id'] ?? '',
            $payload['status_code'] ?? '',
            $payload['gross_amount'] ?? '',
            config('midtrans.server_key')
        ];

        $expected = hash('sha512', implode('', $components));
        return isset($payload['signature_key']) && hash_equals($expected, $payload['signature_key']);
    }

    /**
     * Apply order and payment status based on Midtrans transaction status.
     */
    private function applyTransactionStatus(Order $order, ?string $status, ?string $fraud): void
    {
        $isPaid = ($status === 'capture' && $fraud === 'accept') || $status === 'settlement';
        $isFailed = in_array($status, [
            'cancel',
            'deny',
            'expire'
        ]);

        if ($isPaid) {
            $order->payment->update(['status' => PaymentStatus::PAID]);
            $order->update(['status' => OrderStatus::DIPROSES]);
        }

        if ($isFailed) {
            $order->payment->update(['status' => PaymentStatus::UNPAID]);
            $order->update(['status' => OrderStatus::PENDING]);
        }
    }
}
