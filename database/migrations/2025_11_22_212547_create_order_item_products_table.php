<?php

use App\Models\ProductVariation;
use App\Models\Purchase\Order;
use App\Models\Purchase\OrderItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_item_products', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Order::class)->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignIdFor(OrderItem::class)->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignIdFor(ProductVariation::class)->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedSmallInteger('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_item_products');
    }
};
