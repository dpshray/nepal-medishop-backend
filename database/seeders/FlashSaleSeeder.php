<?php

namespace Database\Seeders;

use App\Models\FlashSale;
use App\Models\Package;
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

        $items = rand(5, 10);
        $products = Product::select('id')
            ->with(['variations'])
            ->inRandomOrder()
            ->take($items)
            ->get();
        $temp = [];
        foreach ($products as $p) {
            $temp[] = [
                'product_variation_id' => $p->variations->random()->id 
            ];
        }
        Log::info($temp);
        
        $img_A = public_path('assets/img/packages/package-1.jpg');
        $img_B   = public_path('assets/img/packages/package-2.jpg');
        $img_C   = public_path('assets/img/packages/package-3.jpg');
        $img_D   = public_path('assets/img/packages/package-4.jpg');
        $img_E   = public_path('assets/img/packages/package-5.jpg');

        $product_media = [
            $img_A,
            $img_B,
            $img_C,
            $img_D,
            $img_E,
        ];
        $randomKey   = array_rand($product_media);
        $randomImage = $product_media[$randomKey];
        
        DB::transaction(function () use ($temp, $items, $randomImage) {
            $package = Package::create([
                'title' => $this->randomPackageName(),
                'price' => $items * 1000
            ]);
            tap($package, function($pkg) use($temp){
                $pkg->packageProducts()
                ->createMany($temp);
            })->addMedia($randomImage)->preservingOriginal()->toMediaCollection(Package::PACKAGE_MEIDA);
        });
    }

    function randomPackageName(): string
    {
        $adjectives = ['Starter', 'Family', 'Mega', 'Premium', 'Smart', 'Quick', 'Value', 'Super', 'Power', 'Deluxe'];
        $nouns = ['Pack', 'Bundle', 'Set', 'Box', 'Deal', 'Combo', 'Kit', 'Offer'];

        return $adjectives[array_rand($adjectives)] . ' ' . $nouns[array_rand($nouns)];
    }
}
