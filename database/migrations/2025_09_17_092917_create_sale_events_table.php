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
        Schema::create('sale_events', function (Blueprint $table) {
            $table->id();
            $table->boolean('status')->default(true);
            $table->string('title')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->timestamp('start_timestamps')->nullable(false);
            $table->timestamp('end_timestamps')->nullable(false);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_events');
    }
};
