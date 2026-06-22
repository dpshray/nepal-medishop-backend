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
        Schema::table('vendors', function (Blueprint $table) {
            //
            $table->string('location')->nullable()->change();
            $table->string('country')->nullable()->change();
            $table->string('state')->nullable()->change(); #province
            $table->string('district')->nullable()->change();
            $table->string('municipality')->nullable()->change(); #city
            $table->string('postal_code')->nullable()->change();
            $table->string('bank_name')->nullable()->change();
            $table->string('bank_account_holder_name')->nullable()->change();
            $table->string('bank_account_number')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            //
            $table->string('location')->change();
            $table->string('country')->change();
            $table->string('state')->change(); #province
            $table->string('district')->change();
            $table->string('municipality')->change(); #city
            $table->string('postal_code')->change();
            $table->string('bank_name')->change();
            $table->string('bank_account_holder_name')->change();
            $table->string('bank_account_number')->change();
        });
    }
};
