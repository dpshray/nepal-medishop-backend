<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('categories')->restrictOnDelete()->cascadeOnUpdate();
            $table->boolean('status')->default(1);
            $table->string('slug')->unique();
            $table->string('name')->unique();
            $table->text('detail')->nullable();
            $table->decimal('discount_percent', 5, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */  
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};


