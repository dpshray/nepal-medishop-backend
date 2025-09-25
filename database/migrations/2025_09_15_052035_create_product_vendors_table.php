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
        Schema::create('product_vendors', function (Blueprint $table) {
            $table->id();
            $table->boolean('status')->default(1)->comment('Vendor control: 1 = visible to admin, 0 = hidden by vendor');
            $table->boolean('is_approved')->default(0)->comment('Admin approval status: 1 = approved by admin (vendor’s marked price validated), 0 = not yet approved');
            $table->unsignedBigInteger('vendor_id');
            $table->foreign('vendor_id')->references('id')->on('vendors')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->boolean('is_featured')->default(0);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_vendors');
    }
};
