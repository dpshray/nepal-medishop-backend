<?php

namespace App\Http\Controllers\V1\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\Banner\BannerCollection;
use App\Http\Resources\Categories\CategoriesCollection;
use App\Http\Resources\Product\ProductCollection;
use App\Http\Resources\Product\ProductDetailResource;
use App\Models\Banner;
use App\Models\Categories;
use App\Models\Product;
use App\ResponseTrait;
use Illuminate\Http\Request;

class MainController extends Controller
{
    //
    use ResponseTrait;
    function product(Request $request)
    {
        $page = $request->query('page');
        $category = $request->query('category');
        $product_name = $request->query('search');
        $limit = $request->input('limit', 9);
        $sort = $request->query('sort');
        $query = Product::with(['categories', 'variants']);
        if ($category) {
            $query->whereHas('categories', function ($q) use ($category) {
                $q->where('name', $category);
            });
        }
        if ($product_name) {
            $query->where('name', 'like', '%' . $product_name . '%');
        }
        // Sorting logic
        if ($sort === 'price_high') {
            $query->orderByRaw('COALESCE(discount_price, price) DESC');
        } elseif ($sort === 'price_low') {
            $query->orderByRaw('COALESCE(discount_price, price) asc');
        } elseif ($sort === 'discount') {
            $query->orderByRaw('(price - discount_price) DESC');
        } elseif ($sort === 'new') {
            $query->orderBy('created_at', 'desc');
        }
        //  elseif ($sort === 'best_seller') {
        //     // Add total_sold column using relationship
        //     $query->withCount(['variants as total_sold' => function ($q) {
        //         $q->join('order_items', 'variants.id', '=', 'order_items.variant_id')
        //             ->join('orders', 'orders.id', '=', 'order_items.order_id')
        //             ->where('orders.status', 'completed') // optional filter
        //             ->select(DB::raw('COALESCE(SUM(order_items.quantity), 0)'));
        //     }])
        //         ->orderByDesc('total_sold');
        // }

        $products = $query->orderBy('created_at', 'desc')->paginate($limit);

        if ($products->isEmpty()) {
            return $this->apiError('Product not found');
        }

        $data = new ProductCollection($products);
        return $this->apiSuccess('Products retrieved successfully', $data);
    }

    function categories(Request $request)
    {
        $limit = $request->input('limit', 9);
        $categories = Categories::paginate($limit);
        if (!$categories) {
            return $this->apiError('categories');
        }
        $data = new CategoriesCollection($categories);
        return $this->apiSuccess('categories was found', $data);
    }

    function product_detail($slug)
    {
        $product = Product::with('variants', 'categories')->where('slug', $slug)->first();
        if (!$product) {
            return $this->apiError('product not found');
        }
        $data = new ProductDetailResource($product);
        return $this->apiSuccess('product was found', $data);
    }
    function Banner()
    {
        $banner=Banner::all();
        if (!$banner) {
            return $this->apiError('Main banner not found');
        }
        $banner = new BannerCollection($banner);
        return $this->apiSuccess('Banner data', $banner);
    }
}
