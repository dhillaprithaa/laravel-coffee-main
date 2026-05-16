<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\View\View;
use App\Enums\OrderStatus;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(): View
    {
        $totalPesanan = Order::today()
            ->count();

        $totalRevenue = Order::today()
            ->whereStatus(OrderStatus::SELESAI)
            ->sum('grand_total');

        $antrean = Order::today()
            ->whereStatus(OrderStatus::PENDING)
            ->count();

        return view('admin.dashboard.index', [
            'totalPesanan' => $totalPesanan,
            'totalRevenue' => $totalRevenue,
            'antrean' => $antrean,
        ]);
    }
}
