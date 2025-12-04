<?php

use App\Models\Product\Service\ServiceBooking;
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
        Schema::create('service_booking_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ServiceBooking::class)->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('type');
            $table->string('code')->nullable();
            $table->decimal('discount_amount',10,2);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_booking_discounts');
    }
};
