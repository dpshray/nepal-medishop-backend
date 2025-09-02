<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Constants\VendorContants;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\VendorStoreRequest;
use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminVendorController extends Controller
{
    use ResponseTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/admin/vendor",
     *     summary="Store a vendor",
     *     description="Save an information of a vendor.",
     *     operationId="StoreVendor",
     *     tags={"Vendor"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"store_name","store_description","location","country","state","district","municipality","postal_code","bank_name","bank_account_holder_name","bank_account_number","vendor_citizenship_card","vendor_business_license","vendor_tax_certificate","name","email","mobile_number"},
     *                 @OA\Property(property="name", type="string", format="email", example="Dave Chappelle"),
     *                 @OA\Property(property="email", type="string", format="email", example="dev.chappelle@mailinator.com"),
     *                 @OA\Property(property="mobile_number", type="string", example="9452114525"),
     *                 @OA\Property(property="store_name", type="string", example="Lilly Lee Store"),
     *                 @OA\Property(property="store_description", type="string", example="Lilly Lee Store Description"),
     *                 @OA\Property(property="location", type="string", format="date", example="Maharajgunj"),
     *                 @OA\Property(property="country", type="string", format="date", example="Nepal"),
     *                 @OA\Property(property="state", type="string", format="date", example="Bagmati Province"),
     *                 @OA\Property(property="district", type="string", format="date", example="Kathmandu"),
     *                 @OA\Property(property="municipality", type="string", format="date", example="Budhanilkantha Municipality"),
     *                 @OA\Property(property="postal_code", type="string", example="4528"),
     *                 @OA\Property(property="bank_name", type="string", example="Laxmi Sunrise"),
     *                 @OA\Property(property="bank_account_holder_name", type="string", example="Laxmi Thapa"),
     *                 @OA\Property(property="bank_account_number", type="string", example="21547741201300157899"),
     *                 @OA\Property(
     *                     property="vendor_citizenship_card",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         format="binary"
     *                     ),
     *                     description="Multiple image files to upload vendor citizenship card"
     *                 ),
     *                 @OA\Property(
     *                     property="vendor_business_license",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         format="binary"
     *                     ),
     *                     description="Multiple image files to upload vendor business license"
     *                 ),
     *                 @OA\Property(
     *                     property="vendor_tax_certificate",
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
     *         description="Success response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example="true"),
     *             @OA\Property(
     *                  property="data", 
     *                  type="object",
     *                  @OA\Property(property="id", type="integer", example="10"),
     *                  @OA\Property(property="name", type="string", example="Lilly Lee"),
     *                  @OA\Property(property="dob", type="date", example="2082-01-15"),
     *                  @OA\Property(property="gender", type="boolean", example="FEMALE"),
     *                  @OA\Property(property="media", type="string", example="http://127.0.0.1:8000/storage/36/conversions/flowers-7382926_1920-thumbnail.jpg")
     *                 ),
     *             @OA\Property(property="message", type="string", example="An infant added successfully")
     *         )
     *     )
     * )
     */
    public function store(VendorStoreRequest $request)
    {
        // Log::info(request()->all());
        DB::transaction(function () use($request){
            $user = $request->safe()->only(["name", "email", "mobile_number"]);
            $password = str()->random(10);
            $user['password'] = $password;
            $vendor = $request->safe()->except(["name", "email", "mobile_number", "vendor_citizenship_card", "vendor_business_license", "vendor_tax_certificate"]);
            $user = User::create($user)
                ->vendor()
                ->create($vendor);
            $user->addMedia($request->file('vendor_citizenship_card'))->toMediaCollection(VendorContants::VENDOR_BUSINESS_LICENSE);
            $user->addMedia($request->file('vendor_business_license'))->toMediaCollection(VendorContants::VENDOR_CITIZENSHIP_CARD);
            $user->addMedia($request->file('vendor_tax_certificate'))->toMediaCollection(VendorContants::VENDOR_TAX_CERTIFICATE);
        });
        return $this->apiSuccess('Vendor added');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
