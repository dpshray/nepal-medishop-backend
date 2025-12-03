<?php

namespace App\Http\Controllers\Api\V1\Client\Service;

use App\Enums\Purchase\ServiceBookingStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Product\Service\Service;
use App\Traits\HelperTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientServiceBookingController extends Controller
{
    use HelperTrait, ResponseTrait;
    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/book-service/{slug}",
     *     summary="Book a service using service slug.",
     *     description="Book a service using service slug.",
     *     operationId="ClientServiceBookingList",
     *     tags={"ClientServiceBooking"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug of service",
     *         @OA\Schema(type="string", example="complete-blood-count-cbc")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"payment_method","name","email","mobile","address"},
     *                 @OA\Property(property="payment_method", type="string", example="Cash on Delivery"),
     *                 @OA\Property(
     *                     property="appointment_datetime",
     *                     type="string",
     *                     format="date-time",
     *                     example="2025-12-01 10:30:00",
     *                     description="Appointment date and time in ISO 8601 format"
     *                 ),
     *                 @OA\Property(property="name", type="string", example=""),
     *                 @OA\Property(property="email", type="string", example=""),
     *                 @OA\Property(property="mobile", type="string", example=""),
     *                 @OA\Property(property="address", type="string", example=""),
     *                 @OA\Property(property="latitude", type="string", example=""),
     *                 @OA\Property(property="longitude", type="string", example=""),
     *                 @OA\Property(property="message", type="string", example="")
     *             )
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 required={"appointment_datetime"},
     *     
     *                 @OA\Property(
     *                     property="appointment_datetime",
     *                     type="string",
     *                     format="date-time",
     *                     example="2025-12-01 10:30:00",
     *                     description="Appointment date and time in ISO 8601 format"
     *                 ),
     *                 @OA\Property(
     *                     property="message",
     *                     type="string",
     *                     example="some service booking message"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Service booked successfully",
     *     
     *         @OA\JsonContent(
     *             type="object",
     *     
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Service booked successfully"
     *             ),
     *     
     *             @OA\Property(
     *                 property="data",
     *                 nullable=true,
     *                 example=null
     *             ),
     *     
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             )
     *         )
     *     )
     * )
     */
    function serviceBooking(Request $request, Service $service)
    {
        $form_data = $request->validate([
            'payment_method' => 'required',
            'name' => 'required|max:255',
            'email' => 'required|email',
            'mobile' => 'required',
            'address' => 'required|max:255',
            'latitude' => 'sometimes|nullable',
            'longitude' => 'sometimes|nullable',
            'appointment_datetime' => 'required|date_format:Y-m-d H:i:s',
            'message' => 'sometimes|nullable'
        ]);
        ['price' => $price, 'previous_price' => $previous_price] = $this->calculateDiscountPrice($service->price, $service->discount_percent);
        $data = [
            'status' => ServiceBookingStatusEnum::PENDING,
            'appointment_at' => $form_data['appointment_datetime'],
            'message' => array_key_exists('message', $form_data) ? $form_data['message'] : null,
            'service_id' => $service->id,
            'price' => empty($previous_price) ? $price : $previous_price,
            'discount_percent' => $service->discount_percent,
            'payment_method' => $form_data['payment_method'],
            'name' => $form_data['name'],
            'email' => $form_data['email'],
            'mobile' => $form_data['mobile'],
            'address' => $form_data['address'],
            'latitude' => array_key_exists('latitude', $form_data) ? $form_data['latitude'] : null,
            'longitude' => array_key_exists('longitude', $form_data) ? $form_data['longitude'] : null,
        ];
        Auth::user()->serviceBookings()->create($data);
        return $this->apiSuccess('Service booked successfully.');
    }

    function clientBookingHistory(Request $request) {
        $per_page = $request->query('per_page');

        // Auth::user()->serviceBookings()->paginate();
    }
}
