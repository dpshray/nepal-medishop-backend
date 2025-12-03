<?php

namespace App\Http\Controllers\Api\V1\Admin\Product\Service;

use App\Enums\Purchase\ServiceBookingStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Product\Service\Booking\AdminServiceBookingDetailResource;
use App\Http\Resources\Admin\Product\Service\Booking\AdminServiceBookingListResource;
use App\Models\Product\Service\Service;
use App\Models\Product\Service\ServiceBooking;
use App\Models\Vendor;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class AdminServiceBookingController extends Controller
{
    use ResponseTrait, PaginationTrait;
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/service-booking",
     *     summary="Fetch all service booking list.",
     *     description="Fetch all service booking list.",
     *     operationId="AdminServiceBookingList",
     *     tags={"AdminServiceBooking"},  
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
     *         description="Booking search based on ordered user name, service name, vendor name",
     *         @OA\Schema(type="string", example="")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of service bookings",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="List of service bookings"),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         
     *                         @OA\Property(
     *                             property="booking_uuid",
     *                             type="string",
     *                             format="uuid",
     *                             example="dc5c31b2-bba5-423b-8cf2-53507ea3ef78"
     *                         ),
     *                         @OA\Property(
     *                             property="status",
     *                             type="string",
     *                             example="PENDING"
     *                         ),
     *                         @OA\Property(
     *                             property="ordered_by",
     *                             type="string",
     *                             example="user1211"
     *                         ),
     *                         @OA\Property(
     *                             property="service_name",
     *                             type="string",
     *                             example="Complete Blood Count (CBC)"
     *                         ),
     *                         @OA\Property(
     *                             property="service_slug",
     *                             type="string",
     *                             example="complete-blood-count-cbc"
     *                         ),
     *                         @OA\Property(
     *                             property="assigned_vendor",
     *                             type="string",
     *                             nullable=true,
     *                             example=null
     *                         ),
     *                         @OA\Property(
     *                             property="appointment_at",
     *                             type="string",
     *                             example="2025/12/01 10:30:00"
     *                         ),
     *                         @OA\Property(
     *                             property="created_at",
     *                             type="string",
     *                             example="2025/12/02"
     *                         )
     *                     )
     *                 ),
     *     
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=2),
     *                 @OA\Property(property="total_items", type="integer", example=2)
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $per_page = $request->query('per_page');
        $search = $request->query('search');
        if (empty($per_page)) {
            $per_page = ServiceBooking::count();
        }
        $pagination = ServiceBooking::with(['orderedBy','assignedVendor.user','service'])
            ->when($search, function($q) use($search){
                $q->whereRelation('orderedBy','name','like', '%'.$search.'%')
                ->orWhereRelation('service','name','like', '%'.$search.'%')
                ->orWhereHas('assignedVendor',fn($qry) => $qry->whereRelation('user', 'name', 'like', '%' . $search . '%'));
            })
            ->latest()
            ->paginate($per_page);
        $data = $this->makePaginationResponse($pagination, fn($item) => AdminServiceBookingListResource::collection($item))->data;
        return $this->apiSuccess('List of service bookings',$data);
    }

    /**
     * Display the specified resource.
     */
    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/service-booking/{uuid}",
     *     summary="Fetch details of service booking.",
     *     description="Fetch details of service booking.",
     *     operationId="AdminServiceBookingShow",
     *     tags={"AdminServiceBooking"},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of a booking",
     *         @OA\Schema(type="string", example="")
     *     ), 
     *     @OA\Response(
     *         response=200,
     *         description="Service booking details",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Service booking details"),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 
     *                 @OA\Property(property="status", type="string", example="CONFIRMED"),
     *                 
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="name", type="string", example="user02"),
     *                     @OA\Property(property="email", type="string", example="user02@gmail.com")
     *                 ),
     *     
     *                 @OA\Property(
     *                     property="assigned_vendor",
     *                     type="object",
     *                     @OA\Property(property="name", type="string", example="vendor163786"),
     *                     @OA\Property(property="email", type="string", example="vendor163786@gmail.com")
     *                 ),
     *     
     *                 @OA\Property(property="service_name", type="string", example="Lipid Profile"),
     *                 @OA\Property(property="service_price", type="number", example=1600),
     *                 @OA\Property(property="service_discount_percent", type="integer", example=1),
     *                 @OA\Property(property="service_description", type="string", example="Measures cholesterol and triglycerides to evaluate heart disease risk."),
     *                 @OA\Property(property="test_requirements", type="string", example="Fast for 9–12 hours before test."),
     *                 @OA\Property(property="message", type="string", example="some service booking message for lipid-profile"),
     *                 @OA\Property(property="appointment_at", type="string", format="date-time", example="2025-12-10 04:30:00"),
     *                 @OA\Property(property="service_created_at", type="string", format="date", example="2025-12-03")
     *             )
     *         )
     *     )
     * )
     */
    public function show(ServiceBooking $service_booking)
    {
        $service_booking->load(['orderedBy', 'assignedVendor.user', 'service']);
        $data = new AdminServiceBookingDetailResource($service_booking);
        return $this->apiSuccess('Service booking details', $data);
    }

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/assign-booking/{booking_uuid}/vendor/{vendor_uuid}",
     *     summary="Assign a service to a vendor.",
     *     description="Assign a service to a vendor.",
     *     operationId="AdminServiceBookingAssign",
     *     tags={"AdminServiceBooking"},
     *     @OA\Parameter(
     *         name="booking_uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of a booking",
     *         @OA\Schema(type="string", example="")
     *     ),
     *     @OA\Parameter(
     *         name="vendor_uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of vendor",
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
    function assignServiceBookingToVendor(ServiceBooking $service_booking, $vendor) {
        $vendor = Vendor::where('uuid', $vendor)->firstOrFail();
        $booking_service = $service_booking->service;
        $vendor_service = $vendor->services()->firstWhere('services.id', $booking_service->id);

        if (!(bool)$vendor_service->is_active) {
            return $this->apiError('This service is not active at the moment.');
        }
        if (empty($vendor_service)) {
            return $this->apiError('Service is not registered to this vendor yet.');
        }
        if (!(bool)$vendor_service->pivot->is_available) {
            return $this->apiError('Service is not made available by the vendor at the moment.');
        }
        if (!(bool)$vendor_service->pivot->is_approved) {
            return $this->apiError('Vendor service is not approved by admin.');
        }
        if ($service_booking->status != ServiceBookingStatusEnum::PENDING) {
            return $this->apiError('Cannot assign service booking(This service booking status is : '.$service_booking->status->value.')');
        }
        if ($service_booking->appointment_at->lt(now())) {
            return $this->apiError('Booking appointment has already been expired.');
        }
        $service_booking->update(['assigned_vendor_id' => $vendor->id]);
        return $this->apiSuccess('Service booking has been assigned to vendor : '.$vendor->user->name);
    }
}
