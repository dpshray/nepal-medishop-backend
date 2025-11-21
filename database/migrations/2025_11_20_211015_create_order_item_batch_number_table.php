<?php

use App\Models\ProductVariation;
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
        Schema::create('order_item_batch_number', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(OrderItem::class)->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignIdFor(VendorProductPrice::class)->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignIdFor(ProductVariation::class)->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedTinyInteger('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_item_batch_number');
    }
};
