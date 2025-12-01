<?php

namespace App\Http\Controllers\Api\V1\Admin\Product\Service;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\Service\AdminServiceTagRequest;
use App\Http\Resources\Admin\Product\Service\AdminServiceTagListResource;
use App\Models\Product\Service\ServiceTag;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class AdminServiceTagController extends Controller
{
    use ResponseTrait, PaginationTrait;
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/service-tag",
     *     summary="Get all service tags",
     *     description="Get all service tags.",
     *     operationId="ServiceTagList",
     *     tags={"ServiceTag"},
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
     *         description="Service tag name to search",
     *         @OA\Schema(type="string", example="")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Service Tag List",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="service tag lists"
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
     *                         @OA\Property(property="name", type="string", example="ST TEST 123"),
     *                         @OA\Property(property="slug", type="string", example="st-test-123")
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
        $per_page = $request->query('per_page', ServiceTag::count());
        $search = $request->query('search');
        $pagination = ServiceTag::when($search, fn($qry) => $qry->whereLike('name', '%' . $search . '%'))
            ->orderBy('id', 'DESC')
            ->paginate($per_page);
        $data = $this->makePaginationResponse($pagination, fn($item) => AdminServiceTagListResource::collection($item))->data;
        return $this->apiSuccess("Service tag list", $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/admin/service-tag",
     *     summary="Store a service tag",
     *     description="Create a new service tag",
     *     operationId="StoreServiceTag",
     *     tags={"ServiceTag"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Health Care")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Service tag created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Service tag added successfully."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function store(AdminServiceTagRequest $request)
    {
        ServiceTag::create($request->validated());
        return $this->apiSuccess('Service tag added successfully.');
    }

    /**
     * Display the specified resource.
     */
    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/service-tag/{slug}",
     *     summary="Show an service tag",
     *     description="Show an service tag.",
     *     operationId="ServiceTagShow",
     *     tags={"ServiceTag"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug of service tag",
     *         @OA\Schema(type="string", example="")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Showing tag",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Service tag fetched successfully."),
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
    public function show(ServiceTag $service_tag)
    {
        $data = new AdminServiceTagListResource($service_tag);
        return $this->apiSuccess('Service tag fetched successfully.', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Patch(
     *     security={{"sanctum": {}}},
     *     path="/admin/service-tag/{slug}",
     *     summary="Update service tag based on ID",
     *     description="Update service tag based on ID",
     *     operationId="ServiceTagUpdate",
     *     tags={"ServiceTag"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="slug of a service tag",
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
     *         description="Service tag update response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Service tag updated successfully."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     *   )
     * )
     */
    public function update(AdminServiceTagRequest $request, ServiceTag $service_tag)
    {
        $service_tag->update($request->validated());
        return $this->apiSuccess('Service tag updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * @OA\Delete(
     *     security={{"sanctum": {}}}, 
     *     path="/admin/service-tag/{slug}",
     *     operationId="ServiceTagDelete",
     *     tags={"ServiceTag"},
     *     summary="Delete a service tag.",
     *     description="Delete a service tag.",
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="slug of the service tag to delete",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Service tag has been deleted successfully."),
     *             @OA\Property(property="data", type="null", example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function destroy(ServiceTag $service_tag)
    {
        $service_tag->delete();
        return $this->apiSuccess('Service tag has been deleted successfully.');
    }
}
