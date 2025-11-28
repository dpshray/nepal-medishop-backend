<?php

namespace App\Http\Controllers\Api\V1\Client\Prescription;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserPrescription\UserPrescriptionResource;
use App\Models\UserPrescription;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientPrescriptionController extends Controller
{
    //
    use ResponseTrait,PaginationTrait;
    /**
     * @OA\Get(
     *     path="/user/prescription",
     *     summary="Get user's prescription list",
     *     description="Returns paginated list of prescriptions for authenticated user",
     *     security={{"sanctum":{}}},
     *     tags={"Prescriptions"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Items per page",
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
     *         description="Prescription list",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="your prescription list"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="description", type="string", example="Headache issue"),
     *                     @OA\Property(property="image_url", type="string", example="https://example.com/prescription.png"),
     *                     @OA\Property(property="created_at", type="string", example="2025-01-10 12:00:00")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    function index(Request $request)
    {
        $per_page = $request->query('per_page', 10);
        $user = Auth::user();
        $paginated = UserPrescription::where('user_id', $user->id)->latest('id')->paginate($per_page);
        $prescription = $this->makePaginationResponse($paginated, fn($item) => UserPrescriptionResource::collection($item))->data;
        return $this->apiSuccess('your prescription list', $prescription);
    }
    /**
     * @OA\Post(
     *     path="/user/prescription",
     *     summary="Store new prescription",
     *     description="Upload prescription image and optional description",
     *     security={{"sanctum":{}}},
     *     tags={"Prescriptions"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="description", type="string", example="Prescription for eye checkup"),
     *                 @OA\Property(
     *                     property="prescription_image",
     *                     type="string",
     *                     format="binary",
     *                     description="PNG/JPG file"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Prescription saved",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="your prescription has been stored")
     *         )
     *     )
     * )
     */
    function store(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'description' => 'nullable|sometimes|string',
            'prescription_image' => 'required|file|mimes:png,jpg'
        ]);
        DB::transaction(function () use ($request, $user) {
            $prescription = UserPrescription::create([
                'user_id' => $user->id,
                'description' => $request->description,
            ]);
            $prescription->addMedia($request->file('prescription_image'))
                ->toMediaCollection(UserPrescription::PRESCRIPTION_IMAGE);
        });
        return $this->apiSuccess('your prescription has been stored');
    }
    /**
     * @OA\Delete(
     *     path="/user/prescription/{prescription}",
     *     summary="Delete a prescription",
     *     description="Delete prescription by ID",
     *     security={{"sanctum":{}}},
     *     tags={"Prescriptions"},
     *     @OA\Parameter(
     *         name="prescription",
     *         in="path",
     *         required=true,
     *         description="ID of the prescription",
     *         @OA\Schema(type="integer", example=3)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Prescription deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="your prescription has been deleted")
     *         )
     *     )
     * )
     */

    function destroy(UserPrescription $prescription)
    {
        $user=Auth::user();
        if($prescription->user_id !=$user->id)
        {
            return $this->apiError('This prescription doesnt belong to you');
        }
        $prescription->delete();
        return $this->apiSuccess('your prescription has been deleted');
    }
}
