<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Exceptions\LoginException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\Login\UserLoginRequest;
use App\Http\Resources\User\UserLoginResource;
use App\Services\SanctumTokenService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminAuthController extends AdminController
{
    use ResponseTrait;

    /**
     * @OA\Post(
     *     path="/admin/login",
     *     summary="Admin login Api",
     *     description="Login API for admin.",
     *     tags={"AdminAuthentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="admin@gmail.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful login",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Welcome, admin"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="uuid", type="string", format="uuid", example="9c10176e-f1b1-37ae-8e0f-958d6176246c"),
     *                     @OA\Property(property="name", type="string", example="admin"),
     *                     @OA\Property(property="email", type="string", format="email", example="admin@gmail.com")
     *                 ),
     *                 @OA\Property(property="token", type="string", example="Bearer 3|9x5lWn2WdVWSrVGP7zOYSaOdEK9e4t5hIQ2Q27KX08ebeca4")
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function login(UserLoginRequest $request){
        try {
            $formData = $request->validated();
            $STS = new SanctumTokenService();
            ['user' => $user, 'token' => $token] = $STS->check($formData)->forAdmin()->make();
            return $this->apiSuccess("Welcome, $user->name", [
                'user' => new UserLoginResource($user),
                'token' => $token
            ]);
        } catch(LoginException $e){
            return $this->apiError($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            Log::info($e);
            return $this->apiError('Something went wrong. please try again later.');
        }
    }
}
