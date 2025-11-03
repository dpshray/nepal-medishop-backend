<?php

namespace App\Http\Controllers\Api\V1\Admin\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TagStoreRequest;
use App\Http\Resources\Admin\AdminTagResource;
use App\Models\Tag;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class AdminTagController extends Controller
{
    use ResponseTrait, PaginationTrait;

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/tag",
     *     summary="Get all active/inactive tag",
     *     description="Get all active/inactive tag.",
     *     operationId="TagList",
     *     tags={"Tag"},
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
     *         description="Items on each page.(empty to fetch all data)",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         description="Toggle active/inactive tags(values: 0 and 1)",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Tag name to search",
     *         @OA\Schema(type="string", example="aspirin")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Active/Inactive tag list",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Active tag lists"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=8),
     *                         @OA\Property(property="slug", type="string", example="vitamin-c"),
     *                         @OA\Property(property="name", type="string", example="Vitamin C")
     *                     )
     *                 ),
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=4),
     *                 @OA\Property(property="total_items", type="integer", example=4)
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $per_page = $request->query('per_page', Tag::count());
        $search = $request->query('search');
        $status = $request->query('status', 1) == 1 ? 1 : 0;
        $pagination = Tag::where('status', $status)
            ->when($search, fn($qry) => $qry->whereLike('name', '%'.$search.'%'))
            ->orderBy('id','DESC')
            ->paginate($per_page);
        $data = $this->makePaginationResponse($pagination, fn($item) => AdminTagResource::collection($item))->data;
        $msg = $status == 1 ? 'Active' : 'Inactive';
        return $this->apiSuccess("$msg tag lists", $data);
    }

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/tag/{tag}",
     *     summary="Show an active tag",
     *     description="Show an active tag.",
     *     operationId="TagShow",
     *     tags={"Tag"},
     *     @OA\Parameter(
     *         name="tag",
     *         in="path",
     *         required=true,
     *         description="Slug of tag",
     *         @OA\Schema(type="string", example="omega-3")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Showing tag",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Showing tag"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=3),
     *                 @OA\Property(property="slug", type="string", example="sun-pharma"),
     *                 @OA\Property(property="name", type="string", example="Sun Pharma")
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function show($slug)
    {
        $tag = Tag::firstWhere('slug', $slug);
        return $this->apiSuccess('Showing tag', new AdminTagResource($tag));
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/admin/tag",
     *     summary="Store a product tag",
     *     description="Store a product tag.",
     *     operationId="StoreTag",
     *     tags={"Tag"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Broad Spectrum"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag create response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Tag added successfully."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     * )
     */
    public function store(TagStoreRequest $request)
    {
        Tag::create($request->validated());
        return $this->apiSuccess('Tag added successfully.');
    }

    /**
     * @OA\Patch(
     *     security={{"sanctum": {}}},
     *     path="/admin/tag/{tag}",
     *     summary="Update tag based on ID",
     *     description="Update tag based on ID",
     *     operationId="TagUpdate",
     *     tags={"Tag"},
     *     @OA\Parameter(
     *         name="tag",
     *         in="path",
     *         required=true,
     *         description="ID of a tag",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name"},
     *                 @OA\Property(property="name", type="string", example="Anesthesiology")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag update response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Tag updated successfully."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     *   )
     * )
     */
    public function update(TagStoreRequest $request, Tag $tag)
    {
        $tag->update($request->validated());
        return $this->apiSuccess('Tag updated successfully.');
    }

    /**
     * @OA\Delete(
     *     security={{"sanctum": {}}}, 
     *     path="/admin/tag/{tag}",
     *     operationId="TagDelete",
     *     tags={"Tag"},
     *     summary="Delete a tag(soft).",
     *     description="Delete a tag(soft).",
     *     @OA\Parameter(
     *         name="tag",
     *         in="path",
     *         required=true,
     *         description="ID of the tag to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag successfully deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="null", example=null),
     *             @OA\Property(property="message", type="string", example="Tag removed successfully.")
     *         )
     *     )
     * )
     */
    public function destroy(Tag $tag)
    {
        $tag->delete();
        return $this->apiSuccess('Tag removed successfully.');
    }

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/toggle-tag-status/{tag}",
     *     summary="Toggle tag status",
     *     description="Toggle tag status.",
     *     operationId="TagStatusToggle",
     *     tags={"Tag"},
     *     @OA\Parameter(
     *         name="tag",
     *         in="path",
     *         required=true,
     *         description="Slug of tag",
     *         @OA\Schema(type="string", example="sunovion")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag status changed successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Tag status changed to ACTIVE"),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function statusToggler(Tag $tag)
    {
        $current_status = (int)$tag->status;
        $message = 'Tag status changed to ACTIVE';
        if ($current_status == 1) {
            $message = 'Tag status changed to INACTIVE';
        }
        $tag->update([
            'status' => !$current_status
        ]);
        return $this->apiSuccess($message);
    }
}
