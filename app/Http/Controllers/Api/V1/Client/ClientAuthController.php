<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Enums\UserTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\Register\UserRegisterRequest;
use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;

class ClientAuthController extends ClientController
{
    use ResponseTrait;
    /**
     * @OA\Post(
     *     path="/register",
     *     summary="User Register Form",
     *     description="Registration form for user.",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","mobile_number","email","password"},
     *             @OA\Property(property="name", type="string", format="email", example="Dave Chappelle"),
     *             @OA\Property(property="email", type="string", format="email", example="dev.chappelle@mailinator.com"),
     *             @OA\Property(property="mobile_number", type="string", example="9452114525"),
     *             @OA\Property(property="password", type="string", example="secret123"),
     *             @OA\Property(property="password_confirmation", type="string", example="secret123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful registration",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Please check your email to verify registration"),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function register(UserRegisterRequest $request){
        $form_data = $request->validated();
        $form_data['user_type'] = UserTypeEnum::USER->value;
        DB::transaction(function () use($form_data){  
            $user = User::create($form_data);
            event(new Registered($user));
        });
        return $this->apiSuccess('Please check your email to verify registration');
    }

    function emailVerifier(Request $request)
    {
        $user_id = $request->query('id');
        $user = User::findOrFail($user_id);
        if (!hash_equals(sha1($user->getEmailForVerification()), $request->query('hash'))) {
            abort(403, 'Invalid verification hash.');
        }

        $user->markEmailAsVerified();
        return view('auth.client.mail.email_verified');
    }
}
