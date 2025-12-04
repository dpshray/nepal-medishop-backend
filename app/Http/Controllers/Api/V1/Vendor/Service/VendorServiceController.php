<?php

namespace App\Http\Controllers\Api\V1\Vendor\Service;

use App\Enums\Purchase\ServiceBookingStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\Product\Service\VendorServiceStoreRequest;
use App\Http\Resources\Vendor\Product\Service\VendorRegisteredServiceListResource;
use App\Http\Resources\Vendor\Product\Service\VendorServiceDetailResource;
use App\Http\Resources\Vendor\Product\Service\VendorServiceListResource;
use App\Models\Product\Service\Service;
use App\Models\Product\Service\ServiceBooking;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
     *         description="List of available services",
     *     
     *         @OA\JsonContent(
     *             type="object",
     *     
     *             @OA\Property(property="message", type="string", example="List of available services"),
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
     *                         @OA\Property(property="id", type="integer", example=57),
     *                         @OA\Property(property="name", type="string", example="Electrocardiogram (ECG)"),
     *                         @OA\Property(property="slug", type="string", example="electrocardiogram-ecg"),
     *                         @OA\Property(property="admin_price", type="number", format="float", example=900),
     *                         @OA\Property(property="admin_discount_percent", type="integer", example=2)
     *                     )
     *                 ),
     *     
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=10),
     *                 @OA\Property(property="total_items", type="integer", example=10)
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
            ->active()
            ->when($search, fn($qry) => $qry->whereLike('name', '%' . $search . '%'))
            ->orderBy('id', 'DESC')
            ->paginate($per_page);
        $data = $this->makePaginationResponse($pagination, fn($item) => VendorServiceListResource::collection($item))->data;
        return $this->apiSuccess("List of available services", $data);
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
     *         description="Showing service detail",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Showing service detail"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="service_id", type="integer", example=57),
     *                 @OA\Property(property="is_approved_by_admin", type="boolean", example=false),
     *                 @OA\Property(property="vendor_service_status", type="boolean", example=true),
     *                 @OA\Property(property="service_name", type="string", example="Electrocardiogram (ECG)"),
     *                 @OA\Property(property="service_slug", type="string", example="electrocardiogram-ecg"),
     *                 @OA\Property(property="admin_price", type="number", format="float", example=900),
     *                 @OA\Property(property="admin_discount_percent", type="integer", example=2),
     *                 @OA\Property(property="added_by_admin_at", type="string", format="date", example="2025/12/02"),
     *                 @OA\Property(property="vendor_price", type="number", format="float", example=800)
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function show(Service $service)
    {
        $service->load(['categories','tags','vendors' => fn($qry) => $qry->wherePivot('vendor_id', Auth::user()->vendor->id)]);
        return $this->apiSuccess('Showing service detail', new VendorServiceDetailResource($service));
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/vendor/service",
     *     summary="register a service by vendor(if already exists it gets updated | if updated when price is different is_approved_by_admin will auto be false).",
     *     description="register a service by vendor(if already exists it gets updated | if updated when price is different is_approved_by_admin will auto be false).",
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
        DB::transaction(function () use($request){
            $data = [
                'price' => $request->price,
                'is_available' => $request->is_available,
            ];
            $previous_service = Auth::user()->vendor
                ->services
                ->firstWhere('pivot.service_id',$request->service_id);
            if ($previous_service && $previous_service->pivot->price != $request->price) {
                $data['is_approved'] = null;
            }
            Auth::user()->vendor->services()->syncWithoutDetaching([
                $request->service_id => $data
            ]);
        });
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

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/vendor/registered-services",
     *     summary="Get all registered service list.",
     *     description="Get all registered service list.",
     *     operationId="VendorRegisteredServiceList",
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
     *         description="List of available services",
     *     
     *         @OA\JsonContent(
     *             type="object",
     *     
     *             @OA\Property(property="message", type="string", example="List of available services"),
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
     *                         @OA\Property(property="id", type="integer", example=57),
     *                         @OA\Property(property="name", type="string", example="Electrocardiogram (ECG)"),
     *                         @OA\Property(property="slug", type="string", example="electrocardiogram-ecg"),
     *                         @OA\Property(property="admin_price", type="number", format="float", example=900),
     *                         @OA\Property(property="admin_discount_percent", type="integer", example=2)
     *                     )
     *                 ),
     *     
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=10),
     *                 @OA\Property(property="total_items", type="integer", example=10)
     *             ),
     *     
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function getRegisteredServices(Request $request) {
        $per_page = $request->query('per_page');
        $pagination = Auth::user()->vendor
            ->services()
            ->paginate($per_page);
        $data = $this->makePaginationResponse($pagination, fn($item) => VendorRegisteredServiceListResource::collection($item))->data;
        return $this->apiSuccess('List of serivces that vendor have registered.', $data);
    }
}
