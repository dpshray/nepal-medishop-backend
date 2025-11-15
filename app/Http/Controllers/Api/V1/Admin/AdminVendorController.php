<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Constants\VendorContants;
use App\Enums\UserTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\VendorStoreRequest;
use App\Http\Resources\Admin\Vendor\AdminVendorProductListResource;
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
     *         description="Filter vendor lists based on verified/unverified(All -> 'All list', 1->verified, 0->unverified)",
     *         @OA\Schema(type="string", example="All")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Search vendor based on name/email",
     *         @OA\Schema(type="string", example="vendor")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="All vendor lists",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="All vendor lists"),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="user_uuid", type="string", format="uuid", example="42f9e1a0-0699-425e-af15-2b7485206e68"),
     *                         @OA\Property(property="vendor_uuid", type="string", format="uuid", example="64cc5e61-c98f-41f7-997c-2b4fbedbf4dc"),
     *                         @OA\Property(property="account_status", type="boolean", example=true),
     *                         @OA\Property(property="email_verified", type="boolean", example=true),
     *                         @OA\Property(property="name", type="string", example="vendor30956945"),
     *                         @OA\Property(property="email", type="string", format="email", example="vendor30956945@gmail.com"),
     *                         @OA\Property(property="mobile_number", type="string", example="9808096921"),
     *                         @OA\Property(property="store_name", type="string", example="Oberbrunner PLC")
     *                     )
     *                 ),
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=30),
     *                 @OA\Property(property="total_items", type="integer", example=30)
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $per_page = $request->per_page;
        $search = $request->query('search', null);
        $verified_vendor = $request->query('verified_vendors', 'All');

        $pagination = User::filterByRole(UserTypeEnum::VENDOR)
            ->has('vendor')
            ->with('vendor')
            // apply search only when provided; group the ORs so they don't break other filters
            ->when($search, function ($q, $search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            // apply verified filter only when not "All"
            ->when($verified_vendor !== 'All', function ($q) use ($verified_vendor) {
                if ((string)$verified_vendor === '1') {
                    $q->whereHas('vendor', fn($q2) => $q2->where('status', true));
                } else { // assume 0
                    $q->whereHas('vendor', fn($q2) => $q2->where('status',false));
                }
            })
            ->latest()
            ->paginate($per_page);

        // $items = $pagination->items();
        // return new AdminVendorUserList($items);
        $data = $this->makePaginationResponse($pagination, fn($items) => AdminVendorUserList::collection($items))->data;
        $stat = $verified_vendor == 'All' ? 'All' : ($verified_vendor == 1 ? 'Verified' : 'Unverified');
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
     *                 required={"store_name","store_description","location","country","state","district","municipality","postal_code","bank_name","bank_account_holder_name","bank_account_number","account_status","vendor_citizenship_card","vendor_business_license","vendor_tax_certificate","name","email","mobile_number"},
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
     *                 @OA\Property(property="account_status", type="boolean", example=true),
     *                 @OA\Property(
     *                     property="vendor_citizenship_card",
     *                     type="string",
     *                     format="binary",
     *                     description="Multiple image files to upload vendor citizenship card"
     *                 ),
     *                 @OA\Property(
     *                     property="vendor_business_license",
     *                     type="string",
     *                     format="binary",
     *                     description="Multiple image files to upload vendor business license"
     *                 ),
     *                 @OA\Property(
     *                     property="vendor_tax_certificate",
     *                     type="string",
     *                     format="binary",
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
     *         description="User(vendor) uuid",
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
     *                     @OA\Property(property="email_verified", type="boolean", example=true),
     *                     @OA\Property(property="status", type="boolean", example=true),
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
    public function show(User $user)
    {
        $user->loadMissing(['vendor' => ['media']]);
        $user = new AdminVendorUserResource($user);
        return $this->apiSuccess('Vendor user detail', $user);
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/admin/vendor/{uuid}",
     *     summary="Update a vendor by admin",
     *     description="Update information of a vendor.",
     *     operationId="UpdateVendor",
     *     tags={"Vendor"},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="User(vendor) uuid",
     *         @OA\Schema(type="string", example="c80dbce7-a3b5-476f-a618-4f59d4c8bdae")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"_method","store_name","store_description","location","country","state","district","municipality","postal_code","bank_name","bank_account_holder_name","bank_account_number","account_status","name","mobile_number"},
     *                 @OA\Property(property="_method", type="string", example="patch"),
     *                 @OA\Property(property="name", type="string", example="Dave Chappelle"),
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
     *                 @OA\Property(property="account_status", type="boolean", example=true),
     *                 @OA\Property(property="vendor_citizenship_card", type="string", format="binary", description="Multiple image files to upload vendor citizenship card"),
     *                 @OA\Property(property="vendor_business_license", type="string", format="binary", description="Multiple image files to upload vendor business license"),
     *                 @OA\Property(property="vendor_tax_certificate", type="string", format="binary", description="Multiple image files to upload vendor tax certificate")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Vendor updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Vendor updated successfully."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */

    public function update(VendorStoreRequest $request, User $user)
    {
        DB::transaction(function () use ($request, $user) {
            $user_data = $request->safe()->only(["name", "email", "mobile_number"]);
            $vendor_data = $request->safe()->except(["name", "email", "mobile_number", "vendor_citizenship_card", "vendor_business_license", "vendor_tax_certificate","account_status"]);
            $vendor_data['verified_at'] = $request->account_status == 1 ? now() : null;
            $user_data['status'] = $request->account_status == 1 ? true : false;
            // return $vendor_data;
            // Log::info($vendor_data);
            tap($user, fn() =>$user->update($user_data))->vendor()->update($vendor_data);
            if ($request->hasFile('vendor_citizenship_card')) {
                $user->vendor->addMedia($request->file('vendor_citizenship_card'))->toMediaCollection(VendorContants::VENDOR_BUSINESS_LICENSE);
            }
            if ($request->file('vendor_business_license')) {
                $user->vendor->addMedia($request->file('vendor_business_license'))->toMediaCollection(VendorContants::VENDOR_CITIZENSHIP_CARD);
            }
            if ($request->file('vendor_tax_certificate')) {
                $user->vendor->addMedia($request->file('vendor_tax_certificate'))->toMediaCollection(VendorContants::VENDOR_TAX_CERTIFICATE);
            }
        });
        return $this->apiSuccess('Vendor updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * @OA\Delete(
     *     security={{"sanctum": {}}}, 
     *     path="/admin/vendor/{uuid}",
     *     operationId="VendorDelete",
     *     tags={"Vendor"},
     *     summary="Delete a vendor(soft).",
     *     description="Delete a vendor(soft).",
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the user(vendor) to delete",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Brand successfully deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="null", example=null),
     *             @OA\Property(property="message", type="string", example="Vendor removed succesfully.")
     *         )
     *     )
     * )
    */
    public function destroy(User $user)
    {
        $user->vendor()->delete();
        return $this->apiSuccess('Vendor removed succesfully.');
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
     *         description="Uuid of a user(vendor)",
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
    function toggleVendorVerifiedStatus(User $user){
        $vendor = $user->vendor;
        $current_verification_status = $vendor->verified_at != null;
        $message = 'Vendor verification status changed to ACTIVE';
        if ((int)$current_verification_status == 1) {
            $message = 'Vendor verification status changed to INACTIVE';
        }
        DB::transaction(function () use($vendor, $current_verification_status){
            $vendor->user->update([
                'status' => !(bool)$current_verification_status
            ]);
            $vendor->update([
                'verified_at' => !(bool)$current_verification_status ? now() : null
            ]);
        });
        return $this->apiSuccess($message);
    }

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/fetch-vendor-products/{uuid}",
     *     summary="Vendors product list",
     *     description="Get vendor product list.",
     *     operationId="VendorProductList",
     *     tags={"Vendor"},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of user(vendor)",
     *         @OA\Schema(type="string", example="0eebfe00-8bfd-4957-9947-503731e37a33")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Items per page",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vendor product lists",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Vendor product lists"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="vendor_status", type="boolean", example=true),
     *                         @OA\Property(property="approved", type="boolean", example=true),
     *                         @OA\Property(property="product_name", type="string", example="Soluta aut voluptas voluptatem beatae."),
     *                         @OA\Property(
     *                             property="product_variants",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="price", type="number", format="float", example=4335),
     *                                 @OA\Property(property="units_in_stock", type="integer", example=69),
     *                                 @OA\Property(property="variation_name", type="string", example="Variant-2"),
     *                                 @OA\Property(property="variation_size_value", type="integer", example=200),
     *                                 @OA\Property(property="variation_size_unit", type="string", example="capsule")
     *                             )
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=1),
     *                 @OA\Property(property="total_items", type="integer", example=1)
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
    */
    function getVendorProduct(Request $request, User $user) {
        $per_page = $request->query('per_page');
        $pagination = $user->vendor->vendorProducts()->with(['product', 'vendorPrices.variation'])->paginate($per_page);
        $data = $this->makePaginationResponse($pagination, fn($item) => AdminVendorProductListResource::collection($item))->data;
        return $this->apiSuccess('Vendor product lists', $data);
    }
}
