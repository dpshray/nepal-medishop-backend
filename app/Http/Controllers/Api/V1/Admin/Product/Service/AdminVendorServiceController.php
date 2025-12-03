<?php

namespace App\Http\Controllers\Api\V1\Admin\Product\Service;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Product\Service\Vendor\AdminVendorServiceDetailResource;
use App\Http\Resources\Admin\Product\Service\Vendor\AdminVendorServiceListResource;
use App\Models\Product\Service\Service;
use App\Models\Vendor;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminVendorServiceController extends Controller
{
    use ResponseTrait, PaginationTrait;
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/service/{slug}/vendor",
     *     summary="Get all vendor list registered on a service.",
     *     description="Get all vendor list registered on a service.",
     *     operationId="AdminVendorServiceList",
     *     tags={"AdminVendorService"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug of a service",
     *         @OA\Schema(type="string", example="")
     *     ),     
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
     *         description="Vendors registered for a service",
     *     
     *         @OA\JsonContent(
     *             type="object",
     *     
     *             @OA\Property(property="message", type="string", example="Vendor list registered on this service."),
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
     *                         @OA\Property(property="is_approved_by_admin", type="boolean", example=true),
     *                         @OA\Property(property="vendor_service_status", type="boolean", example=false),
     *                         @OA\Property(property="vendor_uuid", type="string", example="f6ffcc6e-2aea-46ce-b9b0-8a5ecc005709"),
     *                         @OA\Property(property="vendor_name", type="string", example="vendor2881776"),
     *                         @OA\Property(property="service_price", type="number", format="float", example=6500)
     *                     )
     *                 ),
     *     
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=1),
     *                 @OA\Property(property="total_items", type="integer", example=1),
     *                 @OA\Property(property="service_slug", type="string", example="skin-test")
     *             ),
     *     
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function index(Request $request, Service $service)
    {
        $per_page = $request->query('per_page',100);
        $search = $request->query('search');
        $pagination = $service->vendors()
            ->with('user')
            ->when($search, fn($qry) => $qry->whereRelation('user','name','like', '%'.$search.'%'))
            ->orderBy('id', 'DESC')
            ->paginate($per_page);
        $data = $this->makePaginationResponse($pagination, fn($item) => AdminVendorServiceListResource::collection($item))->data;
        return $this->apiSuccess("Vendor list registered on this service.", $data);
    }

    /**
     * @OA\Patch(
     *     security={{"sanctum": {}}},
     *     path="/admin/service/{slug}/vendor/{uuid}",
     *     summary="Approve/Disapprove vendor service based on service slug and vendor UUID",
     *     description="Approve/Disapprove vendor service based on service slug and vendor UUID",
     *     operationId="AdminVendorServiceApproveToggle",
     *     tags={"AdminVendorService"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug of service",
     *         @OA\Schema(type="string", example="")
     *     ),
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of service vendor",
     *         @OA\Schema(type="string", example="")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Service tag update response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Vendor service has been approved successfully."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     *   )
     * )
     */
    function update(Service $service, Vendor $vendor) {
        $vendorService = $service
            ->vendors()
            ->wherePivot('vendor_id', $vendor->id)
            ->first();
        // return [$vendor->services()->wherePivot('service_id', $service->id)->doesntExist()];
        $current = $vendorService->pivot->is_approved ?? 0;
        $newStatus = !$current;
        $service->vendors()->syncWithoutDetaching([
            $vendor->id => ['is_approved' => $newStatus]
        ]);
        $approve_status = $newStatus ? 'approved' : 'disapproved';
        return $this->apiSuccess("Vendor service has been {$approve_status} successfully.");
    }


    /**
     * Display the specified resource.
     */
    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/service/{slug}/vendor/{uuid}",
     *     summary="Show a vendor service detail",
     *     description="Show a vendor service detail.",
     *     operationId="AdminVendorServiceShow",
     *     tags={"AdminVendorService"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug of service",
     *         @OA\Schema(type="string", example="")
     *     ),
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of service vendor",
     *         @OA\Schema(type="string", example="")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vendor Service detail fetched successfully",
     *     
     *         @OA\JsonContent(
     *             type="object",
     *     
     *             @OA\Property(property="message", type="string", example="Vendor service detail fetched successfully."),
     *     
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *     
     *                 @OA\Property(property="is_approved_by_admin", type="boolean", example=true),
     *                 @OA\Property(property="vendor_service_status", type="boolean", example=false),
     *                 @OA\Property(property="service_name", type="string", example="Skin TEST"),
     *                 @OA\Property(property="service_slug", type="string", example="skin-test"),
     *                 @OA\Property(property="service_description", type="string", example="Full body skin checkup"),
     *                 @OA\Property(property="test_requirements", type="string", example="shower before checkup"),
     *                 @OA\Property(property="admin_price", type="number", format="float", example=8500),
     *                 @OA\Property(property="discount_percent", type="integer", example=0),
     *                 @OA\Property(property="vendor_price", type="number", format="float", example=6500),
     *     
     *                 @OA\Property(
     *                     property="vendor_detail",
     *                     type="object",
     *     
     *                     @OA\Property(property="vendor_name", type="string", example="vendor2881776"),
     *                     @OA\Property(property="store_name", type="string", example="Bechtelar-Stehr"),
     *                     @OA\Property(property="email", type="string", example="vendor2881776@gmail.com"),
     *                     @OA\Property(property="email_verified_at", type="string", format="date-time", example="2025-11-28T10:15:49.000000Z"),
     *                     @OA\Property(property="mobile_number", type="string", example="9834127218")
     *                 )
     *             ),
     *     
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function show(Service $service, Vendor $vendor)
    {
        $service->load([
            'vendors.user',
            'vendors' => fn($qry) => $qry->wherePivot('vendor_id', $vendor->id)
        ]);
        $data = new AdminVendorServiceDetailResource($service);
        return $this->apiSuccess('Vendor service detail fetched successfully.', $data);
    }
}
