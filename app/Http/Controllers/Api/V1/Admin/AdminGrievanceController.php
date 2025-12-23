<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\GrievanceEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Grievance\AdminGrievanceDetailResource;
use App\Http\Resources\Admin\Grievance\AdminGrievanceListResource;
use App\Models\Grievance;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class AdminGrievanceController extends Controller
{
    use ResponseTrait, PaginationTrait;
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/grievance",
     *     summary="grievance list",
     *     description="Get grievance list.",
     *     operationId="AdminGrievanceList",
     *     tags={"Grievance"},
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
     *         description="Items on each page",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Search vendor based on user name",
     *         @OA\Schema(type="string", example="")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="All vendor lists",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="All vendor lists"),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="user_uuid", type="string", format="uuid", example="42f9e1a0-0699-425e-af15-2b7485206e68"),
     *                         @OA\Property(property="vendor_uuid", type="string", format="uuid", example="64cc5e61-c98f-41f7-997c-2b4fbedbf4dc"),
     *                         @OA\Property(property="account_status", type="boolean", example=true),
     *                         @OA\Property(property="email_verified", type="boolean", example=true),
     *                         @OA\Property(property="name", type="string", example="vendor30956945"),
     *                         @OA\Property(property="email", type="string", format="email", example="vendor30956945@gmail.com"),
     *                         @OA\Property(property="mobile_number", type="string", example="9808096921"),
     *                         @OA\Property(property="store_name", type="string", example="Oberbrunner PLC")
     *                     )
     *                 ),
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=30),
     *                 @OA\Property(property="total_items", type="integer", example=30)
     *             )
     *         )
     *     )
     * )
    */
    public function index(Request $request)
    {
        $per_page = $request->query('per_page');
        $per_page = $per_page ? $per_page : Grievance::count();
        $search = $request->query('search');
        $pagination = Grievance::with(['user'])
            ->when($search, fn($q) => $q->whereRelation('user', 'name', 'like', '%' . $search . '%'))
            ->paginate($per_page);
        $grievance = $this->makePaginationResponse($pagination, fn($item) => AdminGrievanceListResource::collection($item))->data;
        return $this->apiSuccess('grievance list', $grievance);
    }

    /**
     * Display the specified resource.
     */
    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/grievance/{uuid}",
     *     summary="Grievance show",
     *     description="Grievance show.",
     *     operationId="GrievanceShow",
     *     tags={"Grievance"},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=false,
     *         description="User(vendor) uuid",
     *         @OA\Schema(type="string", example="c80dbce7-a3b5-476f-a618-4f59d4c8bdae")
     *     ),
     *     @OA\Response(
     *       response=200,
     *       description="User grievance details",
     *       @OA\JsonContent(
     *         type="object",
     *     
     *         @OA\Property(
     *           property="message",
     *           type="string",
     *           example="User grievance details"
     *         ),
     *     
     *         @OA\Property(
     *           property="data",
     *           type="object",
     *     
     *           @OA\Property(
     *             property="uuid",
     *             type="string",
     *             format="uuid",
     *             example="d87d24ba-0d29-4d11-aff9-7794c0627375"
     *           ),
     *           @OA\Property(property="status", type="string", example="PENDING"),
     *           @OA\Property(property="name", type="string", example="some name"),
     *           @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *           @OA\Property(property="phone", type="string", example="9841XXXXXX"),
     *           @OA\Property(property="subject", type="string", example="some subject"),
     *           @OA\Property(property="detail", type="string", example="some details"),
     *           @OA\Property(property="submitted_by", type="string", example="user00 oo"),
     *           @OA\Property(property="created_at", type="string", example="2025/12/19"),
     *     
     *           @OA\Property(
     *             property="images",
     *             type="array",
     *             @OA\Items(
     *               type="string",
     *               format="uri",
     *               example="http://example.com/storage/image.jpg"
     *             )
     *           )
     *         ),
     *     
     *         @OA\Property(property="success", type="boolean", example=true)
     *       )
     *     )
     * )
     */
    public function show(Grievance $grievance)
    {
        $grievance->load(['user','media']);
        $grievance = new AdminGrievanceDetailResource($grievance);
        return $this->apiSuccess('User grievance details', $grievance);
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Patch(
     *     security={{"sanctum": {}}},
     *     path="/admin/grievance/{uuid}",
     *     summary="Update grievance status(status: PENDING, UNDER_PROCESS, RESOLVED)",
     *     description="Update grievance status(status: PENDING, UNDER_PROCESS, RESOLVED).",
     *     operationId="UpdateGrievance",
     *     tags={"Grievance"},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="Grievance uuid",
     *         @OA\Schema(type="string", example="")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", example="UNDER_PROCESS"),
     *             @OA\Property(property="remarks", type="string", example="")
     *         )
     *     ),
     *     @OA\Response(
     *       response=200,
     *       description="Grievance status updated successfully.",
     *       @OA\JsonContent(
     *         type="object",
     *     
     *         @OA\Property(
     *           property="message",
     *           type="string",
     *           example="grievance status updated to RESOLVED"
     *         ),
     *         @OA\Property(
     *           property="data",
     *           type="object",
     *           nullable=true,
     *           example=null
     *         ),
     *         @OA\Property(
     *           property="success",
     *           type="boolean",
     *           example=true
     *         )
     *       )
     *     )
     * )
     */
    public function update(Request $request, Grievance $grievance)
    {
        $form_data = $request->validate([
            'status' => ['required', new Enum(GrievanceEnum::class)],
            'remarks' => [Rule::requiredIf($request->status == GrievanceEnum::RESOLVED->value)]
        ]);
        $grievance->update($form_data);
        return $this->apiSuccess('grievance status updated to '.$form_data['status']);
    }
}
