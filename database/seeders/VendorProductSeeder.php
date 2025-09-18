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
            User::FilterByRole(UserTypeEnum::VENDOR)->get()->map(function($user) use($product){
                // $random_product = $product->random(1,3);
                $randomCount = rand(1, min(5, $product->count()));
                $random_product = $product->random($randomCount);

                Log::info(count($random_product));
                foreach ($random_product as $rp) {
                    $vendor_product = $user->vendor->vendorProducts()->create([
                        'is_approved' => true,
                        'product_id' => $rp->id,
                        'is_featured' => fake()->boolean(50),
                    ]);
                    $variations = $rp->variations;
                    $temp = [];
                    foreach ($variations as $variation) {
                        $price = rand(1000, 5000);
                        $temp[] = [
                            'product_variation_id' => $variation->id,
                            'price' => $price,
                            'discount_price' => fake()->boolean(50) ? null : $price - rand(100, 200),
                            'units_in_stock' => rand(50, 200)
                        ];
                    } 
                    $vendor_product->vendorPrices()->createMany($temp);
                }
                
            });
        });
    }
}
