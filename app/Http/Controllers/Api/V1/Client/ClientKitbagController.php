<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\Product\Card\KitbagCardResource;
use App\Models\Kitbag;
use App\Models\Product;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientKitbagController extends Controller
{
    use ResponseTrait;
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/kitbag",
     *     summary="Get user kitbag items.",
     *     description="Get user kitbag items.",
     *     operationId="KitbagItemList",
     *     tags={"Kitbag"},
     *     @OA\Response(
     *         response=200,
     *         description="List of kitbag items retrieved successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="List of kitbag items"),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="item_uuid", type="string", example="157cfda6-5bdc-4856-bf42-b82d951ae2c3"),
     *                         @OA\Property(property="item_name", type="string", example="Similique natus a quidem deserunt."),
     *                         @OA\Property(property="item_slug", type="string", example="similique-natus-a-quidem-deserunt"),
     *                         @OA\Property(property="brand_name", type="string", example="GlaxoSmithKline"),
     *                         @OA\Property(property="variant_name", type="string", example="Variant-2"),
     *                         @OA\Property(property="variant_id", type="integer", example=1),
     *                         @OA\Property(property="image", type="string", example="http://192.168.100.23:8008/storage/91/visc-inhaler.jpg"),
     *                         @OA\Property(property="quantity", type="integer", example=1),
     *                         @OA\Property(property="price", type="number", format="float", example=1309),
     *                         @OA\Property(property="subtotal", type="number", format="float", example=1309)
     *                     )
     *                 ),
     *                 @OA\Property(property="total_items", type="integer", example=8),
     *                 @OA\Property(property="total_amount", type="number", format="float", example=6811)
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $kitbag = Auth::user()->kitbags;
        $kitbag->load(['product.media', 'product.brand', 'variation']);

        $items = KitbagCardResource::collection($kitbag)->toArray(request());

        $collection_data = collect($items['data'] ?? $items); // handle both cases

        $total_items = $collection_data->sum('quantity');
        $total_amount = $collection_data->sum('subtotal');

        return $this->apiSuccess('List of kitbag items', compact('items', 'total_items', 'total_amount'));
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     security={{"sanctum": {}}},
     *     path="/kitbag",
     *     summary="Add item to kitbag",
     *     description="Add a product variant to the user's kitbag with a specified quantity.",
     *     operationId="AddToKitbag",
     *     tags={"Kitbag"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_variation_id", "product_slug", "quantity"},
     *             @OA\Property(
     *                 property="product_variation_id",
     *                 type="integer",
     *                 example=1,
     *                 description="ID of the product variant to be added"
     *             ),
     *             @OA\Property(
     *                 property="product_slug",
     *                 type="string",
     *                 example="similique-natus-a-quidem-deserunt",
     *                 description="Slug of the product"
     *             ),
     *             @OA\Property(
     *                 property="quantity",
     *                 type="integer",
     *                 example=1,
     *                 description="Quantity of the product to add"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item successfully added to kitbag.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="item added to kitbag."),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_slug' => 'required|exists:products,slug',
            'product_variation_id' => 'required|exists:product_variations,id',
            'quantity' => 'required|integer'
        ]);
        $product = Product::where('slug', $request->product_slug)->firstOrFail();
        $kitbag_product = Kitbag::firstWhere([
            ['product_id', $product->id],
            ['user_id', Auth::id()],
        ]);
        if ($kitbag_product) {
            $kitbag_product->increment('quantity', $request->quantity);
        }else{
            $data = [...$data, ...['product_id' => $product->id, 'user_id' => Auth::id()]];
            Kitbag::create($data);
        }
        return $this->apiSuccess('item added to kitbag.');
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * @OA\Delete(
     *     security={{"sanctum": {}}}, 
     *     path="/kitbag/{uuid}",
     *     operationId="KitbagDelete",
     *     tags={"Kitbag"},
     *     summary="Delete a kitbag item.",
     *     description="Delete a kitbag item.",
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of a kitbag to delete",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Kitbag item removed successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Kitbag item removed succesfully."),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     ),
     * )
     */
    public function destroy(Kitbag $kitbag)
    {
        $kitbag->delete();
        return $this->apiSuccess('Kitbag item removed succesfully.');
    }
}
