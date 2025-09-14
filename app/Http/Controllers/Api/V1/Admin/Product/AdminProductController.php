<?php

namespace App\Http\Controllers\Api\V1\Admin\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\ProductMediaStoreRequest;
use App\Http\Requests\Admin\Product\ProductStoreRequest;
use App\Models\Product;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminProductController extends Controller
{
    use ResponseTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/admin/product",
     *     summary="Add a product by admin",
     *     description="Add a product by admin.",
     *     operationId="CreateProduct",
     *     tags={"Product"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"brand_id","name","description"},
     *                 @OA\Property(property="brand_id", type="integer", example=5),
     *                 @OA\Property(property="name", type="string", example="Fast&Up Charge with Natural Vitamin C and Zinc for Immunity - 20 Effervescent Tablets - Orange Flavour"),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string",
     *                     example="<table border='1' cellpadding='6'><tr><th>Brand</th><td>OSOAA</td></tr></table>"
     *                 ),
     *                 @OA\Property(
     *                     property="categories",
     *                     type="array",
     *                     @OA\Items(type="integer", example=1)
     *                 ),
     *                 @OA\Property(
     *                     property="tags",
     *                     type="array",
     *                     @OA\Items(type="integer", example=1)
     *                 ),
     *                 @OA\Property(
     *                     property="variations",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="size_value", type="number", example=100),
     *                         @OA\Property(property="size_unit", type="string", example="gm"),
     *                         @OA\Property(property="price", type="number", example=1000),
     *                         @OA\Property(property="discount_price", type="number", example=100)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product added successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Product added successfully."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function store(ProductStoreRequest $request)
    {
        DB::transaction(function () use($request){
            $product = $request->safe()->merge(['added_by' => Auth::id()])->all();
            $product = Product::create($product);
            $product->categories()->attach($request->categories);
            $product->tags()->attach($request->tags);
            $product->variations()->createMany($request->variations);
        });
        return $this->apiSuccess('Product added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //
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
