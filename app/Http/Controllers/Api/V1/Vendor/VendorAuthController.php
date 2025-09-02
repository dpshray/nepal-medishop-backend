<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Exceptions\LoginException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\Login\UserLoginRequest;
use App\Http\Resources\User\UserLoginResource;
use App\Services\SanctumTokenService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VendorAuthController extends VendorController
{

    use ResponseTrait;
}
