<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\VendorProductStockStoreRequest;
use App\Http\Resources\Vendor\Product\VendorProductListResource;
use App\Http\Resources\Vendor\Product\VendorProductVariantResource;
use App\Models\Product;
use App\Models\ProductVendor;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VendorProductController extends Controller
{
    use PaginationTrait, ResponseTrait;
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/vendor/product",
     *     summary="Get all available products",
     *     description="Get all available products.",
     *     operationId="VendorProductList",
     *     tags={"Product"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number of list",
     *         @OA\Schema(type="integer", example=1)
     *     ),     
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Items on each page",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Available product lists.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Available product lists."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="status", type="boolean", example=true),
     *                         @OA\Property(property="is_approved_by_admin", type="boolean", example=true),
     *                         @OA\Property(property="name", type="string", example="Laudantium quo ut vel id illo ut qui commodi."),
     *                         @OA\Property(property="product_uuid", type="string", example="093db091-130c-4274-8293-2f9c7112466c"),
     *                         @OA\Property(property="brand", type="string", example="Johnson & Johnson"),
     *                         @OA\Property(property="views_count", type="integer", example=0),
     *                         @OA\Property(property="total_units_in_stock", type="integer", example=467),
     *                         @OA\Property(property="rating", type="number", format="float", example=3.6)
     *                     )
     *                 ),
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=88),
     *                 @OA\Property(property="total_items", type="integer", example=88)
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $per_page = $request->query('per_page', ProductVendor::count());
        /* $pagination = ProductVendor::with(['product.brand'])
            ->whereRelation('product','status',1)
            ->withSum(['vendorPrices as units_in_stock_sum' => function ($q) {
                $q->where('vendor_id', Auth::id());
            }], 'units_in_stock')            
            ->orderBy('units_in_stock_sum', 'DESC')
            ->paginate($per_page); */
            $pagination = ProductVendor::with(['product.brand', 'product.variations'])
                ->whereRelation('product', 'status', 1)
                ->paginate($per_page);
        $data = $this->makePaginationResponse($pagination, fn($item) => VendorProductListResource::collection($item))->data;
        return $this->apiSuccess('Available product lists.', $data);
    }

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/vendor/product-variants/{uuid}",
     *     summary="Get products variants",
     *     description="Get products variants.",
     *     operationId="VendorProductVariant",
     *     tags={"Product"},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of a product",
     *         @OA\Schema(type="string", example="54115543-b75c-4013-bf19-7aa4efd35240")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Available product lists",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Available product lists"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="status", type="integer", example=1, description="Product status (1=active, 0=inactive)"),
     *                         @OA\Property(property="is_approved_by_admin", type="integer", example=1, description="Approval status by admin"),
     *                         @OA\Property(property="name", type="string", example="Laborum sunt facilis odio enim alias."),
     *                         @OA\Property(property="uuid", type="string", format="uuid", example="54115543-b75c-4013-bf19-7aa4efd35240"),
     *                         @OA\Property(property="brand", type="string", example="Pfizer"),
     *                         @OA\Property(property="views_count", type="integer", example=0),
     *                         @OA\Property(property="total_units_in_stock", type="integer", example=753),
     *                         @OA\Property(property="rating", type="number", format="float", example=2.5)
     *                     )
     *                 ),
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=96),
     *                 @OA\Property(property="total_items", type="integer", example=96)
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function productVariants(Product $product) {
        $variants = $product->variations;
        $data = VendorProductVariantResource::collection($variants);
        return $this->apiSuccess('Product variants.', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/vendor/product/{uuid}",
     *     summary="Add stock for vendor products",
     *     description="Add stock details for product variations",
     *     operationId="AddVendorStock",
     *     tags={"Product"},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of a product",
     *         @OA\Schema(type="string", example="54115543-b75c-4013-bf19-7aa4efd35240")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="stock",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="product_variation_id", type="integer", example=1),
     *                     @OA\Property(property="units_in_stock", type="integer", example=50),
     *                     @OA\Property(property="price", type="number", format="float", example=500)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Stock added successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Stock added successfully."),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function store(VendorProductStockStoreRequest $request)
    {

        $vendor_product = Auth::user()->vendorProducts();
        $product = Product::where('uuid', $request->product_uuid)->firstOrFail();
        if ($vendor_product->firstWhere('product_id', $product->id)) {
            return $this->apiError('Stock entry for this product already exists in your vendor list.');
        }
        $invalidIds = collect($request->stock)
            ->pluck('product_variation_id')
            ->diff($product->variations->pluck('id'));
        if ($invalidIds->isNotEmpty()) {
            return $this->apiError('Some product variation IDs are invalid.');
        }
        DB::transaction(fn() => $vendor_product->create(['product_id' => $product->id])->vendorPrices()->createMany($request->variations));
        return $this->apiSuccess('Stock added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
