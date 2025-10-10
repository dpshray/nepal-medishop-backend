<?php

namespace App\Http\Controllers\Api\V1\Client\Purchase;

use App\Enums\ItemTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Purchase\AddToCartRequest;
use App\Http\Resources\User\Purchase\OrderResource;
use App\Models\Package;
use App\Models\Product;
use App\Models\Purchase\Cart;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ClientCartController extends Controller
{
    use ResponseTrait;
    /**
     * @OA\Post(
     *     path="/add-to-cart",
     *     summary="Add an item to the cart",
     *     description="Adds either a product or a package to the user's cart depending on the presence of the variant ID. NOTE: Variant ID of the product.  
     *                 If omitted, the request is treated as adding a **package** to the cart.  
     *                 If provided, the request is treated as adding a **product** to the cart.",
     *     operationId="AddToCart",
     *     tags={"Cart"},
     *     security={{"sanctum": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"slug", "quantity"},
     *             @OA\Property(
     *                 property="slug",
     *                 type="string",
     *                 example="unde-a-maiores-et-omnis",
     *                 description="Unique slug identifier for the product or package."
     *             ),
     *             @OA\Property(
     *                 property="variant_id",
     *                 type="integer",
     *                 example=2,
     *                 nullable=true,
     *                 description="Variant ID of the product.  
     *                 If omitted, the request is treated as adding a **package** to the cart.  
     *                 If provided, the request is treated as adding a **product** to the cart."
     *             ),
     *             @OA\Property(
     *                 property="quantity",
     *                 type="integer",
     *                 example=1,
     *                 description="Number of items to add to the cart."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item has been added to cart",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Item has been added to cart"),
     *             @OA\Property(property="data", type="string", nullable=true, example=null, description="Data payload (null if no cart details returned)"),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */

    function storeOnCart(AddToCartRequest $request)
    {
        $cart = [];
        if ($request->has('variant_id')) { #Product
            $product_w_variant = Product::with(['brand','variations' => fn($qry) => $qry->where('id', $request->variant_id), 'media'])
                ->where('slug', $request->slug)->firstOrFail();
            $product_variation = $product_w_variant->variations->first();
            $product_actual_price = $product_variation->platform_price;
            $product_discount = $product_w_variant->discount_percent; 
            $price = empty($product_discount) ? $product_actual_price : ($product_actual_price - ($product_actual_price * $product_discount)/100); 
            $cart = $request->safe()->merge([
                'user_id' => Auth::id(),
                'item_type' => Product::class,
                'item_id' => $product_w_variant->id,
                'variant_id' => $request->variant_id,
                'item_name' => $product_w_variant->name,
                'item_slug' => $product_w_variant->slug,
                'brand_name' => $product_w_variant->brand->name,
                'variant_name' => (float) $product_variation->size_value.' '. $product_variation->size_unit,
                'quantity' => $request->quantity,
                'price' => $price,
                'subtotal' => $price * $request->quantity,
                'created_at' => now(),
                'image' => $product_w_variant->getFirstMedia(Product::PRODUCT_FEATURE)->getUrl()
            ])->all();
        }else{ #Package
            $package = Package::where('slug', $request->slug)->firstOrFail();
            $package_actual_price = $package->price;
            $package_discount = $package->discount_percent;
            $price = empty($package_discount) ? $package_actual_price : ($package_actual_price - ($package_actual_price * $package_discount) / 100);
            $cart = $request->safe()->merge([
                'user_id' => Auth::id(),
                'item_type' => Package::class,
                'item_id' => $package->id,
                'item_name' => $package->name,
                'item_slug' => $package->slug,
                'quantity' => $request->quantity,
                'price' => $price,
                'subtotal' => $price * $request->quantity,
                'created_at' => now(),
                'image' => $package->getFirstMedia(Package::PACKAGE_FEATURED)->getUrl()
            ])->all();
        }
        Cart::create($cart);

        return $this->apiSuccess('Item has been added to cart.');
    }

    private function getUserCartItems(){
        $cart_items = Auth::user()->cart()->get()->groupBy('item_type');
        $product_items = $cart_items->get(Product::class)?->groupBy('item_id');
        $merged1 = [];
        if ($product_items) {            
            foreach ($product_items as $key => $items) {
                $merged1[$key] = [];
    
                foreach ($items as $item) {
                    $uniqueKey = $item['item_id'] . '-' . $item['variant_id'];
    
                    if (!isset($merged1[$key][$uniqueKey])) {
                        $merged1[$key][$uniqueKey] = $item;
                    } else {
                        $merged1[$key][$uniqueKey]['quantity'] += $item['quantity'];
                        $merged1[$key][$uniqueKey]['subtotal'] += $item['subtotal'];
                    }
                }
    
                // Reset to indexed array for clean JSON structure
                $merged1[$key] = array_values($merged1[$key]);
            }
        }

        $merged2 = [];
        $package_items = $cart_items->get(Package::class)?->groupBy('item_id');

        if ($package_items) {            
            foreach ($package_items as $key => $items) {
                $merged2[$key] = [];
    
                foreach ($items as $item) {
                    $uniqueKey = $item['item_id'] . '-' . $item['variant_id'];
    
                    if (!isset($merged2[$key][$uniqueKey])) {
                        $merged2[$key][$uniqueKey] = $item;
                    } else {
                        $merged2[$key][$uniqueKey]['quantity'] += $item['quantity'];
                        $merged2[$key][$uniqueKey]['subtotal'] += $item['subtotal'];
                    }
                }
    
                // Reset to indexed array for clean JSON structure
                $merged2[$key] = array_values($merged2[$key]);
            }
        }

        $cart = collect([$merged1, $merged2])
            ->flatMap(fn($group) => collect($group)->flatten(1))
            ->flatten(1)
            ->values()
            ->map(function ($item) {
                return [
                    "item_type" => strtolower(class_basename($item['item_type'])),
                    "item_name" => $item['item_name'],
                    "item_slug" => $item['item_slug'],
                    "brand_name" => $item['brand_name'],
                    "variant_name" => $item['variant_name'],
                    "image" => $item['image'],
                    "variant_id" => empty($item['variant_id']) ? null : (int) $item['variant_id'],
                    "quantity" => $item['quantity'],
                    "price" => (float) $item['price'],
                    "subtotal" => (float) $item['subtotal'],
                ];
            });
        
        return ['cart_items' => $cart, 'total' => round($cart->sum('subtotal'),2)];
    }

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/my-cart",
     *     summary="Fetch cart items of a logged in user.",
     *     description="Fetch cart items of a logged in user.",
     *     operationId="MyCart",
     *     tags={"Cart"},
     *     @OA\Response(
     *         response=200,
     *         description="Cart items retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="My Cart items."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="cart_items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="item_type", type="string", example="product", description="Type of the item: Product or Package"),
     *                         @OA\Property(property="item_name", type="string", example="Debitis debitis autem consectetur saepe.", description="Name of the product or package."),
     *                         @OA\Property(property="brand_name", type="string", example="Sanofi", description="Brand name of an item(product)."),
     *                         @OA\Property(property="variant_name", type="string", example="100 patch", description="Variant name of an item(product)."),
     *                         @OA\Property(property="item_slug", type="string", example="debitis-debitis-autem-consectetur-saepe", description="Unique slug of the item."),
     *                         @OA\Property(property="image", type="string", example="http://192.168.100.23:8008/storage/91/medi-plaster.png", description="Image URL of the item."),
     *                         @OA\Property(property="variant_id", type="integer", nullable=true, example=2, description="Variant ID if the item is a product, null if a package."),
     *                         @OA\Property(property="quantity", type="integer", example=2, description="Quantity of the item in the cart."),
     *                         @OA\Property(property="price", type="number", format="float", example=1385.28, description="Unit price of the item."),
     *                         @OA\Property(property="subtotal", type="number", format="float", example=2770.56, description="Subtotal = quantity * price")
     *                     )
     *                 ),
     *                 @OA\Property(property="total", type="number", format="float", example=12270.56, description="Total price of all items in the cart")
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function fetchMyCart() {
        return $this->apiSuccess('My Cart items.', $this->getUserCartItems());
    }

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/remove-cart-item/item_type/{item_type}/slug/{slug}",
     *     summary="Remove a cart item of a logged in user.NOTE: slug value can be: package or product.",
     *     description="Remove a cart item of a logged in user.",
     *     operationId="MyCartItemRemover",
     *     tags={"Cart"},
     *     @OA\Parameter(
     *         name="item_type",
     *         in="path",
     *         required=true,
     *         description="Item type of an item(product/package)",
     *         @OA\Schema(type="string", example="product")
     *     ),
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug of an item",
     *         @OA\Schema(type="string", example="starter-set")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item removed from cart successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Item has been removed", description="Response message confirming item removal"),
     *             @OA\Property(property="data", type="string", nullable=true, example=null, description="Additional response data (null if not applicable)"),
     *             @OA\Property(property="success", type="boolean", example=true, description="Indicates if the operation was successful")
     *         )
     *     )
     * )
     */
    function cartItemRemover(Request $request, $item_type, $slug) {
        $user_cart = Auth::user()->cart();
        if ($item_type == ItemTypeEnum::PRODUCT->value) {
            $user_cart->where([
                ['item_type', Product::class],
                ['item_slug', $slug]
            ])->delete();
        }elseif ($item_type == ItemTypeEnum::PACKAGE->value) {
            $user_cart->where([
                ['item_type', Package::class],
                ['item_slug', $slug]
            ])->delete();
        }
        return $this->apiSuccess('Item has been removed from cart.');
    }
}
