<?php

namespace App\Http\Controllers\Api\V1\Admin\Purchase;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Purchase\Kitbag\AdminKitbagDetailResource;
use App\Http\Resources\Admin\Purchase\Kitbag\AdminKitbagListResource;
use App\Models\Kitbag;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class AdminKitbagOrderController extends Controller
{
    use ResponseTrait, PaginationTrait;

    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/kitbag",
     *     summary="Client kitbag list.",
     *     description="Client kitbag list.",
     *     operationId="KitbagList",
     *     tags={"Kitbag"},
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
     *         description="Search vendor based on email",
     *         @OA\Schema(type="string", example="user")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of user kitbags",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="List of user kitbags"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="kitbag_uuid", type="string", format="uuid", example="dba1fe0a-2abd-4958-96e0-545908d88dd4"),
     *                         @OA\Property(property="username", type="string", example="user00"),
     *                         @OA\Property(property="email", type="string", example="user@gmail.com"),
     *                         @OA\Property(property="created_at", type="string", format="date", example="2025/11/04"),
     *                         @OA\Property(property="no_of_kitbag_items", type="integer", example=4)
     *                     )
     *                 ),
     *                 @OA\Property(property="page", type="integer", example=1),
     *                 @OA\Property(property="total_page", type="integer", example=2),
     *                 @OA\Property(property="total_items", type="integer", example=2)
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     * )
     */
    public function index(Request $request)
    {
        $per_page = $request->query('per_page', Kitbag::count());
        $search = $request->query('search');
        $pagination = Kitbag::with(['user'])
            ->withCount('kitbagItems')
            ->when($search, fn($qry) => $qry->whereHas('user', fn($qry) => $qry->whereLike('email', '%'.$search.'%')))
            ->orderBy('id','DESC')
            ->paginate($per_page);
        $data = $this->makePaginationResponse($pagination, fn($item) => AdminKitbagListResource::collection($item))->data;
        return $this->apiSuccess('List of user kitbags', $data);
    }

    /**
     * Display the specified resource.
     */
    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/admin/kitbag/{uuid}",
     *     summary="Client kitbag details.",
     *     description="Client kitbag details.",
     *     operationId="KitbagDetail",
     *     tags={"Kitbag"},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of kitbag.",
     *         @OA\Schema(type="string", example="dc0ea028-1f3c-465b-8cdc-b6d998512e76")
     *     ),   
     *     @OA\Response(
     *         response=200,
     *         description="Successful kitbag detail response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Kitbag detail"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="created_at", type="string", example="2025/11/04"),
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="product_name", type="string", example="Saepe reiciendis et quae dolores et tenetur voluptas."),
     *                         @OA\Property(property="image", type="string", format="url", example="http://192.168.100.23:8008/storage/106/syrup.jpg"),
     *                         @OA\Property(property="quantity", type="integer", example=7),
     *                         @OA\Property(
     *                             property="variant",
     *                             type="object",
     *                             @OA\Property(property="name", type="string", example="Variant-1"),
     *                             @OA\Property(property="size_value", type="number", example=100),
     *                             @OA\Property(property="size_unit", type="string", example="g"),
     *                             @OA\Property(property="price", type="number", format="float", example=198),
     *                             @OA\Property(property="previous_price", type="number", format="float", nullable=true, example=null)
     *                         )
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     * )
     */
    public function show(Kitbag $kitbag)
    {
        $kitbag->load(['kitbagItems' => [
            'product.media',
            'variation']]);
        $data = new AdminKitbagDetailResource($kitbag);
        return $this->apiSuccess('Kitbag detail', $data);
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * @OA\Delete(
     *     security={{"sanctum": {}}}, 
     *     path="/admin/kitbag/{uuid}",
     *     operationId="KitbagDelete",
     *     tags={"Kitbag"},
     *     summary="Delete a kitbag.",
     *     description="Delete a kitbag.",
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of kitbag to delete",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Brand successfully deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="null", example=null),
     *             @OA\Property(property="message", type="string", example="Kitbag has been deleted successfully.")
     *         )
     *     )
     * )
     */
    public function destroy(Kitbag $kitbag)
    {
        $kitbag->delete();
        return $this->apiSuccess('Kitbag has been deleted successfully.');
    }
}
