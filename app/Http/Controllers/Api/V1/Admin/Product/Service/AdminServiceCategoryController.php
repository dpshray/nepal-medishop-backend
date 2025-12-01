<?php

namespace App\Http\Controllers\Api\V1\Admin\Product\Service;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\Service\AdminServiceCategoryRequest;
use App\Http\Requests\Admin\Product\Service\AdminServiceRequest;
use App\Http\Resources\Admin\Product\Service\AdminServiceCategoryListResource;
use App\Models\Product\Service\ServiceCategory;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class AdminServiceCategoryController extends Controller
{
    use ResponseTrait, PaginationTrait;
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/service-category",
     *     summary="Get all service categories",
     *     description="Get all service categories.",
     *     operationId="ServiceCategoryList",
     *     tags={"ServiceCategory"},
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
     *         description="Items on each page.(empty to fetch all data)",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Service category name to search",
     *         @OA\Schema(type="string", example="")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Service Category List",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="service category lists"
     *             ),
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *     
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="name", type="string", example="SC TEST 123"),
     *                         @OA\Property(property="slug", type="string", example="sc-test-123")
     *                     )
     *                 ),
     *     
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=3),
     *                 @OA\Property(property="total_items", type="integer", example=3)
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $per_page = $request->query('per_page', ServiceCategory::count());
        $search = $request->query('search');
        $pagination = ServiceCategory::when($search, fn($qry) => $qry->whereLike('name', '%' . $search . '%'))
            ->orderBy('id', 'DESC')
            ->paginate($per_page);
        $data = $this->makePaginationResponse($pagination, fn($item) => AdminServiceCategoryListResource::collection($item))->data;
        return $this->apiSuccess("Service category list", $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/admin/service-category",
     *     summary="Store a service category",
     *     description="Store a service category.",
     *     operationId="StoreServiceCategory",
     *     tags={"ServiceCategory"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example=""),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category create response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Service category added successfully."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     * )
     */
    public function store(AdminServiceCategoryRequest $request)
    {
        ServiceCategory::create($request->validated());
        return $this->apiSuccess('Service category added successfully.');
    }

    /**
     * Display the specified resource.
     */
    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/service-category/{slug}",
     *     summary="Show an service category",
     *     description="Show an service category.",
     *     operationId="ServiceCategoryShow",
     *     tags={"ServiceCategory"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug of service category",
     *         @OA\Schema(type="string", example="")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Showing service category",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Service category fetched successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=3),
     *                 @OA\Property(property="slug", type="string", example="sun-pharma"),
     *                 @OA\Property(property="name", type="string", example="Sun Pharma")
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function show(ServiceCategory $service_category)
    {
        $data = new AdminServiceCategoryListResource($service_category);
        return $this->apiSuccess('Service category fetched successfully.', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Patch(
     *     security={{"sanctum": {}}},
     *     path="/admin/service-category/{slug}",
     *     summary="Update service category based on ID",
     *     description="Update service category based on ID",
     *     operationId="ServiceCategoryUpdate",
     *     tags={"ServiceCategory"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="slug of a service category",
     *         @OA\Schema(type="string", example="")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name"},
     *                 @OA\Property(property="name", type="string", example="")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Service category update response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Service category updated successfully."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     *   )
     * )
     */
    public function update(AdminServiceCategoryRequest $request, ServiceCategory $service_category)
    {
        $service_category->update($request->validated());
        return $this->apiSuccess('Service category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * @OA\Delete(
     *     security={{"sanctum": {}}}, 
     *     path="/admin/service-category/{slug}",
     *     operationId="ServiceCategoryDelete",
     *     tags={"ServiceCategory"},
     *     summary="Delete a service category.",
     *     description="Delete a service category.",
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="slug of the service category to delete",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Service category has been deleted successfully."),
     *             @OA\Property(property="data", type="null", example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function destroy(ServiceCategory $service_category)
    {
        $service_category->delete();
        return $this->apiSuccess('Service category has been deleted successfully.');
    }
}
