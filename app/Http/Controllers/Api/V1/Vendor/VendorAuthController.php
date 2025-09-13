<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Exceptions\LoginException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\Login\UserLoginRequest;
use App\Http\Requests\Vendor\VendorStoreRequest;
use App\Http\Resources\User\UserLoginResource;
use App\Services\SanctumTokenService;
use App\Services\VendorService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VendorAuthController extends VendorController
{
    use ResponseTrait;
    /**
     * @OA\Post(
     *     path="/vendor/registration",
     *     summary="Store a vendor",
     *     description="Registration api of a vendor.",
     *     operationId="StoreVendor",
     *     tags={"Vendor"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"store_name","store_description","location","country","state","district","municipality","postal_code","bank_name","bank_account_holder_name","bank_account_number","vendor_citizenship_card[]","vendor_business_license[]","vendor_tax_certificate[]","name","email","mobile_number"},
     *                 @OA\Property(property="name", type="string", example="Dave Chappelle"),
     *                 @OA\Property(property="email", type="string", format="email", example="dev.chappelle@mailinator.com"),
     *                 @OA\Property(property="mobile_number", type="string", example="9452114525"),
     *                 @OA\Property(property="store_name", type="string", example="Lilly Lee Store"),
     *                 @OA\Property(property="store_description", type="string", example="Lilly Lee Store Description"),
     *                 @OA\Property(property="location", type="string", example="Maharajgunj"),
     *                 @OA\Property(property="country", type="string", example="Nepal"),
     *                 @OA\Property(property="state", type="string", example="Bagmati Province"),
     *                 @OA\Property(property="district", type="string", example="Kathmandu"),
     *                 @OA\Property(property="municipality", type="string", example="Budhanilkantha Municipality"),
     *                 @OA\Property(property="postal_code", type="string", example="4528"),
     *                 @OA\Property(property="bank_name", type="string", example="Laxmi Sunrise"),
     *                 @OA\Property(property="bank_account_holder_name", type="string", example="Laxmi Thapa"),
     *                 @OA\Property(property="bank_account_number", type="string", example="21547741201300157899"),
     *                 @OA\Property(
     *                     property="vendor_citizenship_card[]",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         format="binary"
     *                     ),
     *                     description="Multiple image files to upload vendor citizenship card"
     *                 ),
     *                 @OA\Property(
     *                     property="vendor_business_license[]",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         format="binary"
     *                     ),
     *                     description="Multiple image files to upload vendor business license"
     *                 ),
     *                 @OA\Property(
     *                     property="vendor_tax_certificate[]",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         format="binary"
     *                     ),
     *                     description="Multiple image files to upload vendor tax certificate"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vendor added successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Vendor has been registered. please check your email to verify"),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     * )
     */
    function registerVendor(VendorStoreRequest $request){
        DB::transaction(function () use ($request) {
            app(VendorService::class)->store($request);
        });
        return $this->apiSuccess('Vendor has been registered. please check your email to verify');
    }
}
