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
        Schema::create('variants', function (Blueprint $table) {
            $table->id();
            $table->string('status');
            $table->uuid('uuid');
            $table->foreignId('product_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->string('weight_value');
            $table->string('weight_unit_id');
            $table->decimal('price', 10, 2);
            $table->decimal('discounted_price',10,2)->nullable(); // optional if price differs
            $table->unsignedSmallInteger('purchase_limit')->nullable();
            $table->unsignedMediumInteger('items_in_stock')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variants');
    }
};
