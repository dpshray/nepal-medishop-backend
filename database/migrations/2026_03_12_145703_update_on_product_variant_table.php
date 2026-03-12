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
        Schema::table('product_variations', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->string('form_type');
            $table->string('package_type');
            $table->string('package_size');
            $table->string('strength');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variations', function (Blueprint $table) {
            //
            $table->string('name')->nullable(false)->change();
            $table->dropColumn([
                'form_type',
                'package_type',
                'package_size',
                'strength'
            ]);
        });
    }
};
