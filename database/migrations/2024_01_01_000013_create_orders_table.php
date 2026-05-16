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

            $table->ulid('id')->primary();
            $table->string('invoice', 50)->unique();
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->enum('type', $types)->default(OrderType::KASIR);
            $table->enum('status', $status)->default(OrderStatus::PENDING);

            $table->foreignUlid('table_id')->nullable()->constrained('tables')->nullOnDelete();
            $table->foreignUlid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
