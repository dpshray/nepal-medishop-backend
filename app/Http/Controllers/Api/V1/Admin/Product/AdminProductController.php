<?php

namespace App\Http\Controllers\Api\V1\Admin\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\ProductMediaStoreRequest;
use App\Http\Requests\Admin\Product\ProductStoreRequest;
use App\Http\Resources\Admin\Product\AdminProductDetailResource;
use App\Http\Resources\Admin\Product\AdminProductList;
use App\Http\Resources\Admin\Product\AdminProductResource;
use App\Models\Product;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdminProductController extends Controller
{
    use ResponseTrait, PaginationTrait;

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/product-units",
     *     summary="Get all available product units",
     *     description="Get all available product units.",
     *     operationId="ProductUnitList",
     *     tags={"Product"},
     *     @OA\Response(
     *         response=200,
     *         description="List of available product units",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="List of available product units"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="label", type="string", example="Mg"),
     *                     @OA\Property(property="value", type="string", example="mg")
     *                 )
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function productUnits(){
        $units = array_map(fn($item) => ['label' => ucfirst($item->value),'value' => $item->value], \App\Enums\ProductUnitEnum::cases());
        return $this->apiSuccess('List of available product units', $units);
    }
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/product",
     *     summary="Get all active/inactive product",
     *     description="Get all active/inactive product.",
     *     operationId="ProductList",
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
     *         name="status",
     *         in="query",
     *         required=false,
     *         description="Toggle active/inactive brands(values: 0, 1, blank)",
     *         @OA\Schema(type="integer", example="1")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Search product based on name",
     *         @OA\Schema(type="string", example="benedril")
     *     ),
     *      @OA\Response(
     *         response=200,
     *         description="Admin published product list.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Admin published product list."),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="uuid", type="string", format="uuid", example="7974511b-6bd7-42b9-81bc-00d519e37af1"),
     *                         @OA\Property(property="published", type="boolean", example=true),
     *                         @OA\Property(property="name", type="string", example="Sure Grow Procapil Scalp Solution - Hair Serum - 60ml"),
     *                         @OA\Property(property="brand", type="string", example="Bayer"),
     *                         @OA\Property(property="lowest_variant_price", type="number", format="float", example=1500),
     *                         @OA\Property(property="total_stock", type="integer", example=0)
     *                     )
     *                 ),
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=103),
     *                 @OA\Property(property="total_items", type="integer", example=103)
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $per_page = $request->per_page;
        $search = $request->query('search',null);
        $status = $request->query('status', null);
        $msg = 'All';
        if ($status != null) {
            if ($status == 1) {
                $msg = 'published';
            }else{
                $msg = 'unpublished';
            }
        }
        $pagination = Product::with(['brand', 'cheapestVariation', 'productVendorPrices','variations'])
            ->when($status != null, fn($qry) => $qry->where('status', $status))
            ->when($search != null, fn($qry) => $qry->whereLike('name', '%'.$search.'%'))
            ->latest('id')
            ->paginate($per_page);
        $product = $this->makePaginationResponse($pagination, fn($item) => AdminProductResource::collection($item))->data;
        return $this->apiSuccess("Admin $msg product list.", $product);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductStoreRequest $request)
    {
        // dd($request->all());
        DB::transaction(function () use($request){
            $product = $request->safe()->merge(['added_by' => Auth::id()])->all();
            $product = Product::create($product);
            $product->categories()->attach($request->categories);
            $product->tags()->attach($request->tags);
            $product->variations()->createMany($request->variations);
            $product->healthConditions()->attach($request->health_condition);
            $product->addMedia($request->file('featured_image'))->toMediaCollection(Product::PRODUCT_FEATURE);
            foreach ($request->file('gallery_images') as $GI) {
                $product->addMedia($GI)->toMediaCollection(Product::PRODUCT_GALLERY);
            }
        });
        return $this->apiSuccess('Product added successfully.');
    }

    /**
     * Display the specified resource.
     */
    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/product/{uuid}",
     *     summary="Show product",
     *     description="Show product.",
     *     operationId="ProductShow",
     *     tags={"Product"},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="Uuid of product",
     *         @OA\Schema(type="string", example="1b01cd1a-27d4-4dc1-bf0e-a572ce0aa581")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Showing brand",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Showing brand"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=3),
     *                 @OA\Property(property="slug", type="string", example="sun-pharma"),
     *                 @OA\Property(property="brand", type="string", example="Bristol-Myers Squibb"),
     *                 @OA\Property(property="name", type="string", example="Sun Pharma"),
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="url",
     *                     example="http://127.0.0.1:8000/assets/img/default-brand-category.png"
     *                 )
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function show(Product $product)
    {
        $product->loadMissing(['variations','categories','tags','media','brand']);
        $product->loadCount(['productVendors']);
        $product = new AdminProductDetailResource($product);
        return $this->apiSuccess('Product detail', $product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductStoreRequest $request, Product $product)
    {
        // dd($request->validated());
        DB::transaction(function () use ($request, $product) {
            $data = $request->safe()->merge(['updated_by' => Auth::id()])->all();
            $product->update($data);
            $product->categories()->sync($request->categories);
            $product->tags()->sync($request->tags);
            $variation_to_avoid = $request->collect('variations')->pluck('variation_id')->all();
            $product->variations()->whereNotIn('id', $variation_to_avoid)->delete();
            $product->healthConditions()->sync($request->health_condition);
            foreach ($request->variations as $variation) {
                if (array_key_exists('variation_id', $variation)) {
                    $product_variation = $product->variations()->firstWhere('id', $variation['variation_id']);
                    if (empty($product_variation)) {
                        throw new NotFoundHttpException("Variant could not be found");
                    }
                    $product_variation->update($variation);
                }else{
                    $product->variations()->create($variation);
                }
            }
            if ($request->hasFile('featured_image')) {
                $product->addMedia($request->file('featured_image'))->toMediaCollection(Product::PRODUCT_FEATURE);
            }
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $GI) {
                    $product->addMedia($GI)->toMediaCollection(Product::PRODUCT_GALLERY);
                }
            }
        });
        return $this->apiSuccess('Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * @OA\Delete(
     *     security={{"sanctum": {}}},
     *     path="/admin/product/{uuid}",
     *     operationId="ProductDelete",
     *     tags={"Product"},
     *     summary="Delete a product(soft).",
     *     description="Delete a product(soft).",
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="ID of the product to delete",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category successfully deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="null", example=null),
     *             @OA\Property(property="message", type="string", example="Product removed successfully..")
     *         )
     *     )
     * )
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return $this->apiSuccess('Product removed successfully.');
    }

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/toggle-product-status/{uuid}",
     *     summary="Change product published product",
     *     description="Change product published product.",
     *     operationId="ProductToggleStatus",
     *     tags={"Product"},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="Uuid of product",
     *         @OA\Schema(type="string", example="1b01cd1a-27d4-4dc1-bf0e-a572ce0aa581")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Showing brand",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Showing brand"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=3),
     *                 @OA\Property(property="slug", type="string", example="sun-pharma"),
     *                 @OA\Property(property="name", type="string", example="Sun Pharma"),
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="url",
     *                     example="http://127.0.0.1:8000/assets/img/default-brand-category.png"
     *                 )
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function statusToggler(Product $product){
        $current_status = $product->status;
        $product->update(['status' => !$current_status]);
        $status = $current_status == 1 ? 'Inactive' : 'Active';
        return $this->apiSuccess("Product status changed to $status");
    }

    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/admin/product-media/{uuid}",
     *     summary="Add product medias(featured/gallery)",
     *     description="Add product medias(featured/gallery).",
     *     operationId="AddProductMedia",
     *     tags={"Product"},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of product",
     *         @OA\Schema(type="string", example="123e4567-e89b-12d3-a456-426614174000")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"featured_image","gallery_images[]"},
     *                 @OA\Property(
     *                     property="featured_image",
     *                     type="string",
     *                     format="binary",
     *                     description="Main/featured image of product"
     *                 ),
     *                 @OA\Property(
     *                     property="gallery_images[]",
     *                     type="array",
     *                     description="Additional gallery images (multiple allowed)",
     *                     @OA\Items(type="string", format="binary"),
     *                     collectionFormat="multi"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product media added successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Media saved successfully of product: Product Name."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
    */
    function storeMedia(ProductMediaStoreRequest $request, Product $product){
        DB::transaction(function () use($request,$product){
            $product->addMedia($request->file('featured_image'))->toMediaCollection(Product::PRODUCT_FEATURE);
            foreach ($request->file('gallery_images') as $GI) {
                $product->addMedia($GI)->toMediaCollection(Product::PRODUCT_GALLERY);
            }
        });
        return $this->apiSuccess("Media saved successfully of product: $product->name.");
    }
}
