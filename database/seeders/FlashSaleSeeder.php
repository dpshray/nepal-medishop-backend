<?php

namespace Database\Seeders;

use App\Models\FlashSale;
use App\Models\Product;
use App\Models\ProductVendor;
use App\Models\SaleEvent;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FlashSaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $vendor_products = Product::select('id')
            ->with(['variations'])
            ->inRandomOrder()
            ->take(50)
            ->get();
        $temp = [];
        foreach ($vendor_products as $vp) {
            foreach ($vp->variations as $vprc) {
                $temp[] = [
                    'product_variation_id' => $vprc->id,
                    // 'product_id' => $vp->product_id,
                    'event_sale_price' => $vprc->platform_price,
                    // 'platform_price' => $vprc->platform_price - ($vprc->platform_price * rand(10, 50) / 100),
                    'stock_limit' => rand(20, 25),
                    'max_purchase' => 2,
                ];
            }
        }
        DB::transaction(function () use ($temp) {
            SaleEvent::create([
                'title' => 'Flash Sale',
                'start_timestamps' => now()->startOfDay(),
                'end_timestamps' => now()->endOfDay(),
            ])
                ->saleEventProducts()
                ->createMany($temp);
        });
    }
}
