<?php

use App\Models\FlashSale;
use App\Models\Product;
use App\Models\ProductVariation;
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
        Schema::create('flash_sale_products', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(FlashSale::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignIdFor(Product::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignIdFor(VendorProductPrice::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->decimal('flash_sale_price', 10, 2);
            $table->decimal('platform_price', 10, 2);
            $table->unsignedSmallInteger('stock_limit')->nullable();
            $table->unsignedSmallInteger('max_purchase')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flash_sale_products');
    }
};
