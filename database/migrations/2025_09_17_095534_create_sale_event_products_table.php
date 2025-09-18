<?php

use App\Models\Product;
use App\Models\SaleEvent;
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
        Schema::create('sale_event_products', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(SaleEvent::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignIdFor(VendorProductPrice::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->decimal('event_sale_price', 10, 2);
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
        Schema::dropIfExists('sale_event_products');
    }
};
