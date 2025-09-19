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

class VendorService {

    public function store(Request $request){
        $user = $request->safe()->only(["name", "email", "mobile_number"]);
        $user['user_type'] = UserTypeEnum::VENDOR->value;
        $password = str()->random(10);
        $user['password'] = $password;
        $vendor = $request->safe()->except(["name", "email", "mobile_number", "vendor_citizenship_card", "vendor_business_license", "vendor_tax_certificate"]);
        if (Auth::check() && Auth::user()->isAdmin()) {
            $vendor['verified_at'] = $request->is_verified == 1 ? now() : null;
        }
        $vendor = User::create($user)
            ->vendor()
            ->create($vendor);
        
        $vendor->addMedia($request->file('vendor_citizenship_card'))->toMediaCollection(VendorContants::VENDOR_BUSINESS_LICENSE);
        $vendor->addMedia($request->file('vendor_business_license'))->toMediaCollection(VendorContants::VENDOR_CITIZENSHIP_CARD);
        $vendor->addMedia($request->file('vendor_tax_certificate'))->toMediaCollection(VendorContants::VENDOR_TAX_CERTIFICATE);

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
