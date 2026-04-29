<?php

use App\Enums\OrderType;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $types = OrderType::values();
            $status = OrderStatus::values();

            $table->id();
            $table->string('invoice')->unique();
            $table->integer('grand_total')->default(0);
            $table->enum('type', $types)->default(OrderType::KASIR);
            $table->enum('status', $status)->default(OrderStatus::PENDING);

            $table->foreignId('table_id')->nullable()->constrained('tables')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
