<?php

namespace App\Http\Controllers\Api\V1\Client\Feedback;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Feedback\ClientFeedbackRequest;
use App\Models\Feedback;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientFeedbackController extends Controller
{
    //
    use ResponseTrait;
    /**
     * @OA\Post(
     *     path="/user/feedback",
     *     summary="Submit feedback from a client",
     *     description="Allows an authenticated client to submit feedback and rating.",
     *     tags={"User Feedback"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"feedback", "rating"},
     *             @OA\Property(
     *                 property="feedback",
     *                 type="string",
     *                 example="Great service and fast delivery!"
     *             ),
     *             @OA\Property(
     *                 property="rating",
     *                 type="integer",
     *                 minimum=1,
     *                 maximum=5,
     *                 example=5,
     *                 description="Rating between 1 and 5"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Feedback submitted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Thank you for your feedback."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=12),
     *                 @OA\Property(property="user_id", type="integer", example=3),
     *                 @OA\Property(property="feedback", type="string", example="Great service and fast delivery!"),
     *                 @OA\Property(property="rating", type="integer", example=5),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-24T10:15:30Z")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The feedback field is required.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    function store(ClientFeedbackRequest $request)
    {
        $user = Auth::user();
        if (!$user) {
            return $this->apiError('Unauthenticated',401);
        }
        $feedback = Feedback::create([
            'user_id' => $user->id,
            'feedback' => $request->feedback,
            'rating' => $request->rating,
        ]);
        return $this->apiSuccess('Thank you for your feedback');
    }
}
