<?php

namespace App\Http\Controllers\Api\V1\Admin\ClientFeedback;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ClientFeedback\AdminFeedbackResource;
use App\Models\Feedback;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Ramsey\Uuid\Type\Integer;

class AdminFeedbackController extends Controller
{
    //
    use ResponseTrait, PaginationTrait;
    /**
     * @OA\Get(
     *     path="/admin/clientfeedback",
     *     summary="Get list of client feedback",
     *     description="Retrieve a paginated list of client feedback with optional search by user's name or email.",
     *     operationId="getClientFeedback",
     *     tags={"Feedback"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", default=10),
     *         description="Number of feedback items per page"
     *     ),
     *
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Search feedback by user's name or email"
     *     ),
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", default=1),
     *         description="Page number for pagination"
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of Client Feedback",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="List of Client Feedback"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=25),
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=12),
     *                         @OA\Property(property="feedback", type="string", example="Great service, thank you!"),
     *                         @OA\Property(property="rating", type="integer", example=5),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-13T09:25:00Z"),
     *                         @OA\Property(
     *                             property="user",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=5),
     *                             @OA\Property(property="name", type="string", example="John Doe"),
     *                             @OA\Property(property="email", type="string", example="johndoe@example.com")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */

    function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->query('search', null);
        $query = Feedback::with(['user']);
        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }
        $query->latest();
        $paginated = $query->paginate($perPage);
        $data = $this->makePaginationResponse($paginated, fn($items) => AdminFeedbackResource::collection($items))->data;
        return $this->apiSuccess('List of Client Feedback', $data);
    }
}
