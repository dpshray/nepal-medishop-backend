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
        Schema::table('orders', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('used_coupon_code_id')->nullable()->after('price');
            $table->foreign('used_coupon_code_id')->references('id')->on('coupon_codes')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            $table->dropForeign(['used_coupon_code_id']);
            $table->dropColumn('used_coupon_code_id');
        });
    }
};
