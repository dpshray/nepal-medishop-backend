<?php

namespace App\Http\Controllers\Api\V1\Admin\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\ProductMediaStoreRequest;
use App\Http\Requests\Admin\Product\ProductStoreRequest;
use App\Http\Requests\Admin\Product\ProductUpdateRequest;
use App\Http\Resources\Admin\Product\AdminProductDetailResource;
use App\Http\Resources\Admin\Product\AdminProductList;
use App\Http\Resources\Admin\Product\AdminProductResource;
use App\Http\Resources\Vendor\Product\VendorProductAssociationListResource;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\VendorProductPrice;
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
    function productUnits()
    {
        $units = array_map(fn($item) => ['label' => ucfirst($item->value), 'value' => $item->value], \App\Enums\ProductUnitEnum::cases());
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
        $search = $request->query('search', null);
        $status = $request->query('status', null);
        $msg = 'All';
        if ($status != null) {
            if ($status == 1) {
                $msg = 'published';
            } else {
                $msg = 'unpublished';
            }
        }
        $pagination = Product::with(['brand', 'cheapestVariation', 'media', 'productVendorPrices', 'variations', 'genericProductName', 'healthConditions'])
            ->when($status != null, fn($qry) => $qry->where('status', $status))
            ->when($search != null, fn($qry) => $qry->whereLike('name', '%' . $search . '%'))
            ->latest('id')
            ->paginate($per_page);
        $product = $this->makePaginationResponse($pagination, fn($item) => AdminProductResource::collection($item))->data;
        return $this->apiSuccess("Admin $msg product list.", $product);
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     path="/admin/product",
     *     summary="Create Product",
     *     description="Create a new product with variations, categories, tags, health conditions and images",
     *     operationId="ProductStore",
     *     tags={"Product"},
     *     security={{"sanctum":{}}},
     *
     *    @OA\RequestBody(
     *     required=true,
     *
     *   @OA\MediaType(
     *      mediaType="multipart/form-data",
     *
     *       @OA\Schema(
     *          type="object",
     *
     *           required={
     *              "brand_id",
     *             "name",
     *            "categories",
     *           "tags",
     *          "health_condition",
     *         "variations",
     *        "generic_product_name_id",
     *       "featured_image"
     *  },
     *
     *           @OA\Property(
     *              property="brand_id",
     *             type="integer",
     *            example=1
     *       ),
     *
     *           @OA\Property(
     *              property="name",
     *             type="string",
     *            example="Paracetamol"
     *       ),
     *
     *           @OA\Property(
     *              property="description",
     *             type="string",
     *            example="Pain relief medicine"
     *       ),
     *
     *           @OA\Property(
     *              property="generic_product_name_id",
     *             type="integer",
     *            example=1
     *       ),
     *
     *           @OA\Property(
     *              property="prescription_required",
     *             type="boolean",
     *            example=true
     *       ),
     *
     *           @OA\Property(
     *              property="discount_percent",
     *             type="number",
     *            format="float",
     *           example=10,
     *          maximum=100
     *     ),
     *
     *           @OA\Property(
     *              property="categories",
     *             type="array",
     *            @OA\Items(type="integer"),
     *           example={1,2}
     *      ),
     *
     *           @OA\Property(
     *              property="tags",
     *             type="array",
     *            @OA\Items(type="integer"),
     *           example={1,2}
     *      ),
     *
     *           @OA\Property(
     *              property="health_condition",
     *             type="array",
     *            @OA\Items(type="integer"),
     *           example={1,3}
     *      ),
     *
     *           @OA\Property(
     *              property="featured_image",
     *             type="string",
     *            format="binary"
     *       ),

     *      @OA\Property(
     *         property="variations",
     *        type="array",
     *
     *               @OA\Items(
     *                  type="object",
     *
     *                   required={
     *                      "variant_stock",
     *                     "variant_unit",
     *                    "variant_price",
     *                   "variant_batch_no",
     *                  "variant_expiry_date",
     *                 "variant_form_type",
     *                "variant_package_type",
     *               "variant_package_size",
     *              "variant_strength"
     *         },
     *
     *                   @OA\Property(
     *                      property="variant_name",
     *                     type="string",
     *                    example="500mg"
     *               ),
     *
     *                   @OA\Property(
     *                      property="variant_stock",
     *                     type="number",
     *                    example=100
     *               ),
     *
     *                   @OA\Property(
     *                      property="variant_unit",
     *                     type="string",
     *                    example="tablet"
     *               ),
     *
     *                   @OA\Property(
     *                      property="variant_price",
     *                     type="number",
     *                    format="float",
     *                   example=120
     *              ),
     *
     *                   @OA\Property(
     *                      property="discount_percent",
     *                     type="number",
     *                    format="float",
     *                   example=5
     *              ),
     *
     *                   @OA\Property(
     *                      property="variant_batch_no",
     *                     type="string",
     *                    example="BATCH-1001"
     *               ),
     *
     *                   @OA\Property(
     *                      property="variant_expiry_date",
     *                     type="string",
     *                    format="date",
     *                   example="2027-12-31"
     *              ),
     *
     *                   @OA\Property(
     *                      property="variant_form_type",
     *                     type="string",
     *                    example="Tablet"
     *               ),
     *
     *                   @OA\Property(
     *                      property="variant_package_type",
     *                     type="string",
     *                    example="Box"
     *               ),
     *
     *                   @OA\Property(
     *                      property="variant_package_size",
     *                     type="string",
     *                    example="10 Tablets"
     *               ),
     *
     *                   @OA\Property(
     *                      property="variant_strength",
     *                     type="string",
     *                    example="500mg"
     *               )
     *          )
     *     )
     *)
     * )
     *),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Product created successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="status",
     *                 type="boolean",
     *                 example=true
     *             ),
     *
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Product created successfully"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error"
     *     )
     * )
     */
    public function store(ProductStoreRequest $request)
    {
        DB::transaction(function () use ($request) {
            $product = $request->safe()->merge(['added_by' => Auth::id()])->all();
            $product = Product::create($product);
            $pv = $product->productVendors()->create(['is_approved' => true, 'vendor_id' => Auth::id()]);
            $product->categories()->attach($request->categories);
            $product->tags()->attach($request->tags);

            collect($request->variations)->each(function ($item) use ($product, $pv) {
                $product_variation = ProductVariation::create([
                    'product_id' => $product->id,
                    'name' => $item['variant_name'] ?? null,
                    'platform_price' => $item['variant_price'],
                    'size_value' => $item['variant_stock'],
                    'size_unit' => $item['variant_unit'],
                    'form_type' => $item['variant_form_type'],
                    'package_type' => $item['variant_package_type'],
                    'package_size' => $item['variant_package_size'],
                    'strength' => $item['variant_strength'],
                ]);
                $vendorProductPrices = VendorProductPrice::create([
                    'product_vendor_id' => $pv->id,
                    'product_variation_id' => $product_variation->id,
                    'units_in_stock' => $item["variant_stock"],
                    'expiry_date' => $item["variant_expiry_date"],
                    'batch_number' => $item["variant_batch_no"],
                    'price' => $item['variant_price']
                ]);
                if (isset($item['image'])) {
                    $product_variation->addMedia($item['image'])->toMediaCollection(ProductVariation::VARIATION_IMAGE);
                }
            });

            $product->healthConditions()->attach($request->health_condition);
            $product->addMedia($request->file('featured_image'))->toMediaCollection(Product::PRODUCT_FEATURE);
            // foreach ($request->file('gallery_images') as $GI) {
            //     $product->addMedia($GI)->toMediaCollection(Product::PRODUCT_GALLERY);
            // }
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
     *         description="Successful product detail response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product detail"),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="Incidunt atque veniam voluptates inventore consequatur."),
     *                 @OA\Property(property="uuid", type="string", example="b1b4a46a-9cb1-4d03-a421-16dcbdf976ae"),
     *                 @OA\Property(property="slug", type="string", example="incidunt-atque-veniam-voluptates-inventore-consequatur"),
     *                 @OA\Property(
     *                     property="brand",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=3),
     *                     @OA\Property(property="name", type="string", example="Sun Pharma")
     *                 ),
     *                 @OA\Property(property="description", type="string", example="<p>Placeat accusamus illum iure amet eius...</p>"),
     *                 @OA\Property(property="added_date", type="string", format="date-time", example="2025-10-28T19:08:02.000000Z"),
     *                 @OA\Property(property="prescription_required", type="boolean", example=false),
     *                 @OA\Property(property="no_of_vendors", type="integer", example=1),
     *                 @OA\Property(property="units_in_stock", type="integer", example=1),
     *                 @OA\Property(
     *                     property="categories",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=13),
     *                         @OA\Property(property="name", type="string", example="Weight Management")
     *                     )
     *                 ),
     *
     *                 @OA\Property(
     *                     property="tags",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=24),
     *                         @OA\Property(property="name", type="string", example="Sunscreen")
     *                     )
     *                 ),
     *
     *                 @OA\Property(
     *                     property="variations",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="variation_id", type="integer", example=5),
     *                         @OA\Property(property="name", type="string", example="Variant-2"),
     *                         @OA\Property(property="size_value", type="number", example=200),
     *                         @OA\Property(property="size_unit", type="string", example="bottle"),
     *                         @OA\Property(property="admin_price", type="number", example=1181)
     *                     )
     *                 ),
     *
     *                 @OA\Property(
     *                     property="health_conditions",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="name", type="string", example="Sleep & Relaxation")
     *                     )
     *                 ),
     *
     *                 @OA\Property(
     *                     property="featured_image",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=111),
     *                     @OA\Property(property="url", type="string", example="http://192.168.100.23:8008/storage/111/syrup.jpg")
     *                 ),
     *
     *                 @OA\Property(
     *                     property="gallery_images",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=112),
     *                         @OA\Property(property="url", type="string", example="http://192.168.100.23:8008/storage/112/tablets.jpg")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function show(Product $product)
    {
        $product->loadMissing(['variations', 'categories', 'tags', 'media', 'brand', 'healthConditions', 'productVendorPrices']);
        $product->loadCount(['productVendors']);
        $product = new AdminProductDetailResource($product);
        return $this->apiSuccess('Product detail', $product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductUpdateRequest $request, Product $product)
    {
        DB::transaction(function () use ($request, $product) {
            $data = $request->safe()->merge(['updated_by' => Auth::id()])->all();
            $product->update($data);
            $product->categories()->sync($request->categories);
            $product->tags()->sync($request->tags);
            $product->healthConditions()->sync($request->health_condition);

            // Get the product vendor for this product and current user
            $pv = $product->productVendors()->where('vendor_id', Auth::id())->first();

            // If no product vendor exists, create one
            if (!$pv) {
                $pv = $product->productVendors()->create([
                    'is_approved' => true,
                    'vendor_id' => Auth::id()
                ]);
            }

            // Collect variant IDs to keep
            $variation_to_avoid = $request->collect('variations')
                ->pluck('variant_id')
                ->filter(fn($item) => $item)
                ->all();

            // Delete variations that are not in the request
            $product->variations()
                ->when(!empty($variation_to_avoid), fn($qry) => $qry->whereNotIn('id', $variation_to_avoid))
                ->delete();

            foreach ($request->variations as $variation) {
                if (array_key_exists('variant_id', $variation) && !empty($variation['variant_id'])) {
                    // Update existing variation
                    $product_variation = $product->variations()->find($variation['variant_id']);

                    if (empty($product_variation)) {
                        throw new NotFoundHttpException("Variant could not be found of this product");
                    }

                    // Update the variation
                    $product_variation->update([
                        'name'   => $variation['variant_name'] ?? null,
                        'size_value'  => $variation['variant_stock'],
                        'size_unit'   => $variation['variant_unit'],
                        'platform_price'  => $variation['variant_price'],
                        'form_type' => $variation['variant_form_type'],
                        'package_type' => $variation['variant_package_type'],
                        'package_size' => $variation['variant_package_size'],
                        'strength' => $variation['variant_strength'],
                    ]);

                    if (isset($variation['image'])) {
                        $product_variation->addMedia($variation['image'])->toMediaCollection(ProductVariation::VARIATION_IMAGE);
                    }
                    // Update or create vendor product prices
                    $product_variation->vendorProductPrices()
                        ->updateOrCreate(
                            [
                                'product_vendor_id' => $pv->id,
                                'product_variation_id' => $product_variation->id,
                            ],
                            [
                                'price' => $variation['variant_price'],
                                'units_in_stock' => $variation['variant_stock'],
                                'expiry_date' => $variation['variant_expiry_date'],
                                'batch_number' => $variation['variant_batch_no'],
                            ]
                        );
                } else {
                    // Create new variation
                    $newVariation = $product->variations()->create([
                        'name'   => $variation['variant_name'] ?? null,
                        'size_value'  => $variation['variant_stock'],
                        'size_unit'   => $variation['variant_unit'],
                        'platform_price'  => $variation['variant_price'],
                        'form_type' => $variation['variant_form_type'],
                        'package_type' => $variation['variant_package_type'],
                        'package_size' => $variation['variant_package_size'],
                        'strength' => $variation['variant_strength'],
                    ]);
                    if (isset($variation['image'])) {
                        $newVariation->addMedia($variation['image'])->toMediaCollection(ProductVariation::VARIATION_IMAGE);
                    }
                    // Create vendor product prices with proper foreign keys
                    $newVariation->vendorProductPrices()->create([
                        'product_vendor_id' => $pv->id,
                        'product_variation_id' => $newVariation->id,
                        'units_in_stock' => $variation['variant_stock'],
                        'expiry_date' => $variation['variant_expiry_date'],
                        'batch_number' => $variation['variant_batch_no'],
                        'price' => $variation['variant_price']
                    ]);
                }
            }

            if ($request->hasFile('featured_image')) {
                $product->addMedia($request->file('featured_image'))->toMediaCollection(Product::PRODUCT_FEATURE);
            }
            // if ($request->hasFile('gallery_images')) {
            //     foreach ($request->file('gallery_images') as $GI) {
            //         $product->addMedia($GI)->toMediaCollection(Product::PRODUCT_GALLERY);
            //     }
            // }
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
    function statusToggler(Product $product)
    {
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
    function storeMedia(ProductMediaStoreRequest $request, Product $product)
    {
        DB::transaction(function () use ($request, $product) {
            $product->addMedia($request->file('featured_image'))->toMediaCollection(Product::PRODUCT_FEATURE);
            foreach ($request->file('gallery_images') as $GI) {
                $product->addMedia($GI)->toMediaCollection(Product::PRODUCT_GALLERY);
            }
        });
        return $this->apiSuccess("Media saved successfully of product: $product->name.");
    }

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/product/{uuid}/vendors",
     *     summary="Get all vendor list associated with this product.",
     *     description="Get all vendor list associated with this product.",
     *     operationId="ProductVendorList",
     *     tags={"Product"},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of product",
     *         @OA\Schema(type="string", example="123e4567-e89b-12d3-a456-426614174000")
     *     ),
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
    function productVendors(Request $request, Product $product)
    {
        $per_page = $request->query('per_page', $product->productVendors->count());
        $pagination = $product->productVendors()->with([
            'associatedVendor.user',
            'vendorPrices',
            'vendorPrices.product',
            'vendorPrices.variation',
        ])
            ->paginate($per_page);
        $data = $this->makePaginationResponse($pagination, fn($items) => VendorProductAssociationListResource::collection($items))->data;
        return $this->apiSuccess('Vendor list associated with this product', $data);
    }
    function deleteProductMedia($product_uuid, $media_id)
    {
        $product = Product::where('uuid', $product_uuid)->firstOrFail();

        $deleted = $product->media()->where('id', $media_id)->delete();

        if (!$deleted) {
            return $this->apiError('Media not found for this product.', 404);
        }

        return $this->apiSuccess('Product media deleted successfully.');
    }
}
