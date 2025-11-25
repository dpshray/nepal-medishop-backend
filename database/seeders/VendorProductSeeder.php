<?php

namespace Database\Seeders;

use App\Enums\UserTypeEnum;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VendorProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $product = Product::with('variations:id,product_id')->select('id')->get();
        DB::transaction(function () use ($product){
            User::has('vendor')->get()->map(function($user) use($product){
                // $random_product = $product->random(1,3);
                $randomCount = rand(1, min(5, $product->count()));
                $random_product = $product;

                // Log::info(count($random_product));
                foreach ($random_product as $rp) {
                    $vendor_product = $user->vendor->vendorProducts()->create([
                        'is_approved' => true,
                        'product_id' => $rp->id
                    ]);
                    $variations = $rp->variations;
                    $temp = [];
                    foreach ($variations as $variation) {
                        $price = rand(1000, 5000);
                        $temp[] = [
                            'product_variation_id' => $variation->id,
                            'price' => $price,
                            'units_in_stock' => rand(50, 200),
                            'batch_number' => fake()->unixTime(),
                            'manufacture' => fake()->address(),
                            'expiry_date' => fake()->dateTimeBetween('-1 years', '5 years')
                        ];
                    } 
                    $vendor_product->vendorPrices()->createMany($temp);
                }
                
            });
        });

        // DB::transaction(function () {
        //     $ignore_products = User::firstWhere('email','vendor@gmail.com')->vendor->vendorProducts->pluck('product_id')->all();
        //     Product::with('variations')->whereNotIn('id', $ignore_products)->get()->each(function ($product) {
        //         $product_variation = $product->variations->map(function ($item) {
        //             return [
        //                 'product_variation_id' => $item->id,
        //                 'units_in_stock' => rand(50, 200),
        //                 'price' => rand(1000, 5000)
        //             ];
        //         });
        //         User::firstWhere('email', 'vendor@gmail.com')->vendor
        //             ->vendorProducts()
        //             ->firstOrCreate(['is_approved' => true,'product_id' => $product->id])
        //             ->vendorPrices()
        //             ->createMany($product_variation);
        //     });
        // });
    }
}
