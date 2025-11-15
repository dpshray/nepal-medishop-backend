<?php

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
        Schema::create('vendor_product_prices', function (Blueprint $table) {
            $table->id();
            $table->boolean('status')->default(true);
            $table->unsignedBigInteger('product_vendor_id');
            $table->foreign('product_vendor_id')->references('id')->on('product_vendors')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('product_variation_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->decimal('price', 10, 2);
            $table->unsignedSmallInteger('units_in_stock')->default(0);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_product_prices');
    }
};
