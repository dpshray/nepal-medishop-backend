<?php

namespace Database\Seeders;

use App\Models\FlashSale;
use App\Models\ProductVendor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FlashSaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendor_products = ProductVendor::select('id', 'product_id')
            ->with('vendorPrices:id,product_vendor_id,platform_price')
            ->take(50)
            ->get();
        $temp = [];
        foreach ($vendor_products as $vp) {
            foreach ($vp->vendorPrices as $vprc) {
                $temp[] = [
                    'vendor_product_price_id' => $vprc->id,
                    'flash_sale_price' => $vprc->platform_price,
                    'platform_price' => $vprc->platform_price - ($vprc->platform_price * rand(25,50)/100),
                    'stock_limit' => 25,
                    'max_purchase' => 2,
                ];
            }
        }
        DB::transaction(function () use($temp){
            FlashSale::create([
                'title' => 'Flash Sale',
                'start_timestamps' => now()->startOfDay(),
                'end_timestamps' => now()->endOfDay(),
            ])
            ->flashProducts()
            ->createMany($temp);
        });
    }
}
