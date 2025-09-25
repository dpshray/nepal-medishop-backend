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

        DB::transaction(function () use ($temp, $items, $randomImage, $randomKey, $product_media) {
            $price = $items * 1000;
            $package = Package::create([
                'name' => $this->randomPackageName(),
                'price' => $price,
                'discount_percent' => fake()->boolean(50) ? rand(1, 5) : null,
                'rating' => round(mt_rand(0, 500) / 100, 1),
                'description' => implode('', array_map(fn($text) => "<p>{$text}</p>", fake()->paragraphs())),
            ]);
            $package = tap($package, function($pkg) use($temp){
                $pkg->packageProducts()
                ->createMany($temp);
            });
            $package->addMedia($randomImage)->preservingOriginal()->toMediaCollection(Package::PACKAGE_FEATURED);
            unset($product_media[$randomKey]);
            foreach ($product_media as $GI) {
                $package->addMedia($GI)->preservingOriginal()->toMediaCollection(Package::PACKAGE_GALLERY);
            }
        });
    }

    function randomPackageName(): string
    {
        $adjectives = ['Starter', 'Family', 'Mega', 'Premium', 'Smart', 'Quick', 'Value', 'Super', 'Power', 'Deluxe'];
        $nouns = ['Pack', 'Bundle', 'Set', 'Box', 'Deal', 'Combo', 'Kit', 'Offer'];

        return $adjectives[array_rand($adjectives)] . ' ' . $nouns[array_rand($nouns)];
    }
}
