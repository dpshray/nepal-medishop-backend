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
     *         description="Login successful response.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="name", type="string", example="Prof. Alison Homenick Sr."),
     *                     @OA\Property(property="uuid", type="string", format="uuid", example="1b286ac2-0bc2-408b-992c-b5dd375a4b23"),
     *                     @OA\Property(property="email", type="string", example="schimmel.dillan@yahoo.com"),
     *                     @OA\Property(
     *                         property="saved_products",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="name", type="string", example="Vision-oriented background benchmark"),
     *                             @OA\Property(property="id", type="string", example="727d6f91-103f-37ea-8576-7ad2ef79df3d")
     *                         )
     *                     )
     *                 ),
     *             ),
     *             @OA\Property(property="token", type="string", example="Bearer 28|uE5tqVeCKIMRD01uPP8QxIAHau53fox2YVRfC9uUda73524c"),
     *             @OA\Property(property="message", type="string", example="Welcome, Prof. Alison Homenick Sr.")
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
