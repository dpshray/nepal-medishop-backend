<?php

namespace App\Http\Controllers\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\Login\UserLoginRequest;
use App\Http\Requests\Auth\Password\ForgetPasswordRequest;
use App\Http\Requests\Auth\Password\ResetPasswordRequest;
use App\Http\Requests\Auth\Register\UserRegisterRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\ResponseTrait;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ResponseTrait;
    //
    public function register(UserRegisterRequest $request)
    {
        DB::transaction(function () use ($request, &$user) {
            $user = User::create([
                'name' => $request->name,
                'mobile_number' => $request->mobile_number,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_admin' => 0,
            ]);


            // $user->profile()->create([
            //     'user_id' => $user->id,
            //     'name' => $request->name,
            // ]);

            event(new Registered($user));
        });
        return $this->apiSuccess('your account has been created successfull');
    }

    public function login(UserLoginRequest $request)
    {
        ['email' => $email, 'password' => $password] = $request->validated();

        $user = User::select('id', 'is_admin', 'name', 'email', 'password', 'email_verified_at')
            ->firstWhere('email', $email);

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        if (!$user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => 'Your email has not been verified.'
            ]);
        }

        $token = $user->createToken($user->email . '-AuthToken')->plainTextToken;
        $token = 'Bearer ' . $token;

        $user = new UserResource($user);
        return $this->apiSuccess('Welcome', [
            'data' => $user,
            'token' => $token
        ]);
    }

    public function forget_Password(ForgetPasswordRequest $request)
    {
        $user = DB::table('users')->where('email', $request->email)->first();

        if ($user && $user->email_verified_at !== null) {
            $token = strtoupper(str()->random(5));

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $request->email],
                [
                    'created_at' => now(),
                    'token' => $token
                ]
            );

            Mail::send('auth.password-reset', ['token' => $token, 'user' => $user], function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('Reset Password');
            });

            return $this->apiSuccess('A password reset link has been sent to your email.');
        }

        return $this->apiError('Please verify your email before resetting your password.');
    }

    public function reset_Password(ResetPasswordRequest $request)
    {
        $reset = DB::table('password_reset_tokens')
            ->where('token', $request->token)
            ->first();

        if (!$reset) {
            return response()->json(['message' => 'Invalid or expired token.'], 400);
        }

        $user = DB::table('users')->where('email', $reset->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Update password manually
        DB::table('users')->where('email', $reset->email)->update([
            'password' => Hash::make($request->password),
            'remember_token' => str()->random(60),
        ]);

        // Delete the token
        DB::table('password_reset_tokens')->where('email', $reset->email)->delete();

        return $this->apiSuccess('Your password has been reset successfully.');
    }
    public function emailVerify(Request $request)
    {
        $user = User::find($request->id);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->forceFill([
            'email_verified_at' => now(),
        ])->save();

        return response()->json(['message' => 'Email verified successfully.'], 200);
    }
}
