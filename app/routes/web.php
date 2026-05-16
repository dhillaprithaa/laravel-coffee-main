<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SelfOrderController;

Route::get('/', function () {
    return redirect()->route('admin.dashboard.index');
});

Route::prefix('/auth')
    ->name('auth.')
    ->group(function () {
        Route::get('/login', [AuthController::class, 'index'])->name('index');
        Route::post('/login', [AuthController::class, 'login'])->name('login');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });

Route::middleware('auth')
    ->prefix('/admin')
    ->name('admin.')
    ->group(function () {
        Route::prefix('/reports')
            ->name('reports.')
            ->group(function () {
                Route::get('/export/{bulan}', [ReportController::class, 'export'])->name('export');
            });
        Route::resource('reports', ReportController::class)->only('index');

        Route::middleware('role:pimpinan')
            ->resource('staff', StaffController::class)
            ->except('show');

        Route::prefix('/dashboard')
            ->name('dashboard.')
            ->group(function () {
                Route::get('/', [DashboardController::class, 'index'])->name('index');
            });

        Route::prefix('/profile')
            ->name('profile.')
            ->group(function () {
                Route::get('/', [ProfileController::class, 'edit'])->name('edit');
                Route::patch('/', [ProfileController::class, 'update'])->name('update');
            });

        Route::patch('/menus/{menu}/stock', [MenuController::class, 'restock'])->name('menus.stock');
        Route::resource('menus', MenuController::class)
            ->except('show');

        Route::middleware('role:pimpinan')
            ->prefix('/tables')
            ->name('tables.')
            ->group(function () {
                Route::get('/generate-pdf/{table}', [TableController::class, 'show'])->name('code.show');
                Route::post('/generate-pdf', [TableController::class, 'generate'])->name('code.generate');
            });

        Route::resource('tables', TableController::class)
            ->only([
                'index',
                'store',
                'destroy',
            ]);

        Route::prefix('/orders')
            ->name('orders.')
            ->group(function () {
                Route::get('/', [OrderController::class, 'index'])->name('index');
                Route::get('/queue', [OrderController::class, 'queue'])->name('queue');
                Route::post('/checkout', [OrderController::class, 'checkout'])->name('checkout');
                Route::prefix('/{order}')
                    ->group(function () {
                        Route::post('/complete', [OrderController::class, 'complete'])->name('complete');
                        Route::post('/confirm', [OrderController::class, 'confirm'])->name('confirm');
                        Route::patch('/status', [OrderController::class, 'update'])->name('update');
                        Route::get('/receipt', [OrderController::class, 'nota'])->name('receipt');
                    });
            });
    });


Route::prefix('/selforder')
    ->name('selforder.')
    ->group(function () {
        Route::get('/success/{invoice}', [SelfOrderController::class, 'success'])->name('success');
        Route::get('/{table}', [SelfOrderController::class, 'show'])->name('show');
        Route::post('/checkout', [SelfOrderController::class, 'checkout'])->name('checkout');
    });

Route::prefix('midtrans')
    ->name('midtrans.')
    ->group(function () {
        Route::post('/webhook', [WebhookController::class, 'midtrans'])->name('webhook');
        Route::post('/confirm/{order}', [WebhookController::class, 'midtransConfirm'])->name('confirm');
    });
