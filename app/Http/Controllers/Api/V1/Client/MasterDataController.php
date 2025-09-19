<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Enums\ClientProductSectionEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\Product\Brand\ClientBrandResource;
use App\Http\Resources\User\Product\Card\ProductCardCollection;
use App\Http\Resources\User\Product\Card\ProductCardResource;
use App\Http\Resources\User\Product\Category\ClientCategoryResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVendor;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

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
     *     path="/fetch-section/{section}",
     *     summary="Get product based on section",
     *     description="Get product based on section.",
     *     operationId="ClientSectionList",
     *     tags={"Product"},
     *     @OA\Parameter(
     *         name="section",
     *         in="path",
     *         required=true,
     *         description="Section of product(recent,flash,featured,popular)",
     *         @OA\Schema(type="string", example="recent")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Item per page",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Recent products",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="recent products"),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="name", type="string", example="Voluptatum nobis fuga eum eum repudiandae quae."),
     *                         @OA\Property(property="brand", type="string", example="Takeda"),
     *                         @OA\Property(property="rating", type="number", format="float", example=3.8),
     *                         @OA\Property(property="price", type="number", format="float", example=166),
     *                         @OA\Property(property="previous_price", type="number", format="float", nullable=true, example=222.44)
     *                     )
     *                 ),
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=4),
     *                 @OA\Property(property="total_items", type="integer", example=40)
     *             )
     *         )
     *     )
     * )
     */
    function fetchProductSection(Request $request, $section){
        $per_page = $request->query('per_page', 10);
        $product = Product::with(['brand','cheapestVariation', 'media']);
        $product = match ($section) {
            ClientProductSectionEnum::RECENT->value => $product->whereDate('created_at', now()),
            ClientProductSectionEnum::FEATURED->value => $product->where('is_featured',1),
            ClientProductSectionEnum::FLASH->value => $product->where('is_featured', 1)
        };
        $pagination = $product->latest()->paginate($per_page);
        $data = $this->makePaginationResponse($pagination, fn($item) => ProductCardResource::collection($item))->data;
        return $this->apiSuccess("$section products", $data);
    }
}
