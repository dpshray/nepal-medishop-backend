<?php

namespace App\Services;

use App\Exceptions\LoginException;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\{DB, Hash};

/**
 * Sanctum Service
 */
class SanctumTokenService
{
    private $access_token, $user = null;

    public function __construct() {}

    public function del($token = null)
    {
        if ($token) {
            $this->access_token = PersonalAccessToken::findToken($token);
            if ($this->access_token) {
                $this->access_token->delete();
            }
        } else {
            auth('sanctum')->user()->tokens()->delete();
        }
        return $this;
    }

    public function check(array $credentials, $callback = null)
    {
        ['email' => $email, 'password' => $password] = $credentials;
        $this->user = $user = User::select('id','uuid','user_type','name','email','password', 'email_verified_at')->firstWhere('email', $email);

        if (!$user->hasVerifiedEmail()) {
            throw new LoginException('email not verified', 403);
        } else if (!$user || !Hash::check($password, $user->password)) {
            throw new LoginException('Invalid Credentials', 401);
        }
        return $this;
    }

    function forAdmin(){
        if (!$this->user->isAdmin()) {
            throw new LoginException("Only admin is allowed to login", 403);
        }
        return $this;
    }

    function forVendor(){
        if (!$this->user->isVendor()) {
            throw new LoginException("Only vendor is allowed to login", 403);
        }
        return $this;
    }

    public function make($user = null)
    {
        if ($this->user == null && $user == null) {
            throw new \Exception('$user parameter cannot be null');
        }
        $user = $this->user ?? $user;
        // $user = Auth::guard('api')->user();
        // if($this->access_token){
        //     $user = $this->access_token->tokenable;
        // }
        $newToken = $user->createToken($user->email . '-client-login', ['*']);
        $plain_text_token = $newToken->plainTextToken;

        return ['user' => $user, 'token' => "Bearer $plain_text_token"];
    }
}
