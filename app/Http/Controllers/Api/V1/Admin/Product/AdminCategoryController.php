<?php

namespace App\Http\Controllers\Api\V1\Admin\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryStoreRequest;
use App\Http\Resources\Admin\AdminCategoryResource;
use App\Models\Category;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class AdminCategoryController extends Controller
{
    use ResponseTrait, PaginationTrait;

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/category",
     *     summary="Get all active category",
     *     description="Get all active category.",
     *     operationId="CategoryList",
     *     tags={"Category"},
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
     *     @OA\Response(
     *         response=200,
     *         description="Active category lists",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Active category lists"),
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
     *                         @OA\Property(property="name", type="string", example="Pfizer")
     *                     )
     *                 ),
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=3),
     *                 @OA\Property(property="total_items", type="integer", example=5)
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $per_page = $request->per_page;
        $pagination = Category::paginate($per_page);
        $data = $this->makePaginationResponse($pagination, fn($items) => new AdminCategoryResource($items))->data;
        return $this->apiSuccess('Active category lists', $data);
    }

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/category/{slug}",
     *     summary="Show an active category",
     *     description="Show an active category.",
     *     operationId="CategoryShow",
     *     tags={"Category"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug of category",
     *         @OA\Schema(type="string", example="skin-care")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Showing category",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Showing category"),
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
        $category = Category::firstWhere('slug', $slug);
        return $this->apiSuccess('Showing category', new AdminCategoryResource($category));
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/admin/category",
     *     summary="Store a product category",
     *     description="Store a product category.",
     *     operationId="StoreCategory",
     *     tags={"Category"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Neurology"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category create response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Category added successfully."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     * )
     */
    public function store(CategoryStoreRequest $request)
    {
        Category::create($request->validated());
        return $this->apiSuccess('Category added successfully.');
    }

    /**
     * @OA\Patch(
     *     security={{"sanctum": {}}},
     *     path="/admin/category/{category}",
     *     summary="Update category based on ID",
     *     description="Update category based on ID",
     *     operationId="CategoryUpdate",
     *     tags={"Category"},
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         required=true,
     *         description="An infant id of the belonging user",
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
     *         description="Category update response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Category updated successfully."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     *   )
     * )
     */
    public function update(CategoryStoreRequest $request, Category $category)
    {
        $category->update($request->validated());
        return $this->apiSuccess('Category updated successfully.');
    }

    /**
     * @OA\Delete(
     *     security={{"sanctum": {}}}, 
     *     path="/admin/category/{category}",
     *     operationId="CategoryDelete",
     *     tags={"Category"},
     *     summary="Delete a category(soft).",
     *     description="Delete a category(soft).",
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         required=true,
     *         description="ID of the category to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category successfully deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="null", example=null),
     *             @OA\Property(property="message", type="string", example="Category removed successfully.")
     *         )
     *     )
     * )
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return $this->apiSuccess('Category removed successfully.');
    }
}
