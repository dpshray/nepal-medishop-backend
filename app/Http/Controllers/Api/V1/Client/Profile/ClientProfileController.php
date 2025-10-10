<?php

namespace App\Http\Controllers\Api\V1\Client\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Profile\ProfileUpdateRequest;
use App\Http\Resources\User\Profile\UserProfileResource;
use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientProfileController extends Controller
{
    //
    use ResponseTrait;
    /**
     * @OA\Get(
     *     path="/user/profile",
     *     summary="Get authenticated user profile",
     *     description="Retrieve details of the currently authenticated user.",
     *     tags={"User"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="User profile retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="user detail"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                 @OA\Property(property="status", type="string", example="Active"),
     *                 @OA\Property(property="user_type", type="string", example="Admin"),
     *                 @OA\Property(property="mobile_number", type="string", example="+9779812345678")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    function index()
    {
        $user = Auth::user();
        return $this->apiSuccess('user detail', new UserProfileResource($user));
    }
    /**
     * @OA\Post(
     *     path="/user/profile",
     *     summary="Update authenticated user profile",
     *     description="Update user's name, mobile number, and optional profile image. Use `_method=PUT` for method spoofing.",
     *     tags={"User"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"_method"},
     *                 @OA\Property(
     *                     property="_method",
     *                     type="string",
     *                     example="PUT",
     *                     description="Laravel method spoofing for PUT/PATCH requests"
     *                 ),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="mobile_number", type="string", example="9840000000"),
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary",
     *                     description="Optional profile image to upload"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="profile update successfull"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="mobile_number", type="string", example="9840000000"),
     *                 @OA\Property(property="image", type="string", example="http://example.com/storage/profile/abcd1234.jpg")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */

    function update(ProfileUpdateRequest $request)
    {
        $user = Auth::user();
        $data = $request->validated();
        $user->update($data);
        if ($request->hasFile('image')) {
            $user->clearMediaCollection(User::USER_PROFILE);
            $user->addMedia($request->file('image'))
                ->toMediaCollection(User::USER_PROFILE);
        }
        return $this->apiSuccess('profile update successfull');
    }
}
