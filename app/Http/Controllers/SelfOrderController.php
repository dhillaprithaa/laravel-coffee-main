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
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\SelfOrderCheckoutRequest;

class SelfOrderController extends Controller
{
    public function __construct(protected MidtransService $midtrans) {}

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
}
