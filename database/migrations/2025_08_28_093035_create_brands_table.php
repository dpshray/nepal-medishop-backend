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
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->boolean('status')->default(1);
            $table->string('slug')->unique();
            $table->string('name')->unique();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_popular')->default(false);
            $table->decimal('discount_percent', 5, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
