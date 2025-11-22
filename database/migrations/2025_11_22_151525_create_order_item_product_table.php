<?php

use App\Models\ProductVariation;
use App\Models\Purchase\Order;
use App\Models\Purchase\OrderItem;
use App\Models\VendorProductPrice;
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
        Schema::create('order_item_product', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ProductVariation::class)->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignIdFor(Order::class)->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignIdFor(OrderItem::class)->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedSmallInteger('quantity');
            $table->foreignIdFor(VendorProductPrice::class)->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_item_product');
    }
};
