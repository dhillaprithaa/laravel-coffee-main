<?php

use App\Enums\MenuCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $categories = MenuCategory::values();

            $table->id();
            $table->string('name');
            $table->enum('category', $categories);
            $table->integer('price');
            $table->integer('stock')->default(0);
            $table->string('image')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
