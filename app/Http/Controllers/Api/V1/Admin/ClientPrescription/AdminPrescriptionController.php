<?php

namespace App\Http\Controllers\Api\V1\Admin\ClientPrescription;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\UserPrescription\AdminUserPrescriptionListResource;
use App\Models\UserPrescription;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class AdminPrescriptionController extends Controller
{
    //
    use ResponseTrait, PaginationTrait;
    /**
     * @OA\Get(
     *     path="/admin/prescription",
     *     summary="Get list of prescriptions",
     *     description="Returns paginated list of prescriptions. Can search by user name or email.",
     *     operationId="getPrescriptions",
     *     tags={"Prescriptions"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by user name or email",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Prescription list"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="description", type="string", example="Eye prescription"),
     *                     @OA\Property(property="prescription_image", type="string", example="https://example.com/image.png"),
     *                     @OA\Property(property="created_at", type="string", example="2025-11-27 09:00:00"),
     *                     @OA\Property(property="updated_at", type="string", example="2025-11-27 09:00:00"),
     *                     @OA\Property(
     *                         property="user",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="John Doe"),
     *                         @OA\Property(property="email", type="string", example="john@example.com"),
     *                         @OA\Property(property="mobile_number", type="string", example="+9779812345678")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    function index(Request $request)
    {
        $per_page = $request->query('per_page', 10);
        $search = $request->query('search');
        $prescriptions = UserPrescription::with('user')
            ->when($search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate($per_page);
        $data = $this->makePaginationResponse($prescriptions, fn($items) => AdminUserPrescriptionListResource::collection($items))->data;
        return $this->apiSuccess('Prescription list', $data);
    }
    /**
     * @OA\Delete(
     *     path="/admin/prescription/{id}",
     *     summary="Delete a prescription",
     *     description="Deletes a prescription by ID.",
     *     operationId="deletePrescription",
     *     tags={"Prescriptions"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the prescription",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Prescription deleted successfully"),
     *     @OA\Response(response=404, description="Prescription not found"),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    function destroy(UserPrescription $prescription)
    {
        $prescription->delete();
        return $this->apiSuccess('User prescription has been deleted');
    }
}
