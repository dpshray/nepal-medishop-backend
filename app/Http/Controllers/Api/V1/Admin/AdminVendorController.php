<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\UserTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\VendorStoreRequest;
use App\Http\Resources\Admin\Vendor\AdminVendorUserList;
use App\Http\Resources\Admin\Vendor\AdminVendorUserResource;
use App\Models\User;
use App\Models\Vendor;
use App\Services\VendorService;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminVendorController extends Controller
{
    use ResponseTrait, PaginationTrait;

    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/vendor",
     *     summary="Vendor list",
     *     description="Get vendor list.",
     *     operationId="VendorList",
     *     tags={"Vendor"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number of list",
     *         @OA\Schema(type="integer", example=1)
     *     ),     
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Items on each page",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="verified_vendors",
     *         in="query",
     *         required=false,
     *         description="Filter vendor lists based on verified/unverified(1->verified and 0->unverified)",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inactive vendor lists",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Inactive vendor lists"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="user_uuid", type="string", format="uuid", example="0c8f6da3-58fb-4f5e-ae52-5494a38a3b0e"),
     *                         @OA\Property(property="vendor_uuid", type="string", format="uuid", example="04546ab6-bb58-41f4-b1d7-e42cbd7ec778"),
     *                         @OA\Property(property="name", type="string", example="vendor280"),
     *                         @OA\Property(property="mobile_number", type="string", example="9870807888"),
     *                         @OA\Property(property="store_name", type="string", example="Kovacek and Sons"),
     *                     )
     *                 ),
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=17),
     *                 @OA\Property(property="total_items", type="integer", example=17),
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $per_page = $request->per_page;
        $verified_vendor = $request->query('verified_vendors',1);
        $pagination = User::filterByRole(UserTypeEnum::VENDOR)
            ->with(['vendor'])
            ->when(
                $verified_vendor == 1,
                fn($qry) => $qry->whereRelation('vendor', 'verified_at','!=',null), 
                fn($qry) => $qry->whereRelation('vendor', 'verified_at', null))
            ->latest()
            ->paginate($per_page);
        // $items = $pagination->items();
        // return new AdminVendorUserList($items);
        $data = $this->makePaginationResponse($pagination, fn($items) => AdminVendorUserList::collection($items))->data;
        $stat = $verified_vendor == 1 ? 'Verified' : 'Unverified';
        return $this->apiSuccess("$stat vendor lists", $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/admin/vendor",
     *     summary="Register a vendor by admin",
     *     description="Save/Register an information of a vendor.",
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
    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/vendor/{uuid}",
     *     summary="Vendor user show",
     *     description="Vendor user show.",
     *     operationId="VendorShow",
     *     tags={"Vendor"},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=false,
     *         description="Vendor user uuid",
     *         @OA\Schema(type="string", example="c80dbce7-a3b5-476f-a618-4f59d4c8bdae")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vendor user detail",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Vendor user detail"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="Dave Chappelle"),
     *                 @OA\Property(property="email", type="string", example="dev.chappelle@mailinator.com"),
     *                 @OA\Property(property="mobile_number", type="string", example="9452114525"),
     *                 @OA\Property(
     *                     property="vendor_details",
     *                     type="object",
     *                     @OA\Property(property="is_verified", type="boolean", example=true),
     *                     @OA\Property(property="store_name", type="string", example="Lilly Lee Store"),
     *                     @OA\Property(property="store_description", type="string", example="Lilly Lee Store Description"),
     *                     @OA\Property(property="location", type="string", example="Maharajgunj"),
     *                     @OA\Property(property="country", type="string", example="Nepal"),
     *                     @OA\Property(property="state", type="string", example="Bagmati Province"),
     *                     @OA\Property(property="district", type="string", example="Kathmandu"),
     *                     @OA\Property(property="municipality", type="string", example="Budhanilkantha Municipality"),
     *                     @OA\Property(property="postal_code", type="string", example="4528"),
     *                     @OA\Property(property="bank_name", type="string", example="Laxmi Sunrise"),
     *                     @OA\Property(property="bank_account_holder_name", type="string", example="Laxmi Thapa"),
     *                     @OA\Property(property="bank_account_number", type="string", example="21547741201300156000"),
     *                     @OA\Property(
     *                         property="documents",
     *                         type="object",
     *                         @OA\Property(
     *                             property="citizenship_card",
     *                             type="array",
     *                             @OA\Items(type="string", format="url", example="http://127.0.0.1:8000/storage/93/meadow-16044_1920.jpg")
     *                         ),
     *                         @OA\Property(
     *                             property="tax_certificate",
     *                             type="array",
     *                             @OA\Items(type="string", format="url", example="http://127.0.0.1:8000/storage/95/lucas-k-wQLAGv4_OYs-unsplash.jpg")
     *                         ),
     *                         @OA\Property(
     *                             property="business_license",
     *                             type="array",
     *                             @OA\Items(type="string", format="url", example="http://127.0.0.1:8000/storage/91/animal-4855514_1920.jpg")
     *                         )
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function show($uuid)
    {
        $user = User::with(['vendor' => ['media']])->firstWhere('uuid', $uuid);
        $user = new AdminVendorUserResource($user);
        return $this->apiSuccess('Vendor user detail', $user);
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

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/vendor-verified-toggler/{uuid}",
     *     summary="Toggle vendor verification status",
     *     description="Toggle vendor verification status",
     *     operationId="VendorVerifierToggler",
     *     tags={"Vendor"},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="Uuid of a vendor",
     *         @OA\Schema(type="string", example="ff5487b6-72f4-4a7f-bd0f-0abbde78db27")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vendor status updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Vendor verification status changed to ACTIVE"),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     * )
     */
    function toggleVendorVerifiedStatus(Vendor $vendor){
        $current_verification_status = $vendor->verified_at != null;
        $message = 'Vendor verification status changed to ACTIVE';
        if ((int)$current_verification_status == 1) {
            $message = 'Vendor verification status changed to INACTIVE';
        }
        $vendor->update([
            'verified_at' => !(bool)$current_verification_status ? now() : null
        ]);
        return $this->apiSuccess($message);
    }
}
