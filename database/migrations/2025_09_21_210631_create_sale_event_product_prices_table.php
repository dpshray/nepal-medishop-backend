<?php

use App\Models\ProductVariation;
use App\Models\SaleEventProduct;
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
        Schema::create('sale_event_product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(SaleEventProduct::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignIdFor(ProductVariation::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->decimal('event_sale_price', 10, 2);
            $table->unsignedSmallInteger('stock_limit')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_event_product_prices');
    }
};
