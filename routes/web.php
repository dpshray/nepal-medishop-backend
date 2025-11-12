<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('all-product-assigner-to-vendor', function(){
    User::find(64)->vendor->vendorProducts()->update(['is_approved' => true]);
    DB::table('vendor_product_prices')->update(['status' => true]);
    echo 'DONE';
    /* DB::transaction(function(){
        $ignore_products = User::find(64)->vendor->vendorProducts->pluck('product_id')->all();
        Product::with('variations')->whereNotIn('id',$ignore_products)->get()->each(function($product){
            // 'variations.*.product_variation_id' => 'required|exists:product_variations,id',
            // 'variations.*.units_in_stock' => 'required|numeric',
            // 'variations.*.price' => 'required|numeric',
            $product_variation = $product->variations->map(function($item){
                return [
                    'product_variation_id' => $item->id, 
                    'units_in_stock' => 500,
                    'price' => 8000
                ];
            });
            // dd($product_variation);
            User::find(64)->vendor
                ->vendorProducts()
                ->firstOrCreate(['product_id' => $product->id])
                ->vendorPrices()
                ->createMany($product_variation);
        });
    });
    echo 'DONE'; */
    /* DB::transaction(function () use ($form_data, $product) {
        $vendor_products = Auth::user()->vendor->vendorProducts();
        $vendor_product = $vendor_products->firstOrCreate(['product_id' => $product->id]);
        foreach ($form_data['variations'] as $variation) {
            $vendor_prices = $vendor_product->vendorPrices();
            $vendor_price_already_exists = $vendor_prices->firstWhere('product_variation_id', $variation['product_variation_id']);
            if ($vendor_price_already_exists) {
                $variation['units_in_stock'] = $vendor_price_already_exists->units_in_stock + $variation['units_in_stock'];
                // Log::info($vendor_price_already_exists);
                // Log::info('-----------------------------');
                // Log::info($variation);
                $vendor_price_already_exists->update($variation);
            } else {
                // Log::info('else');
                $vendor_prices->create($variation);
            }
        }
    }); */
});