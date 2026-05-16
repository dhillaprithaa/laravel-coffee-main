<?php

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $methods = PaymentMethod::values();
            $status = PaymentStatus::values();

            $table->ulid('id')->primary();
            $table->enum('method', $methods)->default(PaymentMethod::CASH);
            $table->enum('status', $status)->default(PaymentStatus::UNPAID);
            $table->text('snap_token')->nullable();

            $table->foreignUlid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
