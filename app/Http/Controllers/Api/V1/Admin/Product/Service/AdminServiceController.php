<?php

namespace App\Http\Controllers\Api\V1\Admin\Product\Service;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\Service\AdminServiceRequest;
use App\Http\Resources\Admin\Product\Service\AdminServiceDetailResource;
use App\Http\Resources\Admin\Product\Service\AdminServiceListResource;
use App\Models\Product\Service\Service;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminServiceController extends Controller
{
    use ResponseTrait, PaginationTrait;
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/service",
     *     summary="Get all services",
     *     description="Get all service.",
     *     operationId="ServiceList",
     *     tags={"Service"},
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
     *         description="Service name to search",
     *         @OA\Schema(type="string", example="")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Services list",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Services list"),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="is_active", type="boolean", example=true),
     *                         @OA\Property(property="name", type="string", example="Blood Test"),
     *                         @OA\Property(property="slug", type="string", example="blood-test"),
     *                         @OA\Property(property="price", type="number", format="float", example=1299)
     *                     )
     *                 ),
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=1),
     *                 @OA\Property(property="total_items", type="integer", example=1)
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $per_page = $request->query('per_page', Service::count());
        $search = $request->query('search');
        $pagination = Service::when($search, fn($qry) => $qry->whereLike('name', '%' . $search . '%'))
            ->orderBy('id', 'DESC')
            ->paginate($per_page);
        $data = $this->makePaginationResponse($pagination, fn($item) => AdminServiceListResource::collection($item))->data;
        return $this->apiSuccess("Services list", $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/admin/service",
     *     summary="Store a product service",
     *     description="Store a product service",
     *     operationId="ServiceStore",
     *     tags={"Service"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="test_requirements", type="string"),
     *                 @OA\Property(property="price", type="float"),
     *                 @OA\Property(property="discount_percent", type="integer"),
     *
     *                 @OA\Property(
     *                     property="category_id",
     *                     type="array",
     *                     @OA\Items(type="integer"),
     *                     example={1,2}
     *                 ),
     *                 @OA\Property(
     *                     property="tag_id",
     *                     type="array",
     *                     @OA\Items(type="integer"),
     *                     example={1,2}
     *                 ),
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Service created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Service added successfully."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function store(AdminServiceRequest $request)
    {
        DB::transaction(function () use($request){ 
            $service = Service::create($request->validated());
            $service->categories()->attach($request->category_id);
            $service->tags()->attach($request->tag_id);
            $service->addMedia($request->image)
                ->toMediaCollection(Service::SERVICE_MEDIA);
        });
        return $this->apiSuccess('Service added successfully.');
    }

    /**
     * Display the specified resource.
     */
    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/service/{slug}",
     *     summary="Show an service",
     *     description="Show an service.",
     *     operationId="ServiceShow",
     *     tags={"Service"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug of service",
     *         @OA\Schema(type="string", example="")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Service fetched successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Service fetched successfully."),
     *             @OA\Property(property="success", type="boolean", example=true),
     *     
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="name", type="string", example="Skin TEST"),
     *                 @OA\Property(property="slug", type="string", example="skin-test"),
     *                 @OA\Property(property="description", type="string", example="Full body skin checkup"),
     *                 @OA\Property(property="test_requirements", type="string", example="shower before checkup"),
     *                 @OA\Property(property="price", type="number", format="float", example=8500),
     *                 @OA\Property(property="created_at", type="string", example="2025/12/01"),
     *     
     *                 @OA\Property(
     *                     property="categories",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="service category")
     *                     )
     *                 ),
     *     
     *                 @OA\Property(
     *                     property="tags",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="service tag a")
     *                     )
     *                 ),
     *     
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="uri",
     *                     example="http://192.168.100.23:8008/storage/138/3.png"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function show(Service $service)
    {
        $service->load(['categories','tags']);
        $data = new AdminServiceDetailResource($service);
        return $this->apiSuccess('Service fetched successfully.', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/admin/service/{slug}",
     *     summary="Update service based on slug",
     *     description="Update service based on slug",
     *     operationId="ServiceUpdate",
     *     tags={"Service"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="slug of a service",
     *         @OA\Schema(type="string", example="")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="_method", type="string", example="PATCH"),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="test_requirements", type="string"),
     *                 @OA\Property(property="price", type="float"),
     *                 @OA\Property(property="discount_percent", type="integer"),
     *
     *                 @OA\Property(
     *                     property="category_id",
     *                     type="array",
     *                     @OA\Items(type="integer"),
     *                     example={1,2}
     *                 ),
     *                 @OA\Property(
     *                     property="tag_id",
     *                     type="array",
     *                     @OA\Items(type="integer"),
     *                     example={1,2}
     *                 ),
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Service tag update response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Service updated successfully."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     *   )
     * )
     */
    public function update(AdminServiceRequest $request, Service $service)
    {
        DB::transaction(function () use($service,$request){
            $service->update($request->validated());
            $service->categories()->sync($request->category_id);
            $service->tags()->sync($request->tag_id);
            if ($request->hasFile('image')) {
                $service->addMedia($request->image)
                    ->toMediaCollection(Service::SERVICE_MEDIA);
            }
        });
        return $this->apiSuccess('Service updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * @OA\Delete(
     *     security={{"sanctum": {}}}, 
     *     path="/admin/service/{slug}",
     *     operationId="ServiceDelete",
     *     tags={"Service"},
     *     summary="Delete a service.",
     *     description="Delete a service.",
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="slug of the service to delete",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Service has been deleted successfully."),
     *             @OA\Property(property="data", type="null", example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function destroy(Service $service)
    {
        $service->delete();
        return $this->apiSuccess('Service has been deleted successfully.');
    }
}
