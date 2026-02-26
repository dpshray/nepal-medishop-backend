<?php

namespace App\Http\Controllers\Api\V1\Client\Review;

use App\Enums\GrievanceEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\Review\Grievance\UserGrievanceDetailResource;
use App\Http\Resources\User\Review\Grievance\UserGrievanceListResource;
use App\Models\Grievance;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClientGrievanceController extends Controller
{
    use ResponseTrait, PaginationTrait;

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/user/grievance",
     *     summary="Get user submitted grievance list.",
     *     description="Get user submitted grievance list.",
     *     operationId="GrievanceList",
     *     tags={"Grievance"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="no of items per page",
     *         @OA\Schema(type="integer", example="1")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="page number of pagination",
     *         @OA\Schema(type="integer", example="1")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of user grievances",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="List of user grievances"
     *             ),
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(
     *                             property="uuid",
     *                             type="string",
     *                             format="uuid",
     *                             example="d87d24ba-0d29-4d11-aff9-7794c0627375"
     *                         ),
     *                         @OA\Property(
     *                             property="status",
     *                             type="string",
     *                             example="PENDING"
     *                         ),
     *                         @OA\Property(
     *                             property="subject",
     *                             type="string",
     *                             example="Delivery delay"
     *                         ),
     *                         @OA\Property(
     *                             property="submitted_at",
     *                             type="string",
     *                             example="2025-12-19"
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="page",
     *                     type="integer",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="total_page",
     *                     type="integer",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="total_items",
     *                     type="integer",
     *                     example=1
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    function index(Request $request)
    {
        $per_page = $request->query('per_page');
        $per_page = $per_page ? $per_page : Auth::user()->grievances()->count();
        $pagination = Auth::user()->grievances()->latest()->paginate($per_page);
        $data = $this->makePaginationResponse($pagination, fn($item) => UserGrievanceListResource::collection($item))->data;
        return $this->apiSuccess('List of user grievances', $data);
    }

    /**
     * Handle the incoming request.
     */
    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/user/grievance",
     *     summary="Store a user grievance",
     *     description="Store a user grievance.",
     *     operationId="ClientGrievance",
     *     tags={"Grievance"},
     *     @OA\RequestBody(
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="multipart/form-data",
     *                 @OA\Schema(
     *                     required={"name","email","subject","detail"},
     *                     @OA\Property(
     *                         property="name",
     *                         type="string",
     *                         maxLength=255
     *                     ),
     *                     @OA\Property(
     *                         property="email",
     *                         type="string",
     *                         format="email"
     *                     ),
     *                     @OA\Property(
     *                         property="phone",
     *                         type="string"
     *                     ),
     *                     @OA\Property(
     *                         property="subject",
     *                         type="string",
     *                         maxLength=255
     *                     ),
     *                     @OA\Property(
     *                         property="detail",
     *                         type="string"
     *                     ),
     *                     @OA\Property(
     *                         property="images",
     *                         type="array",
     *                         @OA\Items(
     *                             type="string",
     *                             format="binary"
     *                         ),
     *                         nullable=true
     *                     )
     *                 )
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item reviewed successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Your grievance has been submitted successfully."),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $form_data = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required',
            'phone' => 'required',
            'subject' => 'required|max:255',
            'detail' => 'required',
            'images' => 'sometimes|nullable|array|exclude'
        ]);
        // Log::info($request->images);
        DB::transaction(function () use ($form_data, $request) {
            $form_data['user_id'] = Auth::id();
            $form_data['status'] = GrievanceEnum::PENDING;
            $grievance = Grievance::create($form_data);
            if ($request->hasFile('images')) {
                foreach ($request->images as $image) {
                    $grievance->addMedia($image)->toMediaCollection(Grievance::GRIEVANCE_IMAGE);
                }
            }
        });
        return $this->apiSuccess("Your grievance has been submitted successfully.");
    }

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/user/grievance/{uuid}",
     *     summary="Fetch user grievance detail.",
     *     description="Fetch user grievance detail.",
     *     operationId="GrievanceShow",
     *     tags={"Grievance"},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of grievance",
     *         @OA\Schema(type="string", example="")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Grievance detail",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Grievance detail"
     *             ),
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="uuid",
     *                     type="string",
     *                     format="uuid",
     *                     example="fcdcb8a7-0fe0-46a3-b3f0-e9c335981b7d"
     *                 ),
     *                 @OA\Property(
     *                     property="status",
     *                     type="string",
     *                     enum={"PENDING","IN_REVIEW","RESOLVED"},
     *                     example="PENDING"
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     example="grievance no image"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     format="email",
     *                     example="techno@example.com"
     *                 ),
     *                 @OA\Property(
     *                     property="phone",
     *                     type="string",
     *                     example="9854112547"
     *                 ),
     *                 @OA\Property(
     *                     property="subject",
     *                     type="string",
     *                     example="grievance no image subject"
     *                 ),
     *                 @OA\Property(
     *                     property="detail",
     *                     type="string",
     *                     example="grievance no image detail"
     *                 ),
     *                 @OA\Property(
     *                     property="created_at",
     *                     type="string",
     *                     format="date-time",
     *                     example="2025-12-19T10:49:34.000000Z"
     *                 ),
     *                 @OA\Property(
     *                     property="images",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         example="https://example.com/storage/grievances/image1.jpg"
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    function show(Grievance $grievance)
    {
        $grievance->load('media');
        $grievance = new UserGrievanceDetailResource($grievance);
        return $this->apiSuccess('Grievance detail', $grievance);
    }
}
