<?php

namespace App\Http\Controllers\Api\V1\Client\Service;

use App\Enums\Purchase\DiscountEnum;
use App\Enums\Purchase\ServiceBookingStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\Product\Service\ClientServiceHistoryDetailResource;
use App\Http\Resources\User\Product\Service\ClientServiceHistoryListResource;
use App\Models\Point\CouponCode;
use App\Models\Product\Service\Service;
use App\Models\Product\Service\ServiceBooking;
use App\Traits\HelperTrait;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\UnauthorizedException;

class ClientServiceBookingController extends Controller
{
    use HelperTrait, ResponseTrait, PaginationTrait;
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
     *                 @OA\Property(property="message", type="string", example=""),
     *                 @OA\Property(property="coupon_code", type="string", example=""),
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
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Service booked successfully."),
     *             
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 
     *                 @OA\Property(property="previous_price", type="number", format="float", example=350),
     *                 @OA\Property(property="amount", type="number", format="float", example=346.5),
     *                 @OA\Property(property="order_number", type="string", example="HUR4M"),
     *                 @OA\Property(property="payment_method", type="string", example="Cash on Delivery"),
     *                 @OA\Property(property="appointment_date", type="string", format="date-time", example="2025-12-01 10:30:00"),
     *                 @OA\Property(property="name", type="string", example="Roger"),
     *                 @OA\Property(property="email", type="string", format="email", example="roger@gmail.com"),
     *                 @OA\Property(property="mobile", type="string", example="954778541"),
     *                 @OA\Property(property="delivery_address", type="string", example="Lagankhel, Kathmandu"),
     *                 @OA\Property(property="latitude", type="string", example="78.05646"),
     *                 @OA\Property(property="longitude", type="string", example="15.456454"),
     *                 @OA\Property(property="promo_code", type="string", example="test0"),
     *                 @OA\Property(property="promo_discount", type="number", format="float", example=3.5),
     *                 @OA\Property(property="service_name", type="string", example="Urine Routine Examination")
     *             ),
     *     
     *             @OA\Property(property="success", type="boolean", example=true)
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
            'message' => 'sometimes|nullable',
            'coupon_code' => [
                'sometimes',
                'nullable', 
                Rule::exists('coupon_codes','code')->where('is_active', true)
            ]
        ]);
        $total_discount_percent = $service->discount_percent;
        $coupon_code_discount_amount = $coupon_discount_percent = $coupon_code_id = $coupon_code_name = null;
        $service_discounts = [];
        if (!(bool)$service->is_active) {
            return $this->apiError('Service is not active at the moment.');
        }

        // return [$price, $discount_percent];
        ['price' => $current_service_price, 'previous_price' => $previous_price] = $this->calculateDiscountPrice($service->price, $service->discount_percent);
        if ($previous_price) {
            $service_discounts[] = [
                'type' => DiscountEnum::SERVICE_DISCOUNT,
                'discount_amount' => $previous_price - $current_service_price
            ];
        }

        if (!empty($request->coupon_code)) {
            $coupon_code = CouponCode::firstWhere('code', $request->coupon_code);
            $coupon_discount_percent = $coupon_code->discount_percent;
            $total_discount_percent += $coupon_discount_percent;
            $coupon_code_id = $coupon_code->id;
            ['price' => $current_service_price, 'previous_price' => $prev_price] = $this->calculateDiscountPrice($current_service_price, $coupon_discount_percent);
            $coupon_code_name = $coupon_code->code;
            $coupon_code_discount_amount = round(($prev_price - $current_service_price),2);
            $service_discounts[] = [
                'type' => DiscountEnum::COUPON_DISCOUNT,
                'discount_amount' => $coupon_code_discount_amount,
                'code' => $coupon_code_name
            ];
        }

        // ['price' => $discouted_price] = $this->calculateDiscountPrice($service->price, $total_discount_percent);
        $data = [
            'status' => ServiceBookingStatusEnum::PENDING,
            'appointment_at' => $form_data['appointment_datetime'],
            'message' => array_key_exists('message', $form_data) ? $form_data['message'] : null,
            'service_id' => $service->id,
            'price' => $current_service_price,
            'discount_percent' => $service->discount_percent,
            'payment_method' => $form_data['payment_method'],
            'name' => $form_data['name'],
            'email' => $form_data['email'],
            'mobile' => $form_data['mobile'],
            'address' => $form_data['address'],
            'latitude' => array_key_exists('latitude', $form_data) ? $form_data['latitude'] : null,
            'longitude' => array_key_exists('longitude', $form_data) ? $form_data['longitude'] : null,
            'used_coupon_code_id' => $coupon_code_id,
            'order_code' => $this->generateOrderCode()
        ];
        DB::transaction(function () use($data,$service_discounts){
            Auth::user()->serviceBookings()->create($data)->discounts()->createMany($service_discounts);
        });

        $data_for_response = [
            "previous_price" => (float)$service->price ,
            "amount" => (float)$current_service_price,
            "order_number" => $data['order_code'],
            "payment_method" => $data['payment_method'],
            "appointment_date" => $data['appointment_at'],
            "name" => $data['name'],
            "email" => $data['email'],
            "mobile" => $data['mobile'],
            "delivery_address" => $data['address'],
            "latitude" => $data['latitude'],
            "longitude" => $data['longitude'],
            "promo_code" => $coupon_code_name,
            "promo_discount" => (float)$coupon_code_discount_amount,
            "service_name" => $service->name
        ];
        return $this->apiSuccess('Service booked successfully.', $data_for_response);
    }

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/fetch-service-booking-history",
     *     summary="Get client booking history.",
     *     description="Get client booking history.",
     *     operationId="ClientServiceHistoryList",
     *     tags={"ClientServiceBooking"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="search service booking using order code, name, address, service name",
     *         @OA\Schema(type="string", example="")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Item per page",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Api page number",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Client all service booking history list",
     *         @OA\JsonContent(
     *             type="object",
     *             
     *             @OA\Property(property="message", type="string", example="Client all service booking history list"),
     *     
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *     
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="booking_uuid", type="string", format="uuid", example="26d9b72c-9133-4ca9-9758-ecf5fa256720"),
     *                         @OA\Property(property="status", type="string", example="COMPLETED"),
     *                         @OA\Property(property="order_code", type="string", example="g54ww5"),
     *                         @OA\Property(property="client_name", type="string", example="Sophia"),
     *                         @OA\Property(property="service_name", type="string", example="Complete Blood Count (CBC)"),
     *                         @OA\Property(property="image", type="string", format="url", example="http://192.168.100.23:8008/storage/129/Complete-Blood-Count-CBC.jpeg"),
     *                         @OA\Property(property="price", type="number", format="float", example=1200),
     *                         @OA\Property(property="appointment_at", type="string", format="date-time", example="2025/12/01 10:30:00")
     *                     )
     *                 ),
     *     
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=1),
     *                 @OA\Property(property="total_items", type="integer", example=1)
     *             ),
     *     
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
    */
    function index(Request $request) {
        $per_page = $request->query('per_page');
        $per_page = $per_page ? $per_page : Auth::user()->serviceBookings->count();
        $search = $request->query('search');
        $pagination = Auth::user()->serviceBookings()
            ->with(['service'])
            ->when($search, function($qry) use($search){
                $qry->whereRelation('service','name','like','%'.$search.'%')
                    ->orWhereLike('order_code','%'.$search.'%')
                    ->orWhereLike('name','%' . $search . '%')
                    ->orWhereLike('address','%' . $search . '%');
            })
            ->orderBy('id','DESC')
            ->paginate($per_page);
        $data = $this->makePaginationResponse($pagination, fn($item) => ClientServiceHistoryListResource::collection($item))->data;
        return $this->apiSuccess('Client all service booking history list', $data);
    }

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/fetch-service-booking-detail/{uuid}",
     *     summary="Get client booking detail.",
     *     description="Get client booking detail.",
     *     operationId="ClientServiceDetailList",
     *     tags={"ClientServiceBooking"},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of a service booking",
     *         @OA\Schema(type="string", example="")
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Service booking detail",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Service booking detail"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="order_code", type="string", example="WQDCB"),
     *                  @OA\Property(property="order_status", type="string", example="COMPLETED"),
     *                  @OA\Property(property="price", type="number", format="float", example=1746.36),
     *                  @OA\Property(property="service_name", type="string", example="Thyroid Function Test"),
     *                  @OA\Property(property="service_image", type="string", format="url", example="http://192.168.100.23:8008/storage/130/Thyroid-Function-Test.jpg"),
     *                  @OA\Property(property="service_description", type="string", example="Checks thyroid hormone levels (T3, T4, TSH) to diagnose thyroid disorders."),
     *                  @OA\Property(property="test_requirements", type="string", example="Fasting not required. Morning sample preferred."),
     *
     *                  @OA\Property(
     *                      property="service_categories",
     *                      type="array",
     *                      @OA\Items(
     *                          @OA\Property(property="name", type="string", example="Blood Tests")
     *                      )
     *                  ),
     *
             *                  @OA\Property(
     *                      property="service_tags",
     *                      type="array",
     *                      @OA\Items(
     *                          @OA\Property(property="name", type="string", example="Doctor Recommended")
     *                      )
     *                  ),
     *
     *                  @OA\Property(property="payment_method", type="string", example="Cash on Delivery"),
     *                  @OA\Property(property="name", type="string", example="user00"),
     *                  @OA\Property(property="email", type="string", example="user@gmail.com"),
     *                  @OA\Property(property="mobile", type="string", example="9886825298"),
     *                  @OA\Property(property="address", type="string", example="M8FV+V53 Koteshwor Kathmandu Bagmati Province Nepal"),
     *                  @OA\Property(property="latitude", type="string", example="27.6748152"),
     *                  @OA\Property(property="longitude", type="string", example="85.3430236"),
     *                  @OA\Property(property="user_name", type="string", example="user00"),
     *                  @OA\Property(property="message", type="string", nullable=true, example=null),
     *
     *                  @OA\Property(property="appointment_at", type="string", example="2025/12/04 12:59:00"),
     *
     *                  @OA\Property(property="report_document", type="string", format="url", example="http://192.168.100.23:8008/storage/143/SAMPLE-MEDICAL-REPORT.pdf"),
     *
     *                  @OA\Property(property="coupon_code", type="string", example="test0"),
     *                  @OA\Property(property="coupon_code_discount_amount", type="number", format="float", example=17.64),
     *                  @OA\Property(property="service_discount_amount", type="number", format="float", example=36)
     *              ),
     *              @OA\Property(property="success", type="boolean", example=true)
     *          )
     *      )
     * )
     */
    function show(ServiceBooking $service_booking) {
        // Log::info([Auth::id()]);
        if ($service_booking->orderedBy->isNot(Auth::user())) {
            throw new UnauthorizedException();
        }
        $service_booking->load([
            'media',
            'service' => fn($q) => $q->with(['categories','tags']),
            'orderedBy',
            'discounts'
        ]);
        $data = new ClientServiceHistoryDetailResource($service_booking);
        return $this->apiSuccess('Service booking detail', $data);
    }
}
