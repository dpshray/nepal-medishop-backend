<?php

namespace Database\Seeders;

use App\Models\FlashSale;
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

        $vendor_products = ProductVendor::select('id', 'product_id')
            ->with(['vendorPrices:id,product_vendor_id', 'product.variations'])
            ->take(50)
            ->get();
        $temp = [];
        foreach ($vendor_products as $vp) {
            foreach ($vp->product->variations as $vprc) {
                $temp[] = [
                    'vendor_product_price_id' => $vprc->id,
                    'product_id' => $vp->product_id,
                    'event_sale_price' => $vprc->platform_price,
                    'platform_price' => $vprc->platform_price - ($vprc->platform_price * rand(10, 50) / 100),
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
