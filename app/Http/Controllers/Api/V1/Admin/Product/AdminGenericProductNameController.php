<?php

namespace App\Http\Controllers\Api\V1\Admin\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\AdminProductGenericNameRequest;
use App\Http\Resources\Admin\Product\AdminGenericProductNameListResource;
use App\Models\Product\GenericProductName;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class AdminGenericProductNameController extends Controller
{
    use PaginationTrait, ResponseTrait;

    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/generic-product-name",
     *     summary="Get all active/inactive product generic name.",
     *     description="Get all active/inactive product generic name.",
     *     operationId="ProductGenericNameList",
     *     tags={"ProductGenericName"},
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
     *         description="Toggle active/inactive brands(values: 0 and 1)",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Brand name to search.",
     *         @OA\Schema(type="string", example="pfizer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Active product generic name lists",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Active product generic name lists"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="items", type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=4),
     *                         @OA\Property(property="name", type="string", example="dada"),
     *                         @OA\Property(property="slug", type="string", example="dada")
     *                     )
     *                 ),
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=1),
     *                 @OA\Property(property="total_items", type="integer", example=3)
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $per_page = $request->query('per_page', GenericProductName::count());
        $search = $request->query('search');
        $status = $request->query('status', 1) == 1 ? 1 : 0;
        $pagination = GenericProductName::where('status', $status)
            ->when($search, fn($qry) => $qry->whereLike('name', '%'.$search.'%'))
            ->orderBy('id', 'DESC')
            ->paginate($per_page);
        $data = $this->makePaginationResponse($pagination, fn($items) => AdminGenericProductNameListResource::collection($items))->data;
        $msg = $status == 1 ? 'Active' : 'Inactive';
        return $this->apiSuccess("$msg product generic name lists", $data);
    }

    /**
     * Store a newly created resource in storage.
     */

    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/admin/generic-product-name",
     *     summary="Store a product generic name.",
     *     description="Store a product generic name.",
     *     operationId="ProductGenericNameStore",
     *     tags={"ProductGenericName"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Broad Spectrum")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Generic product create response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Product generic name added successfully."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     * )
     */
    public function store(AdminProductGenericNameRequest $request)
    {
        GenericProductName::create($request->validated());
        return $this->apiSuccess('Product generic name added successfully.');
    }

    /**
     * Display the specified resource.
     */
    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/generic-product-name/{slug}",
     *     summary="Show a generic product name.",
     *     description="Show a generic product name.",
     *     operationId="ProductGenericNameShow",
     *     tags={"ProductGenericName"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug of generic product name",
     *         @OA\Schema(type="string", example="omega-3")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail of product generic name.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Detail of product generic name."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=4),
     *                 @OA\Property(property="name", type="string", example="dada"),
     *                 @OA\Property(property="slug", type="string", example="dada")
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function show(GenericProductName $generic_product_name)
    {
        $data = new AdminGenericProductNameListResource($generic_product_name);
        return $this->apiSuccess('Detail of product generic name.', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Patch(
     *     security={{"sanctum": {}}},
     *     path="/admin/generic-product-name/{slug}",
     *     summary="Update product generic name based on slug.",
     *     description="Update product generic name based on slug.",
     *     operationId="ProductGenericNameUpdate",
     *     tags={"ProductGenericName"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="slug of a product generic name",
     *         @OA\Schema(type="string", example="proactive-ii")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name"},
     *                 @OA\Property(property="name", type="string", example="Anesthesiology"),
     *                 @OA\Property(property="status", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag update response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Tag updated successfully."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     *   )
     * )
     */
    public function update(AdminProductGenericNameRequest $request, GenericProductName $generic_product_name)
    {
        $generic_product_name->update($request->validated());
        return $this->apiSuccess('Generic product name updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * @OA\Delete(
     *     security={{"sanctum": {}}}, 
     *     path="/admin/generic-product-name/{slug}",
     *     operationId="ProductGenericNameDelete",
     *     tags={"ProductGenericName"},
     *     summary="Delete a product generic name.",
     *     description="Delete a product generic name.",
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="slug of product generic name to remove.",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product generic name successfully deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="null", example=null),
     *             @OA\Property(property="message", type="string", example="Generic product name has successfully been deleted.")
     *         )
     *     )
     * )
     */
    public function destroy(GenericProductName $generic_product_name)
    {
        $generic_product_name->delete();
        return $this->apiSuccess('Generic product name has successfully been deleted.');
    }
}
