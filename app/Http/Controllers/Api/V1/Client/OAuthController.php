<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Enums\UserTypeEnum;
use App\Exceptions\LoginException;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserLoginResource;
use App\Models\User;
use App\Services\SanctumTokenService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class OAuthController extends Controller
{
    use ResponseTrait;
    /**
     * Handle the incoming request.
     */
    /**
     * @OA\Post(
     *     path="/login/google",
     *     summary="Google Login",
     *     description="Google Login",
     *     operationId="GoogleLogin",
     *     tags={"Google"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example=""),
     *             @OA\Property(property="fcm_token", type="string", example="")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=10),
     *                 @OA\Property(property="name", type="string", example="Lilly Lee"),
     *                 @OA\Property(property="dob", type="string", format="date", example="2082-01-25"),
     *                 @OA\Property(property="gender", type="boolean", example="FEMALE"),
     *                 @OA\Property(property="media", type="string", example="http://127.0.0.1:8000/storage/36/conversions/flowers-7382926_1920-thumbnail.jpg")
     *             ),
     *             @OA\Property(property="message", type="string", example="Infant information has been updated")
     *         )
     *     )
     * )
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'fcm_token' => 'nullable'
        ], [
            'token.required' => 'google token id is required',
            'fcm_token.nullable' => 'fcm token is nullable'
        ]);
        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->userFromToken($request->input('token'));
            $email = $googleUser->getEmail();
            $user = User::updateOrCreate([
                'email' => $email
            ], [
                'name' => $googleUser->getName(),
                'google_id' => $googleUser->getId(),
                'email_verified_at' => now(),
                'fcm_token' => $request->fcm_token ?? null,
                'status' => true,
                'user_type' => UserTypeEnum::USER->value
            ]);

            $STS = new SanctumTokenService();
            ['user' => $user, 'token' => $token] = $STS->make($user);

            return $this->apiSuccess("Welcome, $user->name", [
                'user' => new UserLoginResource($user),
                'token' => $token
            ]);
        } catch (LoginException $e) {
            Log::error($e);
            return $this->apiError("Unable to process your request at the moment.", 401);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->apiError("Unable to process your request at the moment.", 401);
        }
    }
}
