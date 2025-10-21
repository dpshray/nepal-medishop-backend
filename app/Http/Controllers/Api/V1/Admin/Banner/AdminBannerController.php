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
     *         description="List of banners.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="List of banners."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="object",
     *                     @OA\Property(
     *                         property="items",
     *                         type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="uuid", type="string", example="8fb81300-fe6b-43b6-9ecb-d20e76290043"),
     *                             @OA\Property(property="display_status", type="boolean", example=true),
     *                             @OA\Property(property="order", type="integer", example=1),
     *                             @OA\Property(property="title", type="string", nullable=true, example="Look, a banner!"),
     *                             @OA\Property(property="url", type="string", nullable=true, example="https://inboxes.com/"),
     *                             @OA\Property(property="image", type="string", format="url", example="http://192.168.100.23:8008/storage/2643/sunset-7007680_1920.jpg")
     *                         )
     *                     ),
     *                     @OA\Property(property="page", type="integer", example=1),
     *                     @OA\Property(property="total_page", type="integer", example=1),
     *                     @OA\Property(property="total_items", type="integer", example=3)
     *                 )
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
     *     @OA\Response(
     *         response=200,
     *         description="Banner added successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="banner added successfully"),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
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
    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/admin/banner/{uuid}",
     *     summary="Update banner based on UUID",
     *     description="Update a banner by its UUID.",
     *     operationId="BannerUpdate",
     *     tags={"Banner"},
     *
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the banner to update",
     *         @OA\Schema(type="string", example="8fb81300-fe6b-43b6-9ecb-d20e76290043")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"_method"},
     *                 @OA\Property(
     *                     property="_method",
     *                     type="string",
     *                     example="PATCH",
     *                     description="Used when the route expects PATCH method but form submits as POST."
     *                 ),
     *                 @OA\Property(
     *                     property="order",
     *                     type="integer",
     *                     example=1,
     *                     description="Display order of the banner."
     *                 ),
     *                 @OA\Property(
     *                     property="title",
     *                     type="string",
     *                     example="Updated banner title.",
     *                     description="Title of the banner."
     *                 ),
     *                 @OA\Property(
     *                     property="url",
     *                     type="string",
     *                     example="https://www.youtube.com/",
     *                     description="Clickable URL of the banner."
     *                 ),
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary",
     *                     description="Image file for the banner."
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Banner updated successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Banner updated successfully."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */

    public function update(AdminBannerRequest $request, Banner $banner)
    {
        // return $request->validated();
        DB::transaction(function () use($request, $banner) {
            $data = $request->validated();
            $banner->update($data);
            if ($request->hasFile('image')) {                
                $banner->addMedia($request->image)
                    ->toMediaCollection(Banner::BANNER_MEDIA);
            }
        });
        return $this->apiSuccess('Banner has been updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * @OA\Delete(
     *     security={{"sanctum": {}}}, 
     *     path="/admin/banner/{uuid}",
     *     operationId="BannerDelete",
     *     tags={"Banner"},
     *     summary="Delete a banner.",
     *     description="Delete a banner.",
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the banner to delete",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Banner has been deleted.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Banner has been deleted."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function destroy(Banner $banner)
    {
        $banner->delete();
        return $this->apiSuccess('Banner has been deleted.');
    }

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/toggle-banner-status/{uuid}",
     *     summary="Toggle banner visibility status",
     *     description="Toggle banner visibility status.",
     *     operationId="BannerVisibilityToggle",
     *     tags={"Banner"},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of banner",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Brand status changed successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Banner status changed to ACTIVE"),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function visibilityToggler(Banner $banner)
    {
        $current_status = (int)$banner->display_status;
        $message = 'Banner status changed to ACTIVE';
        if ($current_status == 1) {
            $message = 'Banner status changed to INACTIVE';
        }
        $banner->update([
            'display_status' => !$current_status
        ]);
        return $this->apiSuccess($message);
    }
}
