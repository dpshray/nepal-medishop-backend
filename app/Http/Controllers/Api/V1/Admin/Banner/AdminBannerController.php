<?php

namespace App\Http\Controllers\Api\V1\Admin\Banner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminBannerRequest;
use App\Http\Resources\Admin\AdminBannerResource;
use App\Models\Banner;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminBannerController extends Controller
{
    use ResponseTrait, PaginationTrait;
    
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/banner",
     *     summary="Get all banners",
     *     description="Get all banners.",
     *     operationId="BannerList",
     *     tags={"Banner"},
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
     *         description="Toggle active/inactive brands(values: 0 and 1)",
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
     *                         @OA\Property(property="image", type="string", format="url", example="http://192.168.100.23:8008/assets/img/default-brand-category.png"),
     *                         @OA\Property(property="is_featured", type="boolean", example=false),
     *                         @OA\Property(property="is_popular", type="boolean", example=true)
     *                     )
     *                 ),
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=26),
     *                 @OA\Property(property="total_items", type="integer", example=26)
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $per_page = $request->query('per_page');
        $pagination = Banner::with('media')
            ->paginate($per_page);
        $data = $this->makePaginationResponse($pagination, fn($item) => AdminBannerResource::collection($item));
        return $this->apiSuccess('List of banners.', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/admin/banner",
     *     summary="Create a new banner",
     *     description="Create a new banner.",
     *     operationId="BannerStore",
     *     tags={"Banner"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"image"},
     *                 @OA\Property(
     *                     property="order",
     *                     type="integer",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="title",
     *                     type="string",
     *                     example="title of a banner."
     *                 ),
     *                 @OA\Property(
     *                     property="url",
     *                     type="string",
     *                     example="https://www.youtube.com/"
     *                 ),
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary",
     *                     description="Required image of the banner."
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
    public function store(AdminBannerRequest $request)
    {
        DB::transaction(function () use($request){
            $data = $request->validated();
            Banner::create($data)
                ->addMedia($request->image)
                ->toMediaCollection(Banner::BANNER_MEDIA);
        });
        return $this->apiSuccess('banner added successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Banner $banner)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Banner $banner)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * @OA\Delete(
     *     security={{"sanctum": {}}}, 
     *     path="/admin/banner/{banner}",
     *     operationId="BannerDelete",
     *     tags={"Banner"},
     *     summary="Delete a banner.",
     *     description="Delete a banner.",
     *     @OA\Parameter(
     *         name="brand",
     *         in="path",
     *         required=true,
     *         description="UUID of the banner to delete",
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
    public function destroy(Banner $banner)
    {
        $banner->delete();
        return $this->apiSuccess('Banner has been deleted.');
    }
}
