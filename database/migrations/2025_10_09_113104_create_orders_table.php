<?php

use App\Models\User;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->string('order_code');
            $table->string('order_type');
            $table->foreignIdFor(User::class)->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedBigInteger('assigned_vendor_id')->nullable();
            $table->foreign('assigned_vendor_id')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('user_type');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile')->nullable();
            $table->string('address');
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->boolean('gift_wrap')->default(0);
            $table->text('gift_wrap_remarks')->nullable();
            $table->decimal('gift_wrap_charge', 10, 2)->nullable();
            $table->string('payment_method');
            $table->string('payment_status');
            $table->string('status');
            $table->json('metadata')->nullable();
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
