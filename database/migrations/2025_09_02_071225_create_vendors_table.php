<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    # Country → Province → District → Municipality (or Rural Municipality) → Ward
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();            
            $table->unsignedBigInteger('added_by_admin_id')->nullable();
            $table->foreign('added_by_admin_id')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('store_name');
            $table->text('store_description')->nullable();
            $table->string('location');
            $table->string('country');
            $table->string('state'); #province
            $table->string('district');
            $table->string('municipality'); #city
            $table->string('postal_code');
            $table->string('bank_name');
            $table->string('bank_account_holder_name');
            $table->string('bank_account_number');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
