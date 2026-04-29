<?php

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $methods = PaymentMethod::values();
            $status = PaymentStatus::values();

            $table->id();
            $table->enum('method', $methods)->default(PaymentMethod::CASH);
            $table->enum('status', $status)->default(PaymentStatus::UNPAID);
            $table->string('snap_token')->nullable();

            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
