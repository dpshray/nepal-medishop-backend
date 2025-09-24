<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\Package\PackageDetailResource;
use App\Http\Resources\User\Package\PackageSingleResource;
use App\Http\Resources\User\Product\Brand\ClientBrandResource;
use App\Http\Resources\User\Product\Card\ProductCardResource;
use App\Http\Resources\User\Product\Category\ClientCategoryResource;
use App\Http\Resources\User\Product\ProductDetailResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Package;
use App\Models\Product;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterDataController extends Controller
{
    use ResponseTrait, PaginationTrait;

    /**
     * @OA\Get(
     *     path="/get-brand-list",
     *     summary="Get all active brand",
     *     description="Get all active brand.",
     *     operationId="ClientBrandList",
     *     tags={"Product"},
     *     @OA\Response(
     *         response=200,
     *         description="List of active brands",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="List of active brands"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="slug", type="string", example="pfizer"),
     *                     @OA\Property(property="name", type="string", example="Pfizer"),
     *                     @OA\Property(property="image", type="string", example="http://127.0.0.1:8000/assets/img/default-brand-category.png"),
     *                     @OA\Property(property="is_featured", type="integer", example=1),
     *                     @OA\Property(property="is_popular", type="integer", example=0)
     *                 )
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     *  )
     * )
     */
    function fetchAllActiveBrand(){
        $brands = Brand::with('media')->active()->get();
        $brands = ClientBrandResource::collection($brands);
        return $this->apiSuccess('List of active brands', $brands);
    }

    /**
     * @OA\Get(
     *     path="/get-category-list",
     *     summary="Get all active categories",
     *     description="Get all active categories.",
     *     operationId="ClientCategoryList",
     *     tags={"Product"},
     *     @OA\Response(
     *         response=200,
     *         description="List of active categories",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="List of active categories"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="slug", type="string", example="pfizer"),
     *                     @OA\Property(property="name", type="string", example="Pfizer"),
     *                     @OA\Property(
     *                         property="image",
     *                         type="string",
     *                         format="url",
     *                         example="http://127.0.0.1:8000/assets/img/default-brand-category.png"
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
    */
    function fetchAllActiveCategory(){
        $categories = Category::with('media')->active()->get();
        $categories = ClientCategoryResource::collection($categories);
        return $this->apiSuccess('List of active categories', $categories);
    }

    /**
     * @OA\Get(
     *     path="/products/",
     *     summary="Get product based on category slug",
     *     description="Get product based on category slug.",
     *     operationId="ClientProductList",
     *     tags={"Product"},
     *     @OA\Parameter(
     *         name="category_slug",
     *         in="query",
     *         required=false,
     *         description="slug of a category('all' to fetch all random products)",
     *         @OA\Schema(type="string", example="all")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Item per page",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Api page number",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="All product lists",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="All product lists."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="name", type="string", example="Consequuntur sunt suscipit dolorem maxime ut."),
     *                         @OA\Property(property="slug", type="string", example="consequuntur-sunt-suscipit-dolorem-maxime-ut"),
     *                         @OA\Property(property="brand", type="string", example="AstraZeneca"),
     *                         @OA\Property(property="rating", type="number", format="float", example=3.7),
     *                         @OA\Property(property="price", type="number", format="float", example=2016.7),
     *                         @OA\Property(property="previous_price", type="number", format="float", nullable=true, example=2345),
     *                         @OA\Property(property="feature_image", type="string", example="http://192.168.100.23:8008/storage/576/visc-inhaler.jpg"),
     *                         @OA\Property(property="liked", type="boolean", example=false)
     *                     )
     *                 ),
     *                 @OA\Property(property="page_no", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=30),
     *                 @OA\Property(property="total_items", type="integer", example=300)
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function fetchProducts(Request $request){
        $per_page = $request->query('per_page', 10);
        $category_slug = $request->query('category_slug');
        $query = Product::with(['media','brand', 'cheapestVariation'])->active();
        if ($category_slug == 'all') {
            $query = $query->inRandomOrder();
        } else{
            $query = $query->whereRelation('categories','slug', $category_slug)->latest('id');
        }
        $pagination = $query->paginate($per_page);
        $data = $this->setDataKey(['page' => 'page_no'])->makePaginationResponse($pagination, fn($item) => ProductCardResource::collection($item))->data;
        return $this->apiSuccess("All product lists.", $data);
    }

    /**
     * @OA\Get(
     *     path="/product/{slug}",
     *     summary="Show an product",
     *     description="Show an active brand.",
     *     operationId="ProductShow",
     *     tags={"Product"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug of product",
     *         @OA\Schema(type="string", example="sun-pharma")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product detail response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Product detail fetched successfully."),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="Incidunt ratione recusandae dolor quod."),
     *                 @OA\Property(property="slug", type="string", example="incidunt-ratione-recusandae-dolor-quod"),
     *                 @OA\Property(property="brand", type="string", example="AstraZeneca"),
     *                 @OA\Property(property="description", type="string", example="<p>Qui impedit consectetur necessitatibus eos iste odio...</p>"),
     *                 @OA\Property(property="added_date", type="string", format="date", example="2025-09-24"),
     *                 @OA\Property(
     *                     property="categories",
     *                     type="array",
     *                     @OA\Items(type="string", example="Eye Care")
     *                 ),
     *                 @OA\Property(
     *                     property="tags",
     *                     type="array",
     *                     @OA\Items(type="string", example="Doxycycline")
     *                 ),
     *                 @OA\Property(
     *                     property="variations",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="variation_id", type="integer", example=322),
     *                         @OA\Property(property="size_value", type="number", example=500),
     *                         @OA\Property(property="size_unit", type="string", example="gm"),
     *                         @OA\Property(property="price", type="number", format="float", example=1778.28),
     *                         @OA\Property(property="previous_price", type="number", format="float", nullable=true, example=2044)
     *                     )
     *                 ),
     *                 @OA\Property(property="featured_image", type="string", example="http://192.168.100.23:8008/storage/601/medi-plaster.png"),
     *                 @OA\Property(
     *                     property="gallery_images",
     *                     type="array",
     *                     @OA\Items(type="string", example="http://192.168.100.23:8008/storage/602/tablets.jpg")
     *                 ),
     *                 @OA\Property(property="liked", type="boolean", example=false)
     *             )
     *         )
     *     )
     * )
     */
    function fetchProductDetail(Product $product){
        $product->loadMissing(['media', 'categories','tags','variations','brand']);
        $data = new ProductDetailResource($product);
        return $this->apiSuccess("Product detail fetched successfully.", $data);
    }

    /**
     * @OA\Get(
     *     path="/packages",
     *     summary="Get package list",
     *     description="Get package list.",
     *     operationId="ClientPackageList",
     *     tags={"Package"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Item per page",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Api page number",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Package list response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Package lists"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="name", type="string", example="Mega Combo"),
     *                         @OA\Property(property="slug", type="string", example="mega-combo"),
     *                         @OA\Property(property="price", type="number", format="float", example=3640),
     *                         @OA\Property(property="previous_price", type="number", format="float", nullable=true, example=7000),
     *                         @OA\Property(property="rating", type="number", format="float", example=2.8),
     *                         @OA\Property(property="image", type="string", example="http://192.168.100.23:8008/storage/1642/package-4.jpg"),
     *                         @OA\Property(property="liked", type="boolean", example=false)
     *                     )
     *                 ),
     *                 @OA\Property(property="page_no", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=1),
     *                 @OA\Property(property="total_items", type="integer", example=8)
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function fetchPackages(Request $request){
        $per_page = $request->query('per_page', 10);
        $pagination = Package::with('media')->orderBy('id','DESC')->paginate($per_page);
        $data = $this->setDataKey(['page' => 'page_no'])->makePaginationResponse($pagination, fn($item) => PackageSingleResource::collection($item))->data;
        return $this->apiSuccess('Package lists', $data);
    }

    /**
     * @OA\Get(
     *     path="/package/{slug}",
     *     summary="Show a package",
     *     description="Show a package.",
     *     operationId="PackageShow",
     *     tags={"Package"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug of package",
     *         @OA\Schema(type="string", example="sun-pharma")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Package details retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Package details retrieved successfully."),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="Mega Combo"),
     *                 @OA\Property(property="description", type="string", example="<p>Eos veniam aliquam qui ex...</p>"),
     *                 @OA\Property(property="price", type="number", format="float", example=7000),
     *                 @OA\Property(property="discount_price", type="number", format="float", example=3640),
     *                 @OA\Property(property="rating", type="number", format="float", example=2.8),
     *                 @OA\Property(property="featured_image", type="string", example="http://192.168.100.23:8008/storage/1642/package-4.jpg"),
     *                 @OA\Property(
     *                     property="gallery_images",
     *                     type="array",
     *                     @OA\Items(type="string", example="http://192.168.100.23:8008/storage/1643/package-1.jpg")
     *                 ),
     *                 @OA\Property(
     *                     property="products",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="image", type="string", example="http://192.168.100.23:8008/storage/236/tablets.jpg"),
     *                         @OA\Property(property="product_name", type="string", example="Magni voluptas maiores quia consequuntur."),
     *                         @OA\Property(property="slug", type="string", example="magni-voluptas-maiores-quia-consequuntur"),
     *                         @OA\Property(property="size_value", type="integer", example=650),
     *                         @OA\Property(property="size_unit", type="string", example="gm"),
     *                         @OA\Property(property="price", type="number", format="float", example=3468)
     *                     )
     *                 ),
     *                 @OA\Property(property="liked", type="boolean", example=false)
     *             )
     *         )
     *     )
     * )
     */
    function fetchPackageDetail(Package $package){
        $package->loadMissing(['media', 'packageProducts.variant.product.media', 'packageProducts.variant.product.categories']);
        $data = new PackageDetailResource($package);
        return $this->apiSuccess("Package details retrieved successfully.", $data);
    }

}