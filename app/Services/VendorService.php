<?php

namespace App\Services;

use App\Constants\VendorContants;
use App\Enums\UserTypeEnum;
use App\Events\VendorCreateEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class VendorService
{

    public function store(Request $request)
    {
        $user = $request->safe()->only(["name", "email", "password", "mobile_number", "commission_percentage"]);
        $user['user_type'] = UserTypeEnum::VENDOR->value;
        $password = $request->password;
        $vendor = $request->safe()->except(["name", "email", "password", "mobile_number", "commission_percentage", "vendor_citizenship_card", "vendor_business_license", "vendor_tax_certificate"]);
        if (Auth::check() && Auth::user()->isAdmin()) {
            $user['email_verified_at'] = now();
            $vendor['verified_at'] = now();
            $user['status'] = true;
        }
        $vendor = User::create($user)
            ->vendor()
            ->create($vendor);
        if ($request->hasFile('vendor_citizenship_card')) {
            $vendor->addMedia($request->file('vendor_citizenship_card'))->toMediaCollection(VendorContants::VENDOR_BUSINESS_LICENSE);
        }
        if ($request->hasFile('vendor_business_license')) {
            $vendor->addMedia($request->file('vendor_business_license'))->toMediaCollection(VendorContants::VENDOR_CITIZENSHIP_CARD);
        }
        if ($request->hasFile('vendor_tax_certificate')) {
            $vendor->addMedia($request->file('vendor_tax_certificate'))->toMediaCollection(VendorContants::VENDOR_TAX_CERTIFICATE);
        }


        $link = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $vendor->user->id,
                'hash' => sha1($vendor->user->getEmailForVerification()),
            ]
        );

        event(new VendorCreateEvent($vendor, $password, $link));
    }
}
