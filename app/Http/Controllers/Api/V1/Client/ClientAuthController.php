<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Enums\UserTypeEnum;
use App\Exceptions\LoginException;
use App\Http\Requests\Auth\Login\UserLoginRequest;
use App\Http\Requests\Auth\Password\ForgetPasswordRequest;
use App\Http\Requests\Auth\Register\UserRegisterRequest;
use App\Http\Resources\User\UserLoginResource;
use App\Models\User;
use App\Services\SanctumTokenService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ClientAuthController extends ClientController
{
    use ResponseTrait;
    /**
     * @OA\Post(
     *     path="/register",
     *     summary="User Register Form(only client can register)",
     *     description="Registration form for user.",
     *     tags={"Authentication"},
     *     security={{"sanctum": {}}},
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
    function register(UserRegisterRequest $request)
    {
        $form_data = $request->validated();
        $form_data['user_type'] = UserTypeEnum::USER->value;
        $form_data['status'] = true;
        DB::transaction(function () use ($form_data, $request) {
            $user = User::create($form_data);
            event(new Registered($user));
            if ($request->hasFile('image')) {
                $user->addMedia($request->file('image'))
                    ->toMediaCollection(User::USER_PROFILE);
            }
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
        DB::transaction(function () use($user){
            if ($user->isVendor()) {
                $user->update(['status' => true]);
                $user->vendor(['verified_at' => now()]);
            }
            $user->markEmailAsVerified();
        });
        return view('auth.mail.email_verified');
    }


    /**
     * @OA\Post(
     *     path="/login",
     *     summary="Login Api",
     *     description="Login API(user_type : USER | VENDOR | ADMIN).",
     *     tags={"Authentication"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="vendor@gmail.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful vendor login",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Welcome, vendor00"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="uuid", type="string", format="uuid", example="0b831981-5ad1-3bdc-a1bd-b514676b3f98"),
     *                     @OA\Property(property="name", type="string", example="vendor00"),
     *                     @OA\Property(property="email", type="string", format="email", example="vendor@gmail.com"),
     *                     @OA\Property(property="user_type", type="string", example="VENDOR")
     *                 ),
     *                 @OA\Property(
     *                     property="token",
     *                     type="string",
     *                     example="Bearer 4|VzkoJxyMfelFTKdiJlEnN3n3OhqTxS5SKSzDxJ2z61dd765a"
     *                 )
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function login(UserLoginRequest $request)
    {
        try {
            $formData = $request->validated();
            $STS = new SanctumTokenService();
            ['user' => $user, 'token' => $token] = $STS->check($formData)->make();
            if(!empty($request->fcm_token))
            {
                $user->update([
                    'fcm_token'=>$request->fcm_token,
                ]);
            }
            return $this->apiSuccess("Welcome, $user->name", [
                'user' => new UserLoginResource($user),
                'token' => $token
            ]);
        } catch (LoginException $e) {
            return $this->apiError($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            Log::info($e);
            return $this->apiError('Something went wrong. please try again later.');
        }
    }

    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/logout",
     *     summary="User logout",
     *     description="logs out a user.",
     *     operationId="LogoutUser",
     *     tags={"Authentication"},
     *     @OA\Response(
     *         response=200,
     *         description="User logout successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="data", type="string", example=null),
     *             @OA\Property(property="message", type="string", example="You are logged out")
     *         )
     *     )
     * )
     */
    public function logout()
    {
        (new SanctumTokenService())->del();
        return $this->apiSuccess('You are logged out.');
    }

    /**
     * @OA\Post(
     *     path="/forgot-password",
     *     summary="Forgot password form handler",
     *     description="Forgot password form handler",
     *     operationId="ForgotPassword",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="vendor@gmail.com"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User logout successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(property="data", type="string", example=null),
     *             @OA\Property(property="message", type="string", example="You are logged out")
     *         )
     *     )
     * )
     */
    function sendPasswordResetLink(ForgetPasswordRequest $request)
    {
        $status = Password::sendResetLink(
            $request->only('email')
        );
        $status = Password::ResetLinkSent;
        if (Password::RESET_LINK_SENT == 'passwords.sent') {
            return $this->apiSuccess('A password reset link has been sent to your email.');
        }
        return $this->apiError($status);
    }

    function paswordResetorFormHandler(Request $request, $token)
    {

        if ($request->isMethod('POST')) {
            $request->validate([
                // 'token' => 'required',
                'email' => 'required|email',
                'password' => 'required|min:8|confirmed',
            ]);
            try {
                $credentials = $request->only('email', 'password', 'password_confirmation');
                $credentials['token'] = $token;
                $status = Password::reset(
                    $credentials,
                    function (User $user, string $password) use ($request) {
                        if (Hash::check($password, $user->password)) {
                            throw ValidationException::withMessages([
                                'password' => ['Please choose a different password.'],
                            ]);
                        }
                        $user->forceFill([
                            'password' => Hash::make($password)
                        ])->setRememberToken(Str::random(60));
                        $user->save();
                        event(new PasswordReset($user));
                    }
                );
            } catch (\Exception $e) {
                return $this->apiError($e->getMessage(), 422);
            }
            if ($status === Password::PasswordReset) {
                return $this->apiSuccess('Your password has been reset.');
            }
            $message = match ($status) {
                Password::INVALID_USER => 'Invalid user/email',
                Password::INVALID_TOKEN => 'Token is invalid/expired',
                default => 'Error occured. please try again'
            };
            return $this->apiError($message);
        }
        return view('auth.mail.password-reset', compact('token'));
    }
}
