<?php

namespace App\Http\Middleware;

use App\Exceptions\LoginException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;

class VendorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $vendor = Auth::user();
        if (!$vendor->hasVerifiedEmail()) {
            throw new AuthenticationException('Please verify your mail to continue.');
        } else if (!$vendor->is_active) {
            throw new AuthenticationException('User account is currently inactive.');
        } else if (!(bool)Auth::user()->isVendor()) {
            throw new AuthenticationException('Unauthorized: This action is restricted to vendors only.');
        }
        return $next($request);
    }
}
