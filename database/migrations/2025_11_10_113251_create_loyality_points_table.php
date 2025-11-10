<?php

use App\Models\Purchase\Order;
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
        Schema::create('loyality_points', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignIdFor(Order::class)->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->integer('points');
            $table->string('type'); #, ['earn', 'redeem']
            $table->string('source')->nullable(); // e.g., "order_purchase", "signup_bonus"
            $table->text('description')->nullable();
            $table->string('status')->default('approved');#, ['pending', 'approved', 'expired', 'cancelled']
            $table->integer('balance_after')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyality_points');
    }
};
