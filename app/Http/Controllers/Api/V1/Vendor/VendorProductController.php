<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\VendorProductStockStoreRequest;
use App\Http\Resources\Admin\Vendor\AdminVendorProductDetailResource;
use App\Http\Resources\Vendor\Product\VendorProductListResource;
use App\Http\Resources\Vendor\Product\VendorProductVariantResource;
use App\Http\Resources\Vendor\Product\VendorStockedProductListResource;
use App\Models\Product;
use App\Models\ProductVendor;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VendorProductController extends Controller
{
    use PaginationTrait, ResponseTrait;
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/vendor/available-product",
     *     summary="Get all currently available products.",
     *     description="Get all currently available products.",
     *     operationId="AvailableProductList",
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
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Items to search",
     *         @OA\Schema(type="string", example="Savlon")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Available product lists.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Available product lists."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="product_uuid", type="string", example="19a8e9db-2f17-40a6-ae8d-1fe3c75ba62d"),
     *                         @OA\Property(property="product_name", type="string", example="Porro rerum autem aut odit."),
     *                         @OA\Property(property="brand", type="string", example="Bayer"),
     *                         @OA\Property(
     *                             property="variations",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="name", type="string", example="Variant-1"),
     *                                 @OA\Property(property="size_value", type="integer", example=100),
     *                                 @OA\Property(property="size_unit", type="string", example="l")
     *                             )
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=501),
     *                 @OA\Property(property="total_items", type="integer", example=501)
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $per_page = $request->query('per_page', Product::count());
        $search = $request->query('search');
        $pagination = Product::with(['brand', 'variations'])
            ->when($search, fn($qry) => $qry->whereLike('name', '%'.$search.'%'))
            ->active()
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
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/vendor/product-list",
     *     summary="fetch list of vendor products.",
     *     description="fetch list of vendor products.",
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
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Product name to search",
     *         @OA\Schema(type="string", example="Savlon")
     *     ),

     *     @OA\Response(
     *         response=200,
     *         description="Vendor stocked product lists.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Vendor stocked product lists."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="accepted", type="boolean", example=true),
     *                         @OA\Property(property="product_uuid", type="string", example="cb7c026b-9cab-435f-912a-f4d498e45628"),
     *                         @OA\Property(property="product_name", type="string", example="Quam consequatur porro ex ullam optio dolorum."),
     *                         @OA\Property(property="brand", type="string", example="Novartis"),
     *                         @OA\Property(
     *                             property="variations",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="product_variation_id", type="integer", example=69),
     *                                 @OA\Property(property="vendor_price", type="number", format="float", example=3793),
     *                                 @OA\Property(property="units_in_stock", type="integer", example=195),
     *                                 @OA\Property(property="variant_name", type="string", example="Variant-5"),
     *                                 @OA\Property(property="variant_size_value", type="string", example="500.00"),
     *                                 @OA\Property(property="variant_size_unit", type="string", example="patch")
     *                             )
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=1),
     *                 @OA\Property(property="total_items", type="integer", example=1)
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function vendorProductList(Request $request) {
        $per_page = $request->query('per_page', Auth::user()->vendor->vendorProducts->count());
        $search = $request->query('search');
        $pagination = Auth::user()->vendor
            ->vendorProducts()
            ->with(['product.brand', 'vendorPrices.variation'])
            ->when($search, fn($qry) => $qry->wherehas('product', fn($qry) => $qry->whereLike('name', '%'.$search.'%')))
            ->orderBy('id','DESC')
            ->paginate($per_page);
        // Log::info($pagination);
        $data = $this->makePaginationResponse($pagination, fn($item) => VendorStockedProductListResource::collection($item))->data;
        return $this->apiSuccess('Vendor stocked product lists.', $data);
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
     *                     @OA\Property(property="product_uuid", type="string", example="0fab9a1d-c7a9-4709-8426-d1c94d2c6b95"),
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
        $form_data = $request->validated();
        $product = Product::with('variations')->where('uuid', $form_data['product_uuid'])->firstOrFail();
        $variation_not_exists = $product->variations()
            ->whereIn('id', collect($form_data['variations'])->pluck('product_variation_id')->all())
            ->count() != count($form_data['variations']);
        if ($variation_not_exists) {
            return $this->apiError('Variation does not belong to this product');
        }
        DB::transaction(function () use($form_data, $product){
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
                }else{
                    // Log::info('else');
                    $vendor_prices->create($variation);
                }
            }
        });
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

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/vendor/product-detail/{uuid}",
     *     summary="Get all currently available products.",
     *     description="Get all currently available products.",
     *     operationId="VendorProductDetail",
     *     tags={"Product"},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of a product",
     *         @OA\Schema(type="string", example="c413e2db-9126-4a94-8da5-8756360867ec")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vendor product list resource",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Vendor product list resource"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="product_name", type="string", example="Sed quam error adipisci quia qui."),
     *                 @OA\Property(property="prescription_required", type="boolean", example=true),
     *                 @OA\Property(property="brand_name", type="string", example="Sun Pharma"),
     *                 @OA\Property(
     *                     property="variations",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="variant_name", type="string", example="Variant-1"),
     *                         @OA\Property(property="size_value", type="string", example="100.00"),
     *                         @OA\Property(property="size_unit", type="string", example="mcg"),
     *                         @OA\Property(property="units_in_stock", type="integer", example=10),
     *                         @OA\Property(property="vendor_price", type="number", format="float", example=1000)
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function vendorProductDetail(Product $product) {
        if (empty($product)) {
            return $this->apiError('Product could not be found/already been deleted.');
        }
        $product_id = $product->id;
        $data =  Auth::user()->vendor
            ->vendorProducts()
            ->with(['product.brand', 'vendorPrices.variation'])
            ->firstWhere('product_id', $product_id);
        if (empty($data)) {
            $this->apiError('product is not associated to vendor.');
        }
        $data = new AdminVendorProductDetailResource($data);
        return $this->apiSuccess('Vendor product list detail.', $data); 
    }

    /**
     * @OA\Delete(
     *     security={{"sanctum": {}}}, 
     *     path="/vendor/product-delete/{uuid}",
     *     operationId="VendorProductDelete",
     *     tags={"Product"},
     *     summary="Delete a vendor product.",
     *     description="Delete a vendor product.",
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of product to delete",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vendor product has been removed.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="null", example=null),
     *             @OA\Property(property="message", type="string", example="Vendor product has been removed.")
     *         )
     *     )
     * )
     */
    function vendorProductRemover(Product $product) {
        $product_id = $product->id;
        $vendor_product = Auth::user()->vendor
            ->vendorProducts()
            ->firstWhere('product_id', $product_id);
        if (empty($vendor_product)) {
            return $this->apiError('Product could not be found/already been deleted.');
        }
        $vendor_product->delete();
        return $this->apiError('Vendor product has been removed.');
    }
}
