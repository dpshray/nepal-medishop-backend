<?php

use App\Models\Purchase\Order;
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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Order::class)->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('item_type');
            $table->string('item_id');
            $table->string('item_name')->nullable();
            $table->string('item_slug')->nullable();
            $table->string('item_variant_id')->nullable();
            $table->string('variant_name')->nullable();
            $table->string('variant_size')->nullable();
            $table->unsignedTinyInteger('quantity');
            $table->decimal('price', 10, 2);
            $table->decimal('total', 10, 2);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
