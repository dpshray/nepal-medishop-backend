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
        Schema::create('vendor_payouts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Which vendor this payout belongs to
            $table->unsignedBigInteger('vendor_id');
            $table->foreign('vendor_id')->references('id')->on('users')->cascadeOnDelete();

            // Period this payout covers
            $table->date('period_from');
            $table->date('period_to');

            // Financial columns
            $table->decimal('gross_sales',        12, 2)->default(0);
            $table->decimal('commission_amount',   12, 2)->default(0);  // platform keeps this
            $table->decimal('refund_adjustments',  12, 2)->default(0);  // deducted from vendor
            $table->decimal('net_payable',         12, 2)->default(0);  // gross - commission - refunds

            $table->decimal('commission_rate', 5, 2)->default(0);

            $table->string('status')->default('PENDING');

            $table->timestamp('requested_at')->nullable();
            $table->timestamp('settlement_date')->nullable();
            $table->text('remarks')->nullable();

            // Who processed it
            $table->foreignId('processed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_payouts');
    }
};
