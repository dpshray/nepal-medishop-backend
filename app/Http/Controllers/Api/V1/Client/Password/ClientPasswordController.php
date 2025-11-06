<?php

namespace App\Http\Controllers\Api\V1\Client\Password;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Password\PasswordChangeRequest;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ClientPasswordController extends Controller
{
    //
    use ResponseTrait;
    /**
     * @OA\Post(
     *     path="/user/change-password",
     *     summary="Change user password",
     *     description="Allows the authenticated user to change their password by verifying the old password first.",
     *     tags={"User"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"old_password", "new_password", "new_password_confirmation"},
     *             @OA\Property(property="old_password", type="string", format="password", example="OldPass123"),
     *             @OA\Property(property="new_password", type="string", format="password", example="NewPass456"),
     *             @OA\Property(property="new_password_confirmation", type="string", format="password", example="NewPass456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password changed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password change successful")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Old password does not match",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Old password does not matched")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */

    function ChangePassword(PasswordChangeRequest $request)
    {
        $user = Auth::user();
        if($request->old_password ===$request->new_password)
        {
            return $this->apiError('New password cannot be the same as the old password.');
        }
        if (Hash::check($request->old_password, $user->password)) {
            $user->password = Hash::make($request->new_password);
            $user->save();
            return $this->apiSuccess('Password change successfull');
        }
        return $this->apiError('Old password does not matched');
    }
}
