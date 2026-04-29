<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Enums\RoleType;
use App\Enums\OrderType;
use App\Models\OrderItem;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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
     * Full monthly report with analytics for pimpinan.
     */
    private function pimpinanReport(Request $request): View
    {
        $bulan = $request->input('bulan', now()->format('Y-m'));
        [$year, $month] = explode('-', $bulan);
        [$prevYear, $prevMonth] = $this->previousMonth($year, $month);

        $base = Order::inMonth($year, $month)
            ->when($request->type,    fn($q) => $q->whereType($request->type))
            ->when($request->status,  fn($q) => $q->where('status', $request->status))
            ->when($request->method,  fn($q) => $q->whereHas('payment', fn($p) => $p->where('method', $request->method)))
            ->when($request->invoice, fn($q) => $q->where('invoice', 'like', '%' . $request->invoice . '%'));

        $totalOrders  = (clone $base)->count();
        $totalRevenue = (clone $base)->sum('grand_total');
        $orderIds     = (clone $base)->pluck('id');

        $daily = (clone $base)
            ->select(DB::raw('DATE(created_at) as tanggal'), DB::raw('SUM(grand_total) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        $paymentBreakdown = Order::inMonth($year, $month)
            ->when($request->type,    fn($q) => $q->whereType($request->type))
            ->when($request->status,  fn($q) => $q->where('status', $request->status))
            ->when($request->invoice, fn($q) => $q->where('invoice', 'like', '%' . $request->invoice . '%'))
            ->select('id')
            ->with('payment:order_id,method')
            ->get()
            ->groupBy(fn($o) => $o->payment?->method?->value)
            ->map(fn($group) => $group->count())
            ->sortDesc();

        return view('admin.reports.index', [
            'bulan'             => $bulan,
            'monthly'           => $totalOrders,
            'totalRevenue'      => $totalRevenue,
            'averageOrderValue' => $totalOrders > 0 ? round($totalRevenue / $totalOrders) : 0,
            'selfOrder'         => (clone $base)->whereType(OrderType::SELF)->count(),
            'normalOrder'       => (clone $base)->whereType(OrderType::KASIR)->count(),
            'daily'             => $daily,
            'orders'            => (clone $base)->with(['payment', 'table', 'user'])->latest()->paginate(10)->withQueryString(),
            'bestSellers'       => $this->bestSellers($orderIds, desc: true, limit: 3),
            'worstSellers'      => $this->bestSellers($orderIds, desc: false, limit: 3),
            'paymentBreakdown'  => $paymentBreakdown,
            'prevRevenue'       => Order::inMonth($prevYear, $prevMonth)->sum('grand_total'),
            'prevOrders'        => Order::inMonth($prevYear, $prevMonth)->count(),
            'revenueGrowth'     => $this->growthRate(Order::inMonth($prevYear, $prevMonth)->sum('grand_total'), $totalRevenue),
            'ordersGrowth'      => $this->growthRate(Order::inMonth($prevYear, $prevMonth)->count(), $totalOrders),
            'filters'           => $this->filters($request),
        ]);
    }

    /**
     * Today-only order list for staff.
     */
    private function staffReport(Request $request): View
    {
        $today  = today();
        $orders = Order::whereDate('created_at', $today)
            ->when($request->type,    fn($q) => $q->whereType($request->type))
            ->when($request->status,  fn($q) => $q->where('status', $request->status))
            ->when($request->method,  fn($q) => $q->whereHas('payment', fn($p) => $p->where('method', $request->method)))
            ->when($request->invoice, fn($q) => $q->where('invoice', 'like', '%' . $request->invoice . '%'))
            ->with(['payment', 'table', 'user'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.reports.index', [
            'bulan'   => now()->format('Y-m'),
            'orders'  => $orders,
            'filters' => $this->filters($request),
        ]);
    }

    /**
     * Export filtered orders as PDF.
     */
    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $bulan = Auth::user()->role === RoleType::PIMPINAN
            ? $request->input('bulan', now()->format('Y-m'))
            : now()->format('Y-m');

        [$year, $month] = explode('-', $bulan);

        $orders = Order::inMonth($year, $month)
            ->with(['payment', 'table', 'user'])
            ->when($request->type,    fn($q) => $q->whereType($request->type))
            ->when($request->status,  fn($q) => $q->where('status', $request->status))
            ->when($request->method,  fn($q) => $q->whereHas('payment', fn($p) => $p->where('method', $request->method)))
            ->when($request->invoice, fn($q) => $q->where('invoice', 'like', '%' . $request->invoice . '%'))
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
        }, 'laporan-' . $bulan . '.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * Get best or worst selling order items for a set of order IDs.
     */
    private function bestSellers(\Illuminate\Support\Collection $orderIds, bool $desc, int $limit): \Illuminate\Support\Collection
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
     * Get the previous month's year and month number.
     */
    private function previousMonth(string $year, string $month): array
    {
        $date = Carbon::createFromDate($year, $month, 1)->subMonth();
        return [$date->format('Y'), $date->format('m')];
    }

    /**
     * Extract filter inputs from the request.
     */
    private function filters(Request $request): array
    {
        return [
            'type'    => $request->input('type', ''),
            'status'  => $request->input('status', ''),
            'method'  => $request->input('method', ''),
            'invoice' => $request->input('invoice', ''),
        ];
    }
}
