<?php

namespace App\Http\Controllers\Api\V1\Admin\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminCategoryMenuRequest;
use App\Http\Requests\Admin\CategoryStoreRequest;
use App\Http\Resources\Admin\AdminCategoryResource;
use App\Http\Resources\Admin\Menu\AdminCategoryMenuListResource;
use App\Models\Category;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminCategoryController extends Controller
{
    use ResponseTrait, PaginationTrait;

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/category",
     *     summary="Get all active/inactive category",
     *     description="Get all active/inactive category.",
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
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         description="Toggle active/inactive categories(values: 0 and 1)",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Category name to search.",
     *         @OA\Schema(type="string", example="Pain Relief")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Active category lists",
     *         @OA\JsonContent(
     *             type="object",
     *             example={
     *                 "message": "Active category lists",
     *                 "data": {
     *                     "items": {
     *                         {
     *                             "id": 5,
     *                             "slug": "skin-care",
     *                             "name": "Skin Care",
     *                             "image": "http://192.168.100.23:8008/assets/img/default-brand-category.png",
     *                             "discount_percent": 1
     *                         }
     *                     },
     *                     "page": 2,
     *                     "total_page": 2,
     *                     "total_items": 15
     *                 },
     *                 "success": true
     *             }
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $per_page = $request->query('per_page', Category::count());
        $search = $request->query('search');
        $status = $request->query('status',1) == 1 ? 1 : 0;
        $pagination = Category::with('media')
            ->where('status', $status)
            ->when($search, fn($qry) => $qry->whereLike('name', '%'.$search.'%'))
            ->orderBy('id', 'DESC')
            ->paginate($per_page);
        $data = $this->makePaginationResponse($pagination, fn($items) => AdminCategoryResource::collection($items))->data;
        $msg = $status == 1 ? 'Active' : 'Inactive';
        return $this->apiSuccess("$msg category lists", $data);
    }

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/category/{slug}",
     *     summary="Show category",
     *     description="Show category.",
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
     *             example={
     *                 "message": "Showing category",
     *                 "data": {
     *                     "id": 5,
     *                     "slug": "skin-care",
     *                     "name": "Skin Care",
     *                     "image": "http://192.168.100.23:8008/assets/img/default-brand-category.png",
     *                     "discount_percent": 1
     *                 },
     *                 "success": true
     *             }
     *         )
     *     )
     * )
     */
    public function show($slug)
    {
        $category = Category::with('media')->firstWhere('slug', $slug);
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
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "image"},
     *                 @OA\Property(property="name", type="string", example="Merck"),
     *                 @OA\Property(property="discount_percent", type="string", example="0"),
     *                 @OA\Property(
     *                     property="image",
     *                     type="file",
     *                     format="binary",
     *                     description="Category image"
     *                 )
     *             )
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
        DB::transaction(function () use($request){
            Category::create($request->validated())
                ->addMedia($request->image)
                ->toMediaCollection(Category::CATEGORY_IMAGE);
        });
        return $this->apiSuccess('Category added successfully.');
    }

    /**
     * @OA\Post(
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
     *         description="Category id of category",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name","_method"},
     *                 @OA\Property(property="name", type="string", example="Merck"),
     *                 @OA\Property(property="discount_percent", type="string", example="0"),
     *                 @OA\Property(property="_method", type="string", example="patch"),
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary",
     *                     description="Brand image"
     *                 )
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
        if ($request->hasFile('image')) {
            $category->addMedia($request->image)->toMediaCollection(Category::CATEGORY_IMAGE);
        }
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

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/toggle-category-status/{category}",
     *     summary="Toggle category status",
     *     description="Toggle category status.",
     *     operationId="CategoryStatusToggle",
     *     tags={"Category"},
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         required=true,
     *         description="Slug of category",
     *         @OA\Schema(type="string", example="kidney-liver-care")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category status changed successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Category status changed to ACTIVE"),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function statusToggler(Category $category)
    {
        $current_status = (int)$category->status;
        $message = 'Category status changed to ACTIVE';
        if ($current_status == 1) {
            $message = 'Category status changed to INACTIVE';
        }
        $category->update([
            'status' => !$current_status
        ]);
        return $this->apiSuccess($message);
    }

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/category-menu-list",
     *     summary="Get all category menu list",
     *     description="Get all category menu list.",
     *     operationId="CategoryMenuList",
     *     tags={"Menu"},
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
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Category name to search.",
     *         @OA\Schema(type="string", example="Pain Relief")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Active category lists",
     *         @OA\JsonContent(
     *             type="object",
     *             example={
     *                 "message": "Active category lists",
     *                 "data": {
     *                     "items": {
     *                         {
     *                             "id": 5,
     *                             "menu_order": 1,
     *                             "slug": "skin-care",
     *                             "name": "Skin Care"
     *                         }
     *                     },
     *                     "page": 2,
     *                     "total_page": 2,
     *                     "total_items": 15
     *                 },
     *                 "success": true
     *             }
     *         )
     *     )
     * )
     */
    function categoryMenuListFetcher(Request $request) {
        $per_page = $request->query('per_page', Category::count());
        $search = $request->query('search');
        $pagination = Category::whereNotNull('menu_order')
            ->where('status', true)
            ->when($search, fn($qry) => $qry->whereLike('name', '%' . $search . '%'))
            ->orderBy('menu_order')
            ->paginate($per_page);
        $data = $this->makePaginationResponse($pagination, fn($items) => AdminCategoryMenuListResource::collection($items))->data;
        return $this->apiSuccess("Menu category lists", $data);
    }
    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/admin/category-menu-manager",
     *     summary="Update menu order",
     *     description="Update the order of categories in the menu",
     *     operationId="updateMenuOrder",
     *     tags={"Menu"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="menu",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"category_id","order"},
     *                     @OA\Property(property="category_id", type="integer", example=1),
     *                     @OA\Property(property="menu_order", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Menu has been ordered successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Menu has been ordered successfully."),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function categoryMenuHandler(AdminCategoryMenuRequest $request)
    {
        $form_data = $request->validated();
    
        DB::transaction(function () use ($form_data) {
            // Reset menu_order only for categories in the request
            $categoryIds = collect($form_data['menu'])->pluck('category_id');
            DB::table('categories')->whereIn('id', $categoryIds)->update(['menu_order' => null]);
    
            // Prepare all update cases in a single query
            $caseStatements = '';
            foreach ($form_data['menu'] as $item) {
                $caseStatements .= "WHEN id = {$item['category_id']} THEN {$item['menu_order']} ";
            }
    
            // Run a single optimized update query
            DB::update("
                UPDATE categories
                SET menu_order = CASE
                    $caseStatements
                END
                WHERE id IN (" . $categoryIds->implode(',') . ")
            ");
        });
    
        return $this->apiSuccess('Menu has been ordered successfully.');
    }
    
}
