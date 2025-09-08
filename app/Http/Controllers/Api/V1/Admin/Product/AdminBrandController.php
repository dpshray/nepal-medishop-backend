<?php

namespace App\Http\Controllers\Api\V1\Admin\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\Brand\BrandStoreRequest;
use App\Http\Resources\Admin\AdminBrandResource;
use App\Models\Brand;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class AdminBrandController extends Controller
{
    use ResponseTrait, PaginationTrait;

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/brand",
     *     summary="Get all active brand",
     *     description="Get all active brand.",
     *     operationId="BrandList",
     *     tags={"Brand"},
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
     *         description="Active brand lists",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Active brand lists"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="slug", type="string", example="pfizer"),
     *                         @OA\Property(property="name", type="string", example="Pfizer")
     *                     )
     *                 ),
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=3),
     *                 @OA\Property(property="total_items", type="integer", example=5)
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $per_page = $request->per_page;
        $pagination = Brand::paginate($per_page);
        $data = $this->makePaginationResponse($pagination, fn($items) => new AdminBrandResource($items))->data;
        return $this->apiSuccess('Active brand lists', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/admin/brand",
     *     summary="Store a product brand",
     *     description="Store a product brand.",
     *     operationId="StoreBrand",
     *     tags={"Brand"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Merck"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vendor added successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Brand added successfully."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     * )
     */
    public function store(BrandStoreRequest $request)
    {
        Brand::create($request->validated());
        return $this->apiSuccess('Brand added successfully.');
    }

    /**
     * @OA\Patch(
     *     security={{"sanctum": {}}},
     *     path="/admin/brand/{brand}",
     *     summary="Update brand based on ID",
     *     description="Update brand based on ID",
     *     operationId="BrandUpdate",
     *     tags={"Brand"},
     *     @OA\Parameter(
     *         name="brand",
     *         in="path",
     *         required=true,
     *         description="An infant id of the belonging user",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name"},
     *                 @OA\Property(property="name", type="string", example="Merck")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vendor added successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Brand updated successfully."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     *   )
     * )
     */
    public function update(BrandStoreRequest $request, Brand $brand)
    {
        $brand->update($request->validated());
        return $this->apiSuccess('Brand updated successfully.');
    }

    /**
     * @OA\Delete(
     *     security={{"sanctum": {}}}, 
     *     path="/admin/brand/{brand}",
     *     operationId="BrandDelete",
     *     tags={"Brand"},
     *     summary="Delete a brand(soft).",
     *     description="Delete a brand(soft).",
     *     @OA\Parameter(
     *         name="brand",
     *         in="path",
     *         required=true,
     *         description="ID of the brand to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Brand successfully deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="null", example=null),
     *             @OA\Property(property="message", type="string", example="Brand removed successfully.")
     *         )
     *     )
     * )
    */
    public function destroy(Brand $brand)
    {
        $brand->delete();
        return $this->apiSuccess('Brand removed successfully.');
    }
}
