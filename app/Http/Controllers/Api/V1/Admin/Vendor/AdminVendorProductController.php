<?php

namespace App\Http\Controllers\Api\V1\Admin\Vendor;

use App\Enums\UserTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Vendor\VendorProductPriceDetailResource;
use App\Http\Resources\Admin\Vendor\VendorProductPriceListResource;
use App\Models\ProductVendor;
use App\Models\User;
use App\Models\VendorProductPrice;
use App\Notifications\AdminVendorProductStatusUpdateNotification;
use App\Notifications\VendorProductStatusUpdateNotification;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminVendorProductController extends Controller
{
    //
    use ResponseTrait, PaginationTrait;
    /**
     * @OA\Get(
     *     path="/admin/vendorproductlist",
     *     summary="Get paginated list of vendor products",
     *     description="Retrieve a paginated list of vendor products including variations and vendor info. Supports optional search by product name.",
     *     tags={"Vendor Products list"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for product name",
     *         required=false,
     *         @OA\Schema(type="string", example="Soap")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Vendor products retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Vendor products retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="status", type="boolean", example=true),
     *                     @OA\Property(
     *                         property="vendor",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=5),
     *                         @OA\Property(property="name", type="string", example="Vendor Name")
     *                     ),
     *                     @OA\Property(
     *                         property="product_variation",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=12),
     *                         @OA\Property(property="name", type="string", example="Variation Name"),
     *                         @OA\Property(property="product_name", type="string", example="Product Name"),
     *                         @OA\Property(property="size_value", type="number", format="float", example=250.0),
     *                         @OA\Property(property="size_unit", type="string", example="ml")
     *                     ),
     *                     @OA\Property(property="price", type="number", format="float", example=499.99),
     *                     @OA\Property(property="units_in_stock", type="integer", example=20)
     *                 )
     *             ),
     *             @OA\Property(property="pagination", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=150),
     *                 @OA\Property(property="last_page", type="integer", example=15)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */

    public function vendorProductList(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->query('search', null);

        $query = ProductVendor::with(['vendor', 'vendorPrices','product'])
            ->has('product'); #if product is deleted

        if ($search) {
            $query->whereRelation('product','name', 'like', "%{$search}%");
        }

        $paginated = $query->orderBy('id', 'desc')->paginate($perPage);

        $data = $this->makePaginationResponse($paginated, fn($items) => VendorProductPriceListResource::collection($items))->data;

        return $this->apiSuccess('Vendor products retrieved successfully.', $data);
    }
    /**
     * @OA\Patch(
     *     path="/admin/vendor-product-prices/{uuid}/approve",
     *     summary="Approve or disapprove a vendor product",
     *     description="This endpoint updates the approval status (is_approved) of a vendor product in the VendorProductPrice table.",
     *     tags={"Vendor Products list"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the vendor product price entry",
     *         @OA\Schema(type="string", example="")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"is_approved"},
     *             @OA\Property(
     *                 property="is_approved",
     *                 type="boolean",
     *                 example=true,
     *                 description="Approval status: true = approved, false = disapproved"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Vendor product approval status updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Vendor product approved successfully.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Vendor product not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Vendor product not found.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The is_approved field is required.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */

    public function approveVendorProduct(Request $request, $uuid)
    {
        $validated = $request->validate([
            'is_approved' => 'required|boolean',
        ]);

        DB::transaction(function () use($validated,$uuid){            
            $product_vendor = ProductVendor::with(['product','vendorPrices'])
                ->where('uuid', $uuid)->firstOrFail();
            $product_vendor->update(['is_approved' => $validated['is_approved']]);
            $product_vendor->vendor->user->notify(new VendorProductStatusUpdateNotification($product_vendor));
        });


        return $this->apiSuccess(
            $validated['is_approved']
                ? 'Vendor product approved successfully.'
                : 'Vendor product disapproved successfully.'
        );
    }
    /**
     * @OA\Delete(
     *     path="/admin/vendor-product-prices/{uuid}",
     *     summary="Delete a vendor product",
     *     description="Deletes a vendor product by its UUID.",
     *     tags={"Vendor Products list"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="UUid",
     *         in="path",
     *         required=true,
     *         description="UUID of the vendor product to delete",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Vendor product deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Vendor product deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Vendor product not found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */

    public function deleteVendorProduct($uuid)
    {
        $vendorProduct = ProductVendor::where('uuid', $uuid)->firstOrFail();
        $vendorProduct->delete();
        return $this->apiSuccess('Vendor product deleted successfully.');
    }
    /**
     * @OA\Get(
     *     path="/admin/vendor-product-prices-detail/{uuid}",
     *     summary="Get vendor product detail",
     *     description="Retrieve detailed information about a specific vendor product, including variation and vendor details.",
     *     tags={"Vendor Products list"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the vendor product",
     *         @OA\Schema(type="string", example="98ea8896-e570-4101-a0ca-358157795a0a")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Vendor product detail retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Vendor product detail retrieved successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="status", type="boolean", example=true),
     *                 @OA\Property(property="is_approved", type="boolean", example=true),
     *                 @OA\Property(property="price", type="number", format="float", example=1200.50),
     *                 @OA\Property(property="units_in_stock", type="integer", example=25),
     *                 @OA\Property(property="vendor", type="object",
     *                     @OA\Property(property="id", type="integer", example=3),
     *                     @OA\Property(property="name", type="string", example="ABC Traders")
     *                 ),
     *                 @OA\Property(property="product_variation", type="object",
     *                     @OA\Property(property="id", type="integer", example=5),
     *                     @OA\Property(property="name", type="string", example="500ml Bottle"),
     *                     @OA\Property(property="product_name", type="string", example="Mineral Water"),
     *                     @OA\Property(property="size_value", type="number", example=500),
     *                     @OA\Property(property="size_unit", type="string", example="ml")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Vendor product not found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function detail($uuid)
    {
        $vendorProduct = ProductVendor::with(['product.media', 'vendor.user', 'vendorPrices.variation'])->where('uuid',$uuid)->firstOrFail();
        $data = new VendorProductPriceDetailResource($vendorProduct);
        return $this->apiSuccess('Vendor product detail retrieved successfully.', $data);
    }
}
