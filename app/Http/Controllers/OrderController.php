<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Order;
use App\Models\Payment;
use App\Enums\OrderType;
use App\Models\OrderItem;
use Illuminate\View\View;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CheckoutRequest;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\UpdateOrderStatusRequest;

class OrderController extends Controller
{
    public function __construct(protected MidtransService $midtrans) {}

    /**
     * Display the kasir POS page.
     */
    public function index(): View
    {
        $menus = Menu::all();

        return view('admin.orders.index', [
            'menus' => $menus,
        ]);
    }

    /**
     * Display the order queue age.
     */
    public function queue(): View
    {
        $ongoing = Order::with([
            'items.menu',
            'table',
            'payment',
            'user',
        ])
            ->active()
            ->latest()
            ->get();

        $completed = Order::with([
            'items.menu',
            'table',
            'payment',
            'user',
        ])
            ->completedToday()
            ->latest()
            ->get();

        return view('admin.orders.queue', [
            'ongoing' => $ongoing,
            'completed' => $completed,
        ]);
    }

    /**
     * Process checkout from kasir and create a new order.
     */
    public function checkout(CheckoutRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            [$total, $items] = $this->buildOrderItems($request->items);

            $invoice = Order::generateInvoice('INV');
            $order = Order::create([
                'invoice' => $invoice,
                'user_id' => Auth::id(),
                'table_id' => $request->table_id,
                'customer' => $request->customer,
                'grand_total' => $total,
                'type' => OrderType::KASIR,
                'status' => OrderStatus::PENDING,
            ]);

            $this->createOrderItems($order, $items);

            $payment = Payment::create([
                'order_id' => $order->id,
                'method' => $request->method,
                'status' => PaymentStatus::UNPAID,
            ]);

            if ($request->method === PaymentMethod::MIDTRANS) {
                $snapToken = $this->midtrans->generateSnapToken(
                    $order,
                    $items,
                    Auth::user()->name,
                    route('selforder.success', $invoice)
                );

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
     * Mark an order as complete.
     */
    public function complete(Order $order): JsonResponse
    {
        $order->update([
            'status' => OrderStatus::SELESAI,
        ]);

        $order->payment?->update([
            'status' => PaymentStatus::PAID,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pesanan selesai!',
        ]);
    }

    /**
     * Confirm payment for an order.
     */
    public function confirm(Order $order): JsonResponse
    {
        $payment = $order->payment;

        if (! $payment) {
            return response()->json([
                'success' => false,
                'message' => 'Data pembayaran tidak ditemukan.',
            ], 404);
        }

        $payment->update([
            'status' => PaymentStatus::PAID,
        ]);

        if ($order->status === OrderStatus::PENDING) {
            $order->update([
                'status' => OrderStatus::DIPROSES,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran dikonfirmasi! Pesanan mulai diproses.',
            'new_status' => $order->fresh()->status,
        ]);
    }

    /**
     * Update the processing status of an order.
     */
    public function update(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        $order->update([
            'status' => $request->status,
        ]);

        $message = match ($request->status) {
            OrderStatus::PENDING => 'Pesanan mulai diproses!',
            OrderStatus::DIPROSES => 'Pesanan selesai!',
            default => 'Pesanan diproses!',
        };

        return response()->json([
            'success' => true,
            'message' => $message,
            'new_status' => $request->status,
        ]);
    }

    /**
     * Display the print receipt view for an order.
     */
    public function nota(Order $order): View
    {
        $order->load([
            'items.menu',
            'table',
            'user',
            'payment',
        ]);

        return view('admin.nota', [
            'order' => $order,
        ]);
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

            $item['menu']->decrement('stock', $item['qty']);
        }
    }
}
