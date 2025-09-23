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
        DB::transaction(function () use ($temp, $items) {
            Package::create([
                'title' => $this->randomPackageName(),
                'price' => $items * 1000
            ])
            ->packageProducts()
            ->createMany($temp);
        });
    }

    function randomPackageName(): string
    {
        $adjectives = ['Starter', 'Family', 'Mega', 'Premium', 'Smart', 'Quick', 'Value', 'Super', 'Power', 'Deluxe'];
        $nouns = ['Pack', 'Bundle', 'Set', 'Box', 'Deal', 'Combo', 'Kit', 'Offer'];

        return $adjectives[array_rand($adjectives)] . ' ' . $nouns[array_rand($nouns)];
    }
}
