<?php

namespace App\Http\Controllers\Api\V1\Admin\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HealthConditionRequest;
use App\Http\Resources\Admin\AdminHealthConditionListResource;
use App\Models\HealthCondition;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminHealthConditionController extends Controller
{
    use ResponseTrait, PaginationTrait;

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/health-condition",
     *     summary="Get all active health condition",
     *     description="Get all active health condition.",
     *     operationId="HealthConditionList",
     *     tags={"HealthCondition"},
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
     *         description="Health condition name to search",
     *         @OA\Schema(type="string", example="Immunity Boosters")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of available health condition list.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="list of available health condition list."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="name", type="string", example="Skin Care 88"),
     *                         @OA\Property(property="slug", type="string", example="skin-care-88"),
     *                         @OA\Property(property="description", type="string", nullable=true, example="some description of skin care 88."),
     *                         @OA\Property(property="image", type="string", format="url", example="http://192.168.100.23:8008/storage/2662/origami-6275164_1920.jpg")
     *                     )
     *                 ),
     *                 @OA\Property(property="page", type="integer", example=2),
     *                 @OA\Property(property="total_page", type="integer", example=15),
     *                 @OA\Property(property="total_items", type="integer", example=15)
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     * )
     */
    function index(Request $request) {
        $per_page = $request->query('per_page', HealthCondition::count());
        $search = $request->query('search');
        $pagination = HealthCondition::with('media')
            ->when($search, fn($qry) => $qry->whereLike('name', '%'.$search.'%'))
            ->orderBy('id','DESC')
            ->paginate($per_page);
        $data = $this->makePaginationResponse($pagination, fn($item) => AdminHealthConditionListResource::collection($item))->data;
        return $this->apiSuccess('list of available health condition list.', $data);
    }

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/health-condition/{slug}",
     *     summary="Show an health condition detail.",
     *     description="Show an health condition detail.",
     *     operationId="HealthConditionShow",
     *     tags={"HealthCondition"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug of health condition",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Health resource detail.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Health resource detail."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="Skin Care 88"),
     *                 @OA\Property(property="slug", type="string", example="skin-care-88"),
     *                 @OA\Property(property="description", type="string", example="some description of skin care 88."),
     *                 @OA\Property(property="image", type="string", format="url", example="http://192.168.100.23:8008/storage/2662/origami-6275164_1920.jpg")
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     * )
     */
    function show(HealthCondition $health_condition) {
        $health_condition->load('media');
        $health_condition = new AdminHealthConditionListResource($health_condition);
        return $this->apiSuccess('Health resource detail.', $health_condition);
    }

    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/admin/health-condition",
     *     summary="Store a health condition.",
     *     description="Store a health condition.",
     *     operationId="StoreHealthCondition",
     *     tags={"HealthCondition"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "description", "image"},
     *                 @OA\Property(property="name", type="string", example="Skin Care"),
     *                 @OA\Property(property="description", type="string", example="some description of skin care."),
     *                 @OA\Property(
     *                     property="image",
     *                     type="file",
     *                     format="binary",
     *                     description="Health condition image"
     *                 ),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vendor added successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Health condition added successfully."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     * )
     */
    function store(HealthConditionRequest $request) {
        DB::transaction(fn() => 
            HealthCondition::create($request->validated())
                ->addMedia($request->image)
                ->toMediaCollection(HealthCondition::HEALTH_CONDITION_IMAGE)
        );
        return $this->apiSuccess('Health condition added successfully.');  
    }

    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/admin/health-condition/{slug}",
     *     summary="Update health condition based on slug.",
     *     description="Update health condition based on slug.",
     *     operationId="HealthConditionUpdate",
     *     tags={"HealthCondition"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug of health condition",
     *         @OA\Schema(type="string", example="skin-care")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "description","_method"},
     *                 @OA\Property(property="_method", type="string", example="patch"),
     *                 @OA\Property(property="name", type="string", example="Skin Care"),
     *                 @OA\Property(property="description", type="string", example="some description of skin care."),
     *                 @OA\Property(
     *                     property="image",
     *                     type="file",
     *                     format="binary",
     *                     description="Health condition image"
     *                 ),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vendor added successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Health condition updated successfully."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     *   )
     * )
     */
    function update(HealthConditionRequest $request, HealthCondition $health_condition) {
        $health_condition->update($request->validated());
        if ($request->hasFile('image')) {
            $health_condition->addMedia($request->image)->toMediaCollection(HealthCondition::HEALTH_CONDITION_IMAGE);
        }
        return $this->apiSuccess('Health condition updated successfully.');

        return $request->validated();
    }

    /**
     * @OA\Delete(
     *     security={{"sanctum": {}}}, 
     *     path="/admin/health-condition/{slug}",
     *     operationId="HealthConditionDelete",
     *     tags={"HealthCondition"},
     *     summary="Delete a health condition.",
     *     description="Delete a health condition.",
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug of health condition to delete.",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Brand successfully deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="null", example=null),
     *             @OA\Property(property="message", type="string", example="Health condition removed successfully.")
     *         )
     *     )
     * )
     */
    public function destroy(HealthCondition $health_condition)
    {
        $health_condition->delete();
        return $this->apiSuccess('Health condition removed successfully.');
    }
}
