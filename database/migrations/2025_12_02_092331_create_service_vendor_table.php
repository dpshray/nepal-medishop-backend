<?php

use App\Models\Product\Service\Service;
use App\Models\Vendor;
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
        Schema::create('service_vendor', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_available')->default(true);
            $table->foreignIdFor(Service::class)->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignIdFor(Vendor::class)->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->decimal('price', 10, 2);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_vendor');
    }
};
