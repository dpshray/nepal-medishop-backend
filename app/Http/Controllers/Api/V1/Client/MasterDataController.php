<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\Product\Brand\ClientBrandResource;
use App\Http\Resources\User\Product\Category\ClientCategoryResource;
use App\Models\Brand;
use App\Models\Category;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class MasterDataController extends Controller
{
    use ResponseTrait;

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
}
