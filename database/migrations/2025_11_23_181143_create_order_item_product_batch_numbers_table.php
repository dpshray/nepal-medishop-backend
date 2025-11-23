<?php

use App\Models\Purchase\OrderItemProduct;
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
        Schema::create('order_item_product_batch_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(OrderItemProduct::class)->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignIdFor(VendorProductPrice::class)->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedSmallInteger('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_item_product_batch_numbers');
    }
};
