<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Constants\VendorContants;
use App\Events\VendorCreateEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\VendorStoreRequest;
use App\Models\User;
use App\Services\VendorService;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
     *                 required={"store_name","store_description","location","country","state","district","municipality","postal_code","bank_name","bank_account_holder_name","bank_account_number","vendor_citizenship_card[]","vendor_business_license[]","vendor_tax_certificate[]","name","email","mobile_number"},
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
     *                 @OA\Property(property="is_verified", type="integer", example=1),
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
     *             @OA\Property(property="message", type="string", example="Vendor added"),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function store(VendorStoreRequest $request)
    {
        DB::transaction(function () use($request){
            app(VendorService::class)->store($request);
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
