<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Enums\SettingEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\Package\PackageDetailResource;
use App\Http\Resources\User\Package\PackageSingleResource;
use App\Http\Resources\User\Product\Brand\ClientBrandResource;
use App\Http\Resources\User\Product\Card\ProductCardResource;
use App\Http\Resources\User\Product\Category\ClientCategoryResource;
use App\Http\Resources\User\Product\HealthCondition\ClientHealthConditionListResource;
use App\Http\Resources\User\Product\ProductDetailResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\HealthCondition;
use App\Models\Package;
use App\Models\Product;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
     *     @OA\Parameter(
     *         name="brand",
     *         in="query",
     *         required=false,
     *         description="name of a brand to search.('empty' to fetch all data)",
     *         @OA\Schema(type="string", example="pfizer")
     *     ),
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
    function fetchAllActiveBrand(Request $request){
        $brand_name = $request->query('brand');
        $brands = Brand::with('media')
            ->active()
            ->when($brand_name, fn($qry) => $qry->whereLike('name', '%'.$brand_name.'%'))
            ->get();
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
     *     path="/get-health-condition-list",
     *     summary="Get all active health conditions",
     *     description="Get all active health conditions.",
     *     operationId="ClientHealthConsitionList",
     *     tags={"Product"},
     *     @OA\Parameter(
     *         name="health_condition",
     *         in="query",
     *         required=false,
     *         description="name of a health condition to search.('empty' to fetch all data)",
     *         @OA\Schema(type="string", example="immunity boosters")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of health conditions.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="List of health conditions."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="name", type="string", example="Hormonal Care"),
     *                     @OA\Property(property="slug", type="string", example="hormonal-care"),
     *                     @OA\Property(property="description", type="string", nullable=true, example="Products that help regulate hormones, including thyroid, adrenal, and reproductive hormones."),
     *                     @OA\Property(property="image", type="string", format="url", example="http://192.168.100.23:8008/storage/2646/hormonal-balance-icon-design-vector.jpg")
     *                 )
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     * )
     */
    function fetchAllHealthCondition(Request $request) {
        $query_health_condition = $request->query('health_condition');
        $health_conditions = HealthCondition::with('media')
            ->when($query_health_condition, fn($qry) => $qry->whereLike('name', '%'. $query_health_condition.'%'))
            ->get();
        $health_conditions = ClientHealthConditionListResource::collection($health_conditions);
        return $this->apiSuccess('List of health conditions.', $health_conditions);
    }

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}}, 
     *     path="/products/",
     *     summary="Get product based on category slug",
     *     description="Get product based on category slug.",
     *     operationId="ClientProductList",
     *     tags={"Product"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="search product using name",
     *         @OA\Schema(type="string", example="")
     *     ),
     *     @OA\Parameter(
     *         name="brand_slug",
     *         in="query",
     *         required=false,
     *         description="slug of a brand",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="category_slug",
     *         in="query",
     *         required=false,
     *         description="slug of a category",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="health_condition_slug",
     *         in="query",
     *         required=false,
     *         description="slug of a health condition",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="list_type",
     *         in="query",
     *         required=false,
     *         description="Fetch item list based on type(default fetches latest products).(Types values are: random)",
     *         @OA\Schema(type="string")
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
     *         description="Successful response",
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
     *                         @OA\Property(property="name", type="string", example="Sit quas consequatur dignissimos voluptatem."),
     *                         @OA\Property(property="slug", type="string", example="sit-quas-consequatur-dignissimos-voluptatem"),
     *                         @OA\Property(property="brand", type="string", example="Roche"),
     *                         @OA\Property(property="isPrescriptionRequired", type="boolean", example=true),
     *                         @OA\Property(property="rating", type="number", format="float", example=3.5),
     *                         @OA\Property(property="price", type="number", format="float", example=137),
     *                         @OA\Property(property="previous_price", type="number", format="float", nullable=true, example=null),
     *                         @OA\Property(property="feature_image", type="string", example="http://192.168.100.23:8008/storage/2061/tablets.jpg"),
     *                         @OA\Property(property="liked", type="boolean", example=false),
     *                         @OA\Property(property="discount_percent", type="number", format="float", example=5),
     *                         @OA\Property(
     *                             property="variations",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="variation_id", type="integer", example=1154),
     *                                 @OA\Property(property="name", type="string", example="Variant-1"),
     *                                 @OA\Property(property="size_value", type="integer", example=100),
     *                                 @OA\Property(property="size_unit", type="string", example="patch"),
     *                                 @OA\Property(property="price", type="number", format="float", example=137),
     *                                 @OA\Property(property="previous_price", type="number", format="float", nullable=true, example=null)
     *                             )
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="page_no", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=51),
     *                 @OA\Property(property="total_items", type="integer", example=501)
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     * )
     */
    function fetchProducts(Request $request){
        $per_page = $request->query('per_page', 10);
        $category_slug = $request->query('category_slug');
        $brand_slug = $request->query('brand_slug');
        $health_condition_slug = $request->query('health_condition_slug');
        $list_type = $request->query('list_type');
        $search = $request->query('search');
        
        $query = Product::with([
            'media',
            'brand', 
            'cheapestVariation', 
            'likes' => fn($qry) => $qry->where('user_id', Auth::id()),
            'variations'
            ])
            ->active()
            ->when($search, fn($qry,$search) => $qry->whereLike('name', "%$search%"))
            ->when($category_slug, fn($qry) => $qry->whereRelation('categories', 'slug', $category_slug)->latest('id'))
            ->when($brand_slug, fn($qry) => $qry->whereRelation('brand','slug', $brand_slug))
            ->when($health_condition_slug, fn($qry) => $qry->whereRelation('healthConditions','slug', $health_condition_slug))
            ->when($list_type, function($qry,$value){
                if ($value == 'random') {
                    $qry->inRandomOrder(); 
                }
            },fn($qry) => $qry->latest());

        $pagination = $query->paginate($per_page);
        $data = $this->setDataKey(['page' => 'page_no'])->makePaginationResponse($pagination, fn($item) => ProductCardResource::collection($item))->data;
        return $this->apiSuccess("All product lists.", $data);
    }

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}}, 
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
     *         description="Product detail fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product detail fetched successfully."),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="Placeat earum iusto iste perspiciatis vel."),
     *                 @OA\Property(property="slug", type="string", example="placeat-earum-iusto-iste-perspiciatis-vel"),
     *                 @OA\Property(property="brand", type="string", example="Bristol-Myers Squibb"),
     *                 @OA\Property(property="description", type="string", example="<p>Omnis corporis aut a aut et ut sunt...</p>"),
     *                 @OA\Property(property="added_date", type="string", format="date", example="2025-09-22"),
     *                 @OA\Property(property="isPrescriptionRequired", type="boolean", example=true),
     *                 @OA\Property(property="rating", type="number", format="float", example=3.8),
     *                 @OA\Property(
     *                     property="categories",
     *                     type="array",
     *                     @OA\Items(type="string", example="Weight Management")
     *                 ),
     *                 @OA\Property(
     *                     property="tags",
     *                     type="array",
     *                     @OA\Items(type="string", example="Sunscreen")
     *                 ),
     *                 @OA\Property(
     *                     property="variations",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="variation_id", type="integer", example=1132),
     *                         @OA\Property(property="name", type="string", example="Variant-1"),
     *                         @OA\Property(property="size_value", type="integer", example=100),
     *                         @OA\Property(property="size_unit", type="string", example="ml"),
     *                         @OA\Property(property="price", type="number", format="float", example=167),
     *                         @OA\Property(property="previous_price", type="number", format="float", nullable=true, example=null)
     *                     )
     *                 ),
     *                 @OA\Property(property="featured_image", type="string", format="url", example="http://192.168.100.23:8008/storage/1881/syrup.jpg"),
     *                 @OA\Property(
     *                     property="gallery_images",
     *                     type="array",
     *                     @OA\Items(type="string", format="url", example="http://192.168.100.23:8008/storage/1882/tablets.jpg")
     *                 ),
     *                 @OA\Property(property="liked", type="boolean", example=false)
     *             )
     *         )
     *     )
     * )
     */
    function fetchProductDetail(Product $product){
        $product->loadMissing(['media', 'categories','tags','variations','brand','likes' => fn($qry) => $qry->where('user_id', Auth::id())]);
        $data = new ProductDetailResource($product);
        return $this->apiSuccess("Product detail fetched successfully.", $data);
    }

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}}, 
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
     *                         @OA\Property(property="discount_percent", type="number", format="float", example=3),
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
        $pagination = Package::with(['media', 'likes' => fn($qry) => $qry->where('user_id', Auth::id())])->active()->orderBy('id','DESC')->paginate($per_page);
        $data = $this->setDataKey(['page' => 'page_no'])->makePaginationResponse($pagination, fn($item) => PackageSingleResource::collection($item))->data;
        return $this->apiSuccess('Package lists', $data);
    }

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}}, 
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
     *         description="Package details retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Package details retrieved successfully."),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="Super Offer"),
     *                 @OA\Property(property="slug", type="string", example="super-offer"),
     *                 @OA\Property(property="description", type="string", example="<p>Package details...</p>"),
     *                 @OA\Property(property="price", type="number", format="float", example=9000),
     *                 @OA\Property(property="discount_price", type="number", format="float", example=8910),
     *                 @OA\Property(property="discount_percent", type="number", format="float", example=3),
     *                 @OA\Property(property="rating", type="number", format="float", example=2.8),
     *                 @OA\Property(property="featured_image", type="string", example="http://192.168.100.23:8008/storage/2636/package-1.jpg"),
     *                 @OA\Property(
     *                     property="gallery_images",
     *                     type="array",
     *                     @OA\Items(type="string", example="http://192.168.100.23:8008/storage/2637/package-2.jpg")
     *                 ),
     *                 @OA\Property(
     *                     property="products",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="image", type="string", example="http://192.168.100.23:8008/storage/2021/cream.jpg"),
     *                         @OA\Property(property="product_name", type="string", example="At odio sed velit numquam."),
     *                         @OA\Property(property="slug", type="string", example="at-odio-sed-velit-numquam"),
     *                         @OA\Property(property="size_value", type="number", example=100),
     *                         @OA\Property(property="size_unit", type="string", example="IU"),
     *                         @OA\Property(property="price", type="number", format="float", example=142),
     *                         @OA\Property(property="brand", type="string", example="Abbott"),
     *                         @OA\Property(property="variant_name", type="string", example="Variant-8")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="categories",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="name", type="string", example="Heart Care"),
     *                         @OA\Property(property="slug", type="string", example="heart-care")
     *                     )
     *                 ),
     *                 @OA\Property(property="liked", type="boolean", example=false)
     *             )
     *         )
     *     )
     * )
     */
    function fetchPackageDetail(Package $package){
        $package->loadMissing([
            'media', 
            'packageProducts.variant.product.media', 
            'packageProducts.variant.product.categories',
            'packageProducts.variant.product.brand', 
            'likes' => fn($qry) => $qry->where('user_id', Auth::id())]);
        $data = new PackageDetailResource($package);
        return $this->apiSuccess("Package details retrieved successfully.", $data);
    }

    /**
     * @OA\Get(
     *     path="/settings",
     *     summary="Get list of settings.",
     *     description="Retrieve the list of application settings with related data.",
     *     operationId="SettingList",
     *     tags={"Setting"},
     *     @OA\Response(
     *         response=200,
     *         description="App settings fetched successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="app settings fetched successfully"),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="name", type="string", example="GIFT_WRAP_CHARGE"),
     *                     @OA\Property(property="value", type="number", example=300)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    function fetchSettings() {
        $settings = DB::table('settings')->select('key','value')->get()->map(function($item){
            if ($item->key == SettingEnum::GIFT_WRAP_CHARGE->value) {
                return [
                    'name' => $item->key,
                    'value' => (float) $item->value
                ];
            }
        });
        return $this->apiSuccess('app settings fetched successfully', $settings);
    }
}