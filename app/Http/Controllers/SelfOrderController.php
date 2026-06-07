<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Order;
use App\Models\Table;
use App\Models\Payment;
use App\Enums\OrderType;
use App\Models\OrderItem;
use Illuminate\View\View;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\SelfOrderCheckoutRequest;

class SelfOrderController extends Controller
{

    /**
     * Display the self-ordering menu for a table.
     */
    public function show(Table $table): View
    {
        $menus = Menu::query()->orderBy('category', 'asc')->get();
        $categories = $menus->groupBy('category');

        return view('selforder.show', [
            'table' => $table,
            'menus' => $menus,
            'categories' => $categories,
        ]);
    }

    /**
     * Process self-order checkout submission.
     */
    public function checkout(SelfOrderCheckoutRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            [$total, $items] = $this->buildOrderItems($request->items);

            $invoice = Order::generateInvoice('QR');
            $order = Order::create([
                'invoice' => $invoice,
                'table_id' => $request->table_id,
                'customer' => $request->customer,
                'grand_total' => $total,
                'type' => OrderType::SELF,
                'status' => OrderStatus::PENDING,
            ]);

            $this->createOrderItems($order, $items);

            $payment = Payment::create([
                'order_id' => $order->id,
                'method' => $request->method,
                'status' => PaymentStatus::UNPAID,
            ]);

            $methodValue = $request->method instanceof PaymentMethod ? $request->method->value : (string) $request->method;
            Log::info('Checkout method', ['method' => $methodValue]);

            if ($methodValue === PaymentMethod::MIDTRANS->value) {
                $snapToken = $this->generateMidtransSnapToken($order, $items, route('selforder.success', $invoice));

                if (!$snapToken) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal memproses pembayaran Midtrans. Silakan pilih metode tunai.',
                    ], 500);
                }

                $payment->update([
                    'snap_token' => $snapToken,
                ]);

                DB::commit();
                return response()->json([
                    'success' => true,
                    'snap_token' => $snapToken,
                    'order_id' => $order->id,
                    'invoice' => $invoice
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dibuat! Konfirmasi pembayaran di halaman Antrian.',
                'invoice' => $invoice,
                'order_id' => $order->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Build order items array, validate stock, and calculate total price.
     *
     * @throws ValidationException
     */
    private function buildOrderItems(array $payload): array
    {
        $total = 0;
        $items = [];

        foreach ($payload as $item) {
            $menu = Menu::findOrFail($item['id']);
            if ($menu->stock < $item['qty']) {
                throw ValidationException::withMessages([
                    'items' => "Stok {$menu->name} tidak mencukupi. Tersedia: {$menu->stock}.",
                ]);
            }

            $subtotal = $menu->price * $item['qty'];
            $total += $subtotal;
            $items[] = [
                'menu' => $menu,
                'qty' => $item['qty'],
                'subtotal' => $subtotal,
            ];
        }

        return [$total, $items];
    }

    /**
     * Persist order items and decrement stock.
     */
    private function createOrderItems(Order $order, array $items): void
    {
        foreach ($items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'menu_id' => $item['menu']->id,
                'qty' => $item['qty'],
                'price' => $item['menu']->price,
                'subtotal' => $item['subtotal'],
            ]);
        }
    }

    /**
     * Update menu stock in real-time (called from frontend when +/- is clicked).
     */
    public function updateStock(Request $request, Menu $menu): JsonResponse
    {
        $request->validate([
            'qty' => 'required|integer|min:0',
        ]);

        $menu->update(['stock' => $request->qty]);

        return response()->json([
            'success' => true,
            'stock' => $menu->fresh()->stock,
        ]);
    }

    /**
     * Display the order success page.
     */
    public function success(string $invoice): View
    {
        $order = Order::with(['items.menu', 'table', 'payment'])
            ->where('invoice', $invoice)
            ->firstOrFail();

        return view('selforder.success', [
            'order' => $order
        ]);
    }

    /**
     * Generate Midtrans Snap token via raw API call (no library dependency).
     */
    private function generateMidtransSnapToken(Order $order, array $items, string $finishUrl): ?string
    {
        $serverKey = config('midtrans.server_key');
        $isProduction = config('midtrans.is_production');
        $baseUrl = $isProduction
            ? 'https://app.midtrans.com/snap/v1'
            : 'https://app.sandbox.midtrans.com/snap/v1';

        $itemDetails = array_map(fn ($item) => [
            'id' => $item['menu']->id,
            'price' => (int) $item['menu']->price,
            'quantity' => (int) $item['qty'],
            'name' => substr($item['menu']->name, 0, 50),
        ], $items);

        $payload = [
            'transaction_details' => [
                'order_id' => $order->invoice,
                'gross_amount' => $order->grand_total,
            ],
            'item_details' => $itemDetails,
            'customer_details' => [
                'first_name' => 'Coffee Shop',
            ],
        ];

        if ($finishUrl) {
            $payload['callbacks'] = ['finish' => $finishUrl];
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $baseUrl . '/transactions',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Basic ' . base64_encode($serverKey . ':'),
            ],
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CONNECTTIMEOUT_MS => 10000,
            CURLOPT_TIMEOUT_MS => 10000,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            Log::error('Midtrans cURL Error: ' . $curlError, ['order_id' => $order->id]);
            return null;
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            Log::error('Midtrans API Error', ['http_code' => $httpCode, 'response' => $response, 'order_id' => $order->id]);
            return null;
        }

        $result = json_decode($response, true);
        return $result['token'] ?? null;
    }
}
