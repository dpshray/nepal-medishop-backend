<?php

namespace App\Http\Controllers\Api\V1\Admin\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BrandStoreRequest;
use App\Http\Resources\Admin\AdminBrandResource;
use App\Models\Brand;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminBrandController extends Controller
{
    use ResponseTrait, PaginationTrait;

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/brand",
     *     summary="Get all active/inactive brand",
     *     description="Get all active/inactive brand.",
     *     operationId="BrandList",
     *     tags={"Brand"},
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
     *         description="Show active/inactive brands(values: 0 and 1)",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Active brand lists",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Active brand lists"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="slug", type="string", example="pfizer"),
     *                         @OA\Property(property="name", type="string", example="Pfizer"),
     *                         @OA\Property(
     *                             property="image",
     *                             type="string",
     *                             format="url",
     *                             example="http://127.0.0.1:8000/assets/img/default-brand-category.png"
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=1),
     *                 @OA\Property(property="total_items", type="integer", example=6),
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $per_page = $request->per_page;
        $status = $request->query('status',1) == 1 ? 1 : 0;
        $pagination = Brand::with(['media'])
            ->where('status', $status)
            ->paginate($per_page);
        $data = $this->makePaginationResponse($pagination, fn($items) => AdminBrandResource::collection($items))->data;
        $msg = $status == 1 ? 'Active' : 'Inactive';
        return $this->apiSuccess("$msg brand lists", $data);
    }

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/brand/{slug}",
     *     summary="Show an active brand",
     *     description="Show an active brand.",
     *     operationId="BrandShow",
     *     tags={"Brand"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug of brand",
     *         @OA\Schema(type="string", example="sun-pharma")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Showing brand",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Showing brand"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=3),
     *                 @OA\Property(property="slug", type="string", example="sun-pharma"),
     *                 @OA\Property(property="name", type="string", example="Sun Pharma"),
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="url",
     *                     example="http://127.0.0.1:8000/assets/img/default-brand-category.png"
     *                 )
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function show($slug){
        $brand = Brand::with('media')->firstWhere('slug',$slug);
        return $this->apiSuccess('Showing brand', new AdminBrandResource($brand));
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/admin/brand",
     *     summary="Store a product brand",
     *     description="Store a product brand.",
     *     operationId="StoreBrand",
     *     tags={"Brand"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "image"},
     *                 @OA\Property(property="name", type="string", example="Merck"),
     *                 @OA\Property(
     *                     property="image",
     *                     type="file",
     *                     format="binary",
     *                     description="Brand image"
     *                 ),
     *                 @OA\Property(
     *                     property="is_featured",
     *                     type="boolean",
     *                     example=true,
     *                     description="Indicates if the brand is featured"
     *                 ),
     *                 @OA\Property(
     *                     property="is_popular",
     *                     type="boolean",
     *                     example=false,
     *                     description="Indicates if the brand is popular"
     *                 )
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vendor added successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Brand added successfully."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     * )
     */
    public function store(BrandStoreRequest $request)
    {
        DB::transaction(function () use($request){
            Brand::create($request->validated())
                ->addMedia($request->image)
                ->toMediaCollection(Brand::BRAND_IMAGE);
        });

        return $this->apiSuccess('Brand added successfully.');
    }

    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/admin/brand/{brand}",
     *     summary="Update brand based on ID",
     *     description="Update brand based on ID",
     *     operationId="BrandUpdate",
     *     tags={"Brand"},
     *     @OA\Parameter(
     *         name="brand",
     *         in="path",
     *         required=true,
     *         description="Brand ID of a brand",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name","_method"},
     *                 @OA\Property(property="name", type="string", example="Merck"),
     *                 @OA\Property(property="_method", type="string", example="patch"),
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary",
     *                     description="Brand image"
     *                 ),
     *                 @OA\Property(
     *                     property="is_featured",
     *                     type="boolean",
     *                     example=true,
     *                     description="Indicates if the brand is featured"
     *                 ),
     *                 @OA\Property(
     *                     property="is_popular",
     *                     type="boolean",
     *                     example=false,
     *                     description="Indicates if the brand is popular"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vendor added successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Brand updated successfully."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     *   )
     * )
     */
    public function update(BrandStoreRequest $request, Brand $brand)
    {
        $brand->update($request->validated());
        if ($request->hasFile('image')) {
            $brand->addMedia($request->image)->toMediaCollection(Brand::BRAND_IMAGE);
        }
        return $this->apiSuccess('Brand updated successfully.');
    }

    /**
     * @OA\Delete(
     *     security={{"sanctum": {}}}, 
     *     path="/admin/brand/{brand}",
     *     operationId="BrandDelete",
     *     tags={"Brand"},
     *     summary="Delete a brand(soft).",
     *     description="Delete a brand(soft).",
     *     @OA\Parameter(
     *         name="brand",
     *         in="path",
     *         required=true,
     *         description="ID of the brand to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Brand successfully deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="null", example=null),
     *             @OA\Property(property="message", type="string", example="Brand removed successfully.")
     *         )
     *     )
     * )
    */
    public function destroy(Brand $brand)
    {
        $brand->delete();
        return $this->apiSuccess('Brand removed successfully.');
    }

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/toggle-brand-status/{brand}",
     *     summary="Toggle brand status",
     *     description="Toggle brand status.",
     *     operationId="BrandStatusToggle",
     *     tags={"Brand"},
     *     @OA\Parameter(
     *         name="brand",
     *         in="path",
     *         required=true,
     *         description="Slug of brand",
     *         @OA\Schema(type="string", example="sunovion")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Brand status changed successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Brand status changed to ACTIVE"),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function statusToggler(Brand $brand){
        $current_status = (int)$brand->status;
        $message = 'Brand status changed to ACTIVE';
        if ($current_status == 1) {
            $message = 'Brand status changed to INACTIVE';
        }
        $brand->update([
            'status' => !$current_status
        ]);
        return $this->apiSuccess($message);
    }
}
