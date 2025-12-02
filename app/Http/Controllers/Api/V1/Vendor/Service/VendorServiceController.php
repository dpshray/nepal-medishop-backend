<?php

namespace App\Http\Controllers\Api\V1\Vendor\Service;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\Product\Service\VendorServiceStoreRequest;
use App\Http\Resources\Vendor\Product\Service\VendorServiceDetailResource;
use App\Http\Resources\Vendor\Product\Service\VendorServiceListResource;
use App\Models\Product\Service\Service;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorServiceController extends Controller
{
    use ResponseTrait, PaginationTrait;

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/vendor/service",
     *     summary="Get all service list.",
     *     description="Get all service list.",
     *     operationId="VendorServiceList",
     *     tags={"VendorService"},
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
     *         description="Vendor service name to search",
     *         @OA\Schema(type="string", example="")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Service list response",
     *     
     *         @OA\JsonContent(
     *             type="object",
     *     
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Service list lists"
     *             ),
     *     
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *     
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *     
     *                     @OA\Items(
     *                         type="object",
     *     
     *                         @OA\Property(property="service_id", type="integer", example=14),
     *                         @OA\Property(property="is_made_available_by_admin", type="boolean", example=true),
     *                         @OA\Property(property="is_vendor_already_priced", type="boolean", example=true),
     *                         @OA\Property(property="vendor_service_status", type="boolean", example=true),
     *                         @OA\Property(property="service_name", type="string", example="Complete Blood Count (CBC)"),
     *                         @OA\Property(property="service_slug", type="string", example="complete-blood-count-cbc"),
     *                         @OA\Property(property="admin_price", type="number", format="float", example=5000),
     *                         @OA\Property(property="admin_discount_percent", type="number", example=2),
     *                     )
     *                 ),
     *     
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=1),
     *                 @OA\Property(property="total_items", type="integer", example=7)
     *             ),
     *     
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $per_page = $request->query('per_page', Service::count());
        $search = $request->query('search');
        $pagination = Service::with(['vendors' => fn($qry) => $qry->wherePivot('vendor_id', Auth::user()->vendor->id)])
            ->when($search, fn($qry) => $qry->whereLike('name', '%' . $search . '%'))
            ->orderBy('id', 'DESC')
            ->paginate($per_page);
        $data = $this->makePaginationResponse($pagination, fn($item) => VendorServiceListResource::collection($item))->data;
        return $this->apiSuccess("Service list lists", $data);
    }

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/vendor/service/{slug}",
     *     summary="Show an service details",
     *     description="Show an service details.",
     *     operationId="VendorServiceShow",
     *     tags={"VendorService"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug of service",
     *         @OA\Schema(type="string", example="omega-3")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Service detail response",
     *     
     *         @OA\JsonContent(
     *             type="object",
     *     
     *             @OA\Property(property="message", type="string", example="Showing service detail"),
     *     
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *     
     *                 @OA\Property(property="service_id", type="integer", example=3),
     *                 @OA\Property(property="is_made_available_by_admin", type="boolean", example=true),
     *                 @OA\Property(property="is_vendor_already_applied", type="boolean", example=true),
     *                 @OA\Property(property="vendor_service_status", type="boolean", example=false),
     *                 @OA\Property(property="service_name", type="string", example="Skin TEST"),
     *                 @OA\Property(property="service_slug", type="string", example="skin-test"),
     *                 @OA\Property(property="admin_price", type="number", format="float", example=8500),
     *                 @OA\Property(property="admin_discount_percent", type="integer", example=0),
     *                 @OA\Property(property="added_by_admin_at", type="string", format="date", example="2025/12/01"),
     *                 @OA\Property(property="vendor_price", type="number", format="float", example=6500)
     *             ),
     *     
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function show(Service $service)
    {
        $service->load(['vendors' => fn($qry) => $qry->wherePivot('vendor_id', Auth::user()->vendor->id)]);
        return $this->apiSuccess('Showing service detail', new VendorServiceDetailResource($service));
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/vendor/service",
     *     summary="register/update a service by vendor.",
     *     description="register/update a service by vendor.",
     *     operationId="VendorServiceStore",
     *     tags={"VendorService"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 required={"is_available","service_id","price"},
     *                 
     *                 @OA\Property(property="is_available", type="boolean"),
     *                 @OA\Property(property="service_id", type="integer"),
     *                 @OA\Property(property="price", type="number", format="float")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="service register response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Service registered successfully."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     * )
     */
    public function store(VendorServiceStoreRequest $request)
    {
        // return $request->validated();
        Auth::user()->vendor->services()->syncWithoutDetaching([
            $request->service_id => [
                'price' => $request->price,
                'is_available' => $request->is_available,
            ]
        ]);        // VendorService::create($request->validated());
        return $this->apiSuccess('Service registered successfully.');
    }

    /**
     * @OA\Delete(
     *     security={{"sanctum": {}}}, 
     *     path="/vendor/service/{slug}",
     *     operationId="VendorServiceDelete",
     *     tags={"VendorService"},
     *     summary="Delete a vendor registered service.",
     *     description="Delete a vendor registered service.",
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug of service to delete",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registered service successfully deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="null", example=null),
     *             @OA\Property(property="message", type="string", example="Registered service removed successfully.")
     *         )
     *     )
     * )
     */
    public function destroy(Service $service)
    {
        
        $service->vendors()->detach(Auth::user()->vendor->id);
        return $this->apiSuccess('Registered service removed successfully.');
    }
}
