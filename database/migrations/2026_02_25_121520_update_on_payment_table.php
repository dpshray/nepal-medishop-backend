<?php

use App\Enums\Purchase\PaymentStatusEnum;
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
        Schema::table('payments', function (Blueprint $table) {
            //
            $table->dropColumn('order_id');
            $table->morphs('payable');
        });
        Schema::table('service_bookings', function (Blueprint $table) {
            $table->string('payment_status')->default(PaymentStatusEnum::PENDING->value)->after('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            //
            $table->dropColumn('payable_id');
            $table->dropColumn('payable_type');
            $table->unsignedBigInteger('order_id');
        });
        Schema::table('service_bookings', function (Blueprint $table) {
            $table->dropColumn('payment_status');
        });
    }
};
