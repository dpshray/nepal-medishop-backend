<?php

namespace App\Http\Controllers\Api\V1\Vendor\Service;

use App\Enums\Purchase\ServiceBookingStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\Vendor\Product\Service\VendorAssignedBookingSericeListResource;
use App\Models\Product\Service\ServiceBooking;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\UnauthorizedException;

class VendorServiceBookingController extends Controller
{
    use ResponseTrait, PaginationTrait;

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/vendor/assigned-service-bookings",
     *     summary="Get all booking service assigned to vendor.",
     *     description="Get all booking service assigned to vendor.",
     *     operationId="VendorServiceAssignedBookingList",
     *     tags={"VendorServiceBooking"},
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
     *         description="List of service bookings assigned",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="List of service bookings assigned"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *     
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="booking_uuid", type="string", format="uuid", example="e7591a49-d1af-41c8-90c3-66f7a2891f55"),
     *                         @OA\Property(property="status", type="string", example="PENDING"),
     *                         @OA\Property(property="user_name", type="string", example="user00"),
     *                         @OA\Property(property="service_name", type="string", example="Lipid Profile"),
     *                         @OA\Property(property="service_slug", type="string", example="lipid-profile"),
     *                         @OA\Property(property="message", type="string", nullable=true, example=null),
     *                         @OA\Property(property="appointment_at", type="string", format="date-time", example="2025/12/10 14:30:00")
     *                     )
     *                 ),
     *     
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=1),
     *                 @OA\Property(property="total_items", type="integer", example=1)
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function servicesAssignedToVendor(Request $request) {
        $per_page = $request->query('per_page');
        $pagination = Auth::user()->vendor->assignedServiceBookings()->with(['orderedBy','service'])->paginate($per_page);
        $data = $this->makePaginationResponse($pagination, fn($item) => VendorAssignedBookingSericeListResource::collection($item))->data;
        return $this->apiSuccess('List of service bookings assigned', $data);
    }

    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/vendor/update-booking-status/{uuid}",
     *     summary="Update booking status of a assigned service.status are(CONFIRMED | IN_PROGRESS | COMPLETED | CANCELLED)",
     *     description="Update booking status of a assigned service.",
     *     operationId="VendorServiceStatus",
     *     tags={"VendorServiceBooking"},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="Service booking UUID",
     *         @OA\Schema(type="string", example="")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"status"},
     *                 @OA\Property(property="status", type="string", example=""),
     *                 @OA\Property(
     *                     property="report",
     *                     type="string",
     *                     format="binary",
     *                     description="Report file of a service(when completed)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vendor added successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Service booking status has been changed to : COMPLETED."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     * )
     */
    function bookingStatusUpdate(Request $request, ServiceBooking $service_booking)
    {
        $required_status = ServiceBookingStatusEnum::exceptPending();
        $form_data = $request->validate([
            'status' => ['required', Rule::in($required_status)]
        ]);
        $status = $form_data['status'];
        if ($status == ServiceBookingStatusEnum::COMPLETED->value && !$request->hasFile('report')) {
            return $this->apiError('Report must be uploaded to make it as completed.');
        }
        // RETURN [$service_booking->isNot(Auth::user()->vendor)];
        if ($service_booking->assignedVendor->isNot(Auth::user()->vendor)) {
            throw new UnauthorizedException();
        }

        DB::transaction(function () use($service_booking, $status, $request){            
            $service_booking->update(['status' => $status]);
            if ($status == ServiceBookingStatusEnum::COMPLETED->value) {
                $service_booking->addMedia($request->report)
                    ->toMediaCollection(ServiceBooking::SERVICE_BOOKING_REPORT);
            }
        });
        
        return $this->apiSuccess('Service booking status has been changed to : ' . $status);
    }
}
