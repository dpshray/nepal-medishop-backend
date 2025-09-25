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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->boolean('status')->default(1);
            $table->boolean('is_featured')->default(0);
            $table->unsignedBigInteger('added_by');
            $table->foreign('added_by')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('updated_by')->nullable()->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('brand_id')->constrained()->onDelete('restrict')->onUpdate('cascade');
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description');
            $table->decimal('discount_percent', 5, 2)->nullable();
            $table->decimal('rating', 2, 1)->default(0);
            $table->boolean('prescription_required')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
