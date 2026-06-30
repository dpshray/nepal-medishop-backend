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
        Schema::table('products', function (Blueprint $table) {
            // Drop the existing foreign key
            $table->dropForeign(['brand_id']);

            // Make the column nullable
            $table->unsignedBigInteger('brand_id')->nullable()->change();

            // Recreate the foreign key with SET NULL
            $table->foreign('brand_id')
                ->references('id')
                ->on('brands')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);

            $table->unsignedBigInteger('brand_id')->nullable(false)->change();

            $table->foreign('brand_id')
                ->references('id')
                ->on('brands')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }
};
