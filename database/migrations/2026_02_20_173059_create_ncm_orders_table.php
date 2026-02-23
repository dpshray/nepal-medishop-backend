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
        Schema::create('ncm_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('order_id');

            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->string('ncm_order_id')->nullable();
            $table->string('tbranch')->default('TINKUNE');
            $table->string('fbranch')->default('TINKUNE');
            $table->string('package')->nullable();
            $table->decimal('weight', 8, 2)->default(1.00);
            $table->decimal('cod_charge', 10, 2)->nullable();
            $table->decimal('delivery_charge', 10, 2)->nullable();
            $table->text('instruction')->nullable();
            $table->string('delivery_status')->nullable();
            $table->enum('delivery_type', ['Door2Door', 'Branch2Door', 'Branch2Branch', 'Door2Branch'])->default('Door2Door');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ncm_orders');
    }
};
