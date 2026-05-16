<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Enums\RoleType;
use App\Enums\OrderType;
use App\Models\OrderItem;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{

    /**
     * Route to the correct report view based on the user's role.
     */
    public function index(Request $request): View
    {
        return match (Auth::user()->role) {
            RoleType::PIMPINAN => $this->pimpinanReport($request),
            RoleType::STAFF => $this->staffReport($request),
            default => abort(404),
        };
    }

    /**
     * Full report with analytics for pimpinan.
     */
    private function pimpinanReport(Request $request): View
    {
        $defaultStart = now()->startOfMonth()->format('Y-m-d');
        $defaultEnd = now()->endOfMonth()->format('Y-m-d');

        $start = $request->input('start', $defaultStart);
        $end = $request->input('end', $defaultEnd);

        $days = Carbon::parse($start)->diffInDays(Carbon::parse($end));
        $prevEnd = Carbon::parse($start)->subDay()->format('Y-m-d');
        $prevStart = Carbon::parse($prevEnd)->subDays($days)->format('Y-m-d');

        $base = Order::whereInRange($start, $end)
            ->when($request->type, fn($q) => $q->whereType($request->type))
            ->when($request->status, fn($q) => $q->whereStatus($request->status))
            ->when($request->method, fn($q) => $q->wherePayment($request->method))
            ->when($request->invoice, fn($q) => $q->whereInvoice($request->invoice));

        $totalOrders = (clone $base)->count();
        $totalRevenue = (clone $base)->sum('grand_total');
        $orderIds = (clone $base)->pluck('id');

        $daily = (clone $base)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(grand_total) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $paymentBreakdown = Order::whereInRange($start, $end)
            ->when($request->type, fn($q) => $q->whereType($request->type))
            ->when($request->status, fn($q) => $q->whereStatus($request->status))
            ->when($request->invoice, fn($q) => $q->whereInvoice($request->invoice))
            ->select('id')
            ->with('payment:order_id,method')
            ->get()
            ->groupBy(fn($o) => $o->payment?->method?->value)
            ->map(fn($group) => $group->count())
            ->sortDesc();

        $prevRevenue = Order::whereInRange($prevStart, $prevEnd)->sum('grand_total');
        $prevOrders = Order::whereInRange($prevStart, $prevEnd)->count();

        return view('admin.reports.index', [
            'start' => $start,
            'end' => $end,
            'monthly' => $totalOrders,
            'totalRevenue' => $totalRevenue,
            'averageOrderValue' => $totalOrders > 0 ? round($totalRevenue / $totalOrders) : 0,
            'selfOrder' => (clone $base)->whereType(OrderType::SELF)->count(),
            'normalOrder' => (clone $base)->whereType(OrderType::KASIR)->count(),
            'daily' => $daily,
            'orders' => (clone $base)->with(['payment', 'table', 'user'])->latest()->paginate(10)->withQueryString(),
            'bestSellers' => $this->bestSellers($orderIds, desc: true, limit: 3),
            'worstSellers' => $this->bestSellers($orderIds, desc: false, limit: 3),
            'paymentBreakdown' => $paymentBreakdown,
            'prevRevenue' => $prevRevenue,
            'prevOrders' => $prevOrders,
            'revenueGrowth' => $this->growthRate($prevRevenue, $totalRevenue),
            'ordersGrowth' => $this->growthRate($prevOrders, $totalOrders),
            'filters' => $this->filters($request),
        ]);
    }

    /**
     * Today-only order list for staff.
     */
    private function staffReport(Request $request): View
    {
        $today = today()->format('Y-m-d');
        $orders = Order::whereDate('created_at', $today)
            ->when($request->type, fn($q) => $q->whereType($request->type))
            ->when($request->status, fn($q) => $q->whereStatus($request->status))
            ->when($request->method, fn($q) => $q->wherePayment($request->method))
            ->when($request->invoice, fn($q) => $q->whereInvoice($request->invoice))
            ->with([
                'payment',
                'table',
                'user'
            ])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.reports.index', [
            'start' => $today,
            'end' => $today,
            'orders' => $orders,
            'filters' => $this->filters($request),
        ]);
    }

    /**
     * Export filtered orders as PDF.
     */
    public function export(Request $request): StreamedResponse
    {
        $defaultStart = now()->startOfMonth()->format('Y-m-d');
        $defaultEnd = now()->endOfMonth()->format('Y-m-d');
        $start = $request->input('start', $defaultStart);
        $end = $request->input('end', $defaultEnd);

        $orders = Order::whereInRange($start, $end)
            ->with(['payment', 'table', 'user'])
            ->when($request->type, fn($q) => $q->whereType($request->type))
            ->when($request->status, fn($q) => $q->whereStatus($request->status))
            ->when($request->method, fn($q) => $q->wherePayment($request->method))
            ->when($request->invoice, fn($q) => $q->whereInvoice($request->invoice))
            ->latest()
            ->get();

        return response()->streamDownload(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Invoice', 'Tipe', 'Meja', 'Pelanggan', 'Kasir', 'Total', 'Pembayaran', 'Status', 'Waktu']);

            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->invoice,
                    $order->type->label(),
                    $order->table?->number ?? '-',
                    $order->customer ?? '-',
                    $order->user?->name ?? '-',
                    $order->grand_total,
                    $order->payment?->method->label() ?? '-',
                    $order->status->label(),
                    $order->created_at->format('d/m/Y H:i'),
                ]);
            }

            fclose($handle);
        }, 'laporan-' . $start . '_' . $end . '.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * Get best or worst selling order items for a set of order IDs.
     */
    private function bestSellers(Collection $orderIds, bool $desc, int $limit): Collection
    {
        $query = OrderItem::whereIn('order_id', $orderIds)
            ->select('menu_id', DB::raw('SUM(qty) as total_qty'), DB::raw('SUM(subtotal) as total_revenue'))
            ->with('menu:id,name,category')
            ->groupBy('menu_id')
            ->limit($limit);

        return $desc
            ? $query->orderByDesc('total_qty')->get()
            : $query->orderBy('total_qty')->get();
    }

    /**
     * Calculate growth rate percentage between two values.
     */
    private function growthRate(int|float $previous, int|float $current): ?float
    {
        if ($previous == 0) return null;
        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * Extract filter inputs from the request.
     */
    private function filters(Request $request, string $default = ''): array
    {
        return [
            'type' => $request->input('type', $default),
            'status' => $request->input('status', $default),
            'method' => $request->input('method', $default),
            'invoice' => $request->input('invoice', $default),
            'start' => $request->input('start', $default),
            'end' => $request->input('end', $default),
        ];
    }
}
