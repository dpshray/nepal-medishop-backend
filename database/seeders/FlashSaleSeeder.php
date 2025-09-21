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
                $temp[$vp->id][] = [
                    'product_variation_id' => $vprc->id,
                    'event_sale_price' => $vprc->platform_price,
                    'stock_limit' => rand(20, 25),
                ];
            }
        }
        DB::transaction(function () use ($temp) {
            $se = SaleEvent::create([
                'title' => 'Flash Sale',
                'start_timestamps' => now()->startOfDay(),
                'end_timestamps' => now()->endOfDay(),
            ]);
            foreach ($temp as $pid => $item) {
                $se->saleEventProducts()
                    ->create(['product_id' => $pid])
                    ->saleEventProductPrices()
                    ->createMany($item);
            }
        });
    }
}
