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
        Schema::create('disclaimers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->longText('disclaimer');
            $table->timestamps();
        });
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('show_disclaimer')->default(0)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disclaimers');
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('show_disclaimer');
        });
    }
};
