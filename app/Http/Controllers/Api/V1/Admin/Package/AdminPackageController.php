<?php

namespace App\Http\Controllers\Api\V1\Admin\Package;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Package\PackageStoreRequest;
use App\Http\Requests\Admin\Package\PackageUpdateRequest;
use App\Http\Requests\Admin\Package\StoreProductToPackageRequest;
use App\Http\Resources\Admin\Package\AdminPackageDetailResource;
use App\Http\Resources\Admin\Package\AdminPackageResource;
use App\Models\Package;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class AdminPackageController extends Controller
{
    //
    use ResponseTrait, PaginationTrait;
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/package",
     *     summary="Get all active/inactive package",
     *     description="Get all active/inactive package.",
     *     operationId="packageList",
     *     tags={"Package"},
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
     *         name="status",
     *         in="query",
     *         required=false,
     *         description="Toggle active/inactive brands(values: 0, 1, blank)",
     *         @OA\Schema(type="integer", example="1")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Search product based on name",
     *         @OA\Schema(type="string", example="benedril")
     *     ),
     *      @OA\Response(
     *         response=200,
     *         description="Admin published product list.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Admin published product list."),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="Package_name", type="string", example="Super package"),
     *                         @OA\Property(property="slug", type="string", example="super-package"),
     *                         @OA\Property(property="description", type="string", example="Sure Grow Procapil Scalp Solution - Hair Serum - 60ml"),
     *                         @OA\Property(property="price", type="number", example=5000),
     *                         @OA\Property(property="discount_percent", type="number", format="float", example=10),
     *                         @OA\Property(property="rating", type="number", example=5)
     *                     )
     *                 ),
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=103),
     *                 @OA\Property(property="total_items", type="integer", example=103)
     *             )
     *         )
     *     )
     * )
     */
    function index(Request $request)
    {
        $per_page = $request->per_page;
        $search = $request->query('search', null);
        $status = $request->query('status', null);
        $msg = 'All';
        if ($status != null) {
            if ($status == 1) {
                $msg = 'published';
            } else {
                $msg = 'unpublished';
            }
        }
        $pagination = Package::when($status != null, fn($qry) => $qry->where('status', $status))
            ->when($search != null, fn($qry) => $qry->whereLike('name', '%' . $search . '%'))
            ->latest('id')
            ->paginate($per_page);
        $product = $this->makePaginationResponse($pagination, fn($item) => AdminPackageResource::collection($item))->data;
        return $this->apiSuccess("Admin $msg product list.", $product);
    }

    /**
     * @OA\Post(
     *     path="/admin/package",
     *     summary="Create a new package",
     *     description="Create a new package with name, description, pricing, and optional images.",
     *     tags={"Package"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "description", "price", "status", "featured_image"},
     *
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     example="Summer Deal Package"
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string",
     *                     example="A special summer package with discounted products."
     *                 ),
     *                 @OA\Property(
     *                     property="price",
     *                     type="number",
     *                     format="float",
     *                     example=4999.99
     *                 ),
     *                 @OA\Property(
     *                     property="discount_percent",
     *                     type="number",
     *                     format="float",
     *                     nullable=true,
     *                     example=10.0,
     *                     description="Optional discount percentage"
     *                 ),
     *                 @OA\Property(
     *                     property="status",
     *                     type="boolean",
     *                     example=true,
     *                     description="Package status (true = active, false = inactive)"
     *                 ),
     *
     *                 @OA\Property(
     *                     property="featured_image",
     *                     type="string",
     *                     format="binary",
     *                     description="Required featured image of the package"
     *                 ),
     *
     *                 @OA\Property(
     *                     property="gallery_images",
     *                     type="array",
     *                     @OA\Items(type="string", format="binary"),
     *                     nullable=true,
     *                     description="Optional array of gallery images"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Package created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Package added successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */

    function store(PackageStoreRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request) {
            // Create package
            $package = Package::create([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'description' => $data['description'],
                'price' => $data['price'],
                'discount_percent' => $data['discount_percent'] ?? 0,
                'status' => $data['status'],
            ]);
            // Add featured image
            if ($request->hasFile('featured_image')) {
                $package->addMedia($request->file('featured_image'))
                    ->toMediaCollection(Package::PACKAGE_FEATURED);
            }
            // Add gallery images
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $image) {
                    $package->addMedia($image)->toMediaCollection(Package::PACKAGE_GALLERY);
                }
            }
        });
        return $this->apiSuccess('Package added successfully.');
    }
    /**
     * @OA\Post(
     *     path="/admin/package/{slug}/add-product",
     *     summary="Add products to a package",
     *     description="Attach one or more product variations to an existing package. The package is identified by its slug.",
     *     tags={"Package"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug of the package to which products will be added",
     *         @OA\Schema(type="string", example="summer-deal-package")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"products"},
     *             @OA\Property(
     *                 property="products",
     *                 type="array",
     *                 description="List of products to attach to the package",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"product_variation_id", "quantity"},
     *                     @OA\Property(
     *                         property="product_variation_id",
     *                         type="integer",
     *                         example=12,
     *                         description="The ID of the product variation"
     *                     ),
     *                     @OA\Property(
     *                         property="quantity",
     *                         type="integer",
     *                         example=3,
     *                         description="Quantity of this product in the package"
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Products added to package successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Products added successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Package not found"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    function add_product_to_package($slug, StoreProductToPackageRequest $request)
    {
        $package = Package::where('slug', $slug)->first();
        //Attach related products to package
        $data = $request->validated();
        $package->products()->attach(
            collect($data['products'])->mapWithKeys(fn($item) => [
                $item['product_variation_id'] => ['quantity' => $item['quantity']]
            ])
        );
        return $this->apiSuccess('Product added successfully.');
    }
    /**
     * @OA\Post(
     *     path="/admin/package/{package}",
     *     summary="Update an existing package",
     *     description="Update a package's details, associated products, and images.",
     *     tags={"Package"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="package",
     *         in="path",
     *         required=true,
     *         description="Slug of the package to update",
     *         @OA\Schema(type="string", example="super-package")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="name", type="string", example="Summer Deal Package Updated"),
     *                 @OA\Property(property="description", type="string", example="Updated description of the package."),
     *                 @OA\Property(property="price", type="number", format="float", example=5999.99),
     *                 @OA\Property(property="discount_percent", type="number", format="float", example=15.0),
     *                 @OA\Property(property="status", type="boolean", example=true),
     *                 @OA\Property(property="_method", type="string", example="PATCH"),
     *                 @OA\Property(
     *                     property="featured_image",
     *                     type="string",
     *                     format="binary",
     *                     description="Optional new featured image to replace the existing one."
     *                 ),
     *
     *                 @OA\Property(
     *                     property="gallery_images[]",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         format="binary"
     *                     ),
     *                     description="Optional replace old gallery images for the package"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Package updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Package updated successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Package not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */

    function update(PackageUpdateRequest $request, Package $package)
    {
        $data = $request->validated();
        DB::transaction(function () use ($data, $request, $package) {
            $package->update([
                'name' => $data['name'] ?? $package->name,
                'slug' => Str::slug($data['name'] ?? $package->name),
                'description' => $data['description'] ?? $package->description,
                'price' => $data['price'] ?? $package->price,
                'discount_percent' => $data['discount_percent'] ?? $package->discount_percent,
                'status' => $data['status'] ?? $package->status,
            ]);
            // Update featured image (replace if new file uploaded)
            if ($request->hasFile('featured_image')) {
                $package->clearMediaCollection(Package::PACKAGE_FEATURED);
                $package->addMedia($request->file('featured_image'))
                    ->toMediaCollection(Package::PACKAGE_FEATURED);
            }

            // Update gallery images (replace all if new files uploaded)
            if ($request->hasFile('gallery_images')) {
                $package->clearMediaCollection(Package::PACKAGE_GALLERY);
                foreach ($request->file('gallery_images') as $image) {
                    $package->addMedia($image)
                        ->toMediaCollection(Package::PACKAGE_GALLERY);
                }
            }
        });
        return $this->apiSuccess('Package update successfully.');
    }
    /**
     * @OA\Post(
     *     path="/admin/package/{slug}/update-product",
     *     summary="update products to a package",
     *     description="Attach one or more product variations to an existing package. The package is identified by its slug.",
     *     tags={"Package"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug of the package to which products will be ",
     *         @OA\Schema(type="string", example="summer-deal-package")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"products"},
     *             @OA\Property(
     *                 property="products",
     *                 type="array",
     *                 description="List of products to attach to the package",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"product_variation_id", "quantity"},
     *                     @OA\Property(
     *                         property="product_variation_id",
     *                         type="integer",
     *                         example=12,
     *                         description="The ID of the product variation"
     *                     ),
     *                     @OA\Property(
     *                         property="quantity",
     *                         type="integer",
     *                         example=3,
     *                         description="Quantity of this product in the package"
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Products added to package successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Products added successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Package not found"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    function update_package_product($slug, StoreProductToPackageRequest $request)
    {
        $data = $request->validated();
        $package = Package::where('slug', $slug)->first();
        $package->products()->sync(
            collect($data['products'])->mapWithKeys(fn($item) => [
                $item['product_variation_id'] => ['quantity' => $item['quantity']]
            ])
        );
        return $this->apiSuccess('Product update successfully.');
    }
    /**
     * @OA\Delete(
     *     path="/admin/package/{package}",
     *     summary="Delete a package",
     *     description="Permanently deletes a package and all its associations.",
     *     tags={"Package"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="package",
     *         in="path",
     *         required=true,
     *         description="Slug or ID of the package to delete",
     *         @OA\Schema(type="string", example="super-package")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Package deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Product removed successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Package not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */

    public function destroy(Package $package)
    {
        $package->delete();
        return $this->apiSuccess('Product removed successfully.');
    }
    /**
     * @OA\Get(
     *     path="/admin/package/{package}",
     *     summary="Get package details",
     *     description="Retrieve details of a single package including associated products, variants, media, categories, and brand.",
     *     tags={"Package"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="package",
     *         in="path",
     *         required=true,
     *         description="slug of the package to retrieve",
     *         @OA\Schema(type="string", example="smart-pack")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Package details retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Package details retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Package details",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Summer Deal Package"),
     *                 @OA\Property(property="description", type="string", example="Includes multiple skincare products at a discounted rate."),
     *                 @OA\Property(property="price", type="number", format="float", example=4999.99),
     *                 @OA\Property(property="discount_percent", type="number", format="float", example=10.5),
     *                 @OA\Property(property="status", type="boolean", example=true),
     *                 @OA\Property(
     *                     property="products",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=12),
     *                         @OA\Property(property="quantity", type="integer", example=3),
     *                         @OA\Property(property="variant_name", type="string", example="Variant A"),
     *                         @OA\Property(property="product_name", type="string", example="Product X"),
     *                         @OA\Property(property="brand", type="string", example="Brand Y"),
     *                         @OA\Property(
     *                             property="categories",
     *                             type="array",
     *                             @OA\Items(type="string", example="Skincare")
     *                         ),
     *                         @OA\Property(
     *                             property="media",
     *                             type="array",
     *                             @OA\Items(type="string", example="http://example.com/storage/product_image.jpg")
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="media",
     *                     type="array",
     *                     @OA\Items(type="string", example="http://example.com/storage/package_image.jpg")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Package not found"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function show(Package $package)
    {
        $package->loadMissing([
            'media',
            'packageProducts.variant.product.media',
            'packageProducts.variant.product.categories',
            'packageProducts.variant.product.brand',
        ]);
        $data = new AdminPackageDetailResource($package);
        return $this->apiSuccess("Package details retrieved successfully.", $data);
    }
}
