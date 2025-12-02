<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Enums\Purchase\ServiceBookingStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\Product\Service\ClientServiceDetailResource;
use App\Http\Resources\User\Product\Service\ClientServiceListResource;
use App\Models\Product\Service\Service;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientServiceController extends Controller
{
    use ResponseTrait, PaginationTrait;
    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/get-services",
     *     summary="Get services",
     *     description="Get services.",
     *     operationId="ClientServiceList",
     *     tags={"ClientService"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="search product using name",
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
     *         description="List of services",
     *     
     *         @OA\JsonContent(
     *             type="object",
     *     
     *             @OA\Property(property="message", type="string", example="List of services"),
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
     *                         @OA\Property(property="image", type="string", example="http://example.com/image.png", nullable=true),
     *                         @OA\Property(property="name", type="string", example="Skin TEST"),
     *                         @OA\Property(property="slug", type="string", example="skin-test"),
     *                         @OA\Property(property="price", type="number", format="float", example=8500),
     *                         @OA\Property(property="previous_price", type="number", nullable=true, example=9000)
     *                     )
     *                 ),
     *     
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=1),
     *                 @OA\Property(property="total_items", type="integer", example=3)
     *             ),
     *     
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function index(Request $request) {
        $per_page = $request->query('per_page');
        $search = $request->query('search');
        $services = Service::active()
            ->when($search, fn($qry) => $qry->whereLike('name','%'.$search.'%'));
        if ($per_page) {
            $pagination = $services->paginate($per_page);
            $data= $this->makePaginationResponse($pagination, fn($item) => ClientServiceListResource::collection($item))->data;
        }else{
            $data = $services->get();
            $data = ClientServiceListResource::collection($data);
        }
        return $this->apiSuccess('List of services', $data);
    }

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/get-services/{slug}",
     *     summary="Show service details",
     *     description="Show services details.",
     *     operationId="ClientServiceShow",
     *     tags={"ClientService"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug of service",
     *         @OA\Schema(type="string", example="sun-pharma")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Service details",
     *     
     *         @OA\JsonContent(
     *             type="object",
     *     
     *             @OA\Property(property="message", type="string", example="service details"),
     *     
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *     
     *                 @OA\Property(property="image", type="string", example="http://example.com/image.png", nullable=true),
     *                 @OA\Property(property="name", type="string", example="Skin TEST"),
     *                 @OA\Property(property="slug", type="string", example="skin-test"),
     *                 @OA\Property(property="price", type="number", format="float", example=8500),
     *                 @OA\Property(property="previous_price", type="number", nullable=true, example=9000),
     *                 @OA\Property(property="description", type="string", example="Full body skin checkup"),
     *                 @OA\Property(property="test_requirements", type="string", example="shower before checkup"),
     *     
     *                 @OA\Property(
     *                     property="categories",
     *                     type="array",
     *     
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="slug", type="string", example="service-category"),
     *                         @OA\Property(property="name", type="string", example="service category")
     *                     )
     *                 ),
     *     
     *                 @OA\Property(
     *                     property="tags",
     *                     type="array",
     *     
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="slug", type="string", example="service-tag-a"),
     *                         @OA\Property(property="name", type="string", example="service tag a")
     *                     )
     *                 )
     *             ),
     *     
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function show(Service $service) {
        $service->load([
            'categories','tags'
        ]);
        $data = new ClientServiceDetailResource($service);
        return $this->apiSuccess('Service details', $data);
    }

    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/book-service/{slug}",
     *     summary="Book a service using service slug.",
     *     description="Book a service using service slug.",
     *     operationId="ClientServiceBooking",
     *     tags={"ClientService"},
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
    function serviceBooking(Request $request, Service $service) {
        $form_data = $request->validate([
            'appointment_datetime' => 'required|date_format:Y-m-d H:i:s',
            'message' => 'sometimes|nullable'
        ]);
        $data = [
            'status' => ServiceBookingStatusEnum::PENDING,
            'appointment_at' => $form_data['appointment_datetime'],
            'message' => array_key_exists('message', $form_data) ? $form_data['message'] : null,
            'service_id' => $service->id 
        ];
        Auth::user()->serviceBookings()->create($data);
        return $this->apiSuccess('Service booked successfully.');
    }
}
