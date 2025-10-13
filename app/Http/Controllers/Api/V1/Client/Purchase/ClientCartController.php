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
use Illuminate\Validation\UnauthorizedException;

class ClientCartController extends Controller
{
    use ResponseTrait;
    /**
     * @OA\Post(
     *     path="/add-to-cart",
     *     summary="Add an item to the cart",
     *     description="Adds either a product or a package to the user's cart depending on the presence of the variant ID. NOTE: Variant ID of the product.  
     *                 If omitted, the request is treated as adding a **package** to the cart.  
     *                 If provided, the request is treated as adding a **product** to the cart.
     *                 If both variant_id and quantity are omitted, product of cheapest variant item is added.",
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
        if ($request->has(["slug", "variant_id", "quantity"])) { #Product
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
                'variant_name' => ((float) $product_variation->size_value).' '. $product_variation->size_unit,
                'quantity' => $request->quantity,
                'price' => $price,
                'subtotal' => $price * $request->quantity,
                'created_at' => now(),
                'image' => $product_w_variant->getFirstMedia(Product::PRODUCT_FEATURE)->getUrl()
            ])->all();
        }elseif($request->has(["slug","quantity"])){ #Package
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
        }else{ #A product w. default item
            $product_w_variant = Product::with(['brand', 'cheapestVariation', 'media'])
                ->where('slug', $request->slug)->firstOrFail();
            $product_variation = $product_w_variant->variations->first();
            $product_actual_price = $product_variation->platform_price;
            $product_discount = $product_w_variant->discount_percent;
            $price = empty($product_discount) ? $product_actual_price : ($product_actual_price - ($product_actual_price * $product_discount) / 100);
            $quantity = 1; # As for default
            $cart = $request->safe()->merge([
                'user_id' => Auth::id(),
                'item_type' => Product::class,
                'item_id' => $product_w_variant->id,
                'variant_id' => $product_variation->id,
                'item_name' => $product_w_variant->name,
                'item_slug' => $product_w_variant->slug,
                'brand_name' => $product_w_variant->brand->name,
                'variant_name' => ((float) $product_variation->size_value) . ' ' . $product_variation->size_unit,
                'quantity' => $quantity,
                'price' => $price,
                'subtotal' => $price * $quantity,
                'created_at' => now(),
                'image' => $product_w_variant->getFirstMedia(Product::PRODUCT_FEATURE)->getUrl()
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
                    "item_uuid" => $item['uuid'],
                    "item_type" => strtolower(class_basename($item['item_type'])),
                    "item_name" => $item['item_name'],
                    "item_slug" => $item['item_slug'],
                    "brand_name" => $item['brand_name'],
                    "variant_name" => $item['variant_name'],
                    "image" => $item['image'],
                    "variant_id" => empty($item['variant_id']) ? null : (int) $item['variant_id'],
                    "quantity" => (int) $item['quantity'],
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
     *                         @OA\Property(property="item_uuid", type="string", example="c3f6d58b-90f3-47ad-82ed-e1385df0ea2a", description="UUID of an item."),
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
     * @OA\Post(
     *     path="/update-cart-item/{uuid}",
     *     summary="Update an item quantity of a cart. if quantity is 0, that item is removed.",
     *     description="Update an item quantity of a cart.",
     *     operationId="UpdateCartItem",
     *     tags={"Cart"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of cart item",
     *         @OA\Schema(type="string", example="58d8cb4a-11a9-4aee-89e0-d996f86254f4")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"quantity"},
     *             @OA\Property(property="quantity", type="integer", example="5")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cart item quantity updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="A cart item quantity has been updated.",
     *                 description="Response message confirming the cart item quantity update"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="string",
     *                 nullable=true,
     *                 example=null,
     *                 description="Additional response data (null if not applicable)"
     *             ),
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true,
     *                 description="Indicates if the operation was successful"
     *             )
     *         )
     *     )
     * )
     */
    function cartItemUpdater(Request $request, Cart $cart) {
        throw_if($cart->user->isNot(Auth::user()), UnauthorizedException::class);
        $msg = 'A cart item quantity has been updated.';
        if ($request->quantity == 0) {
            $msg = 'A cart item has been removed.';
            $cart->delete();
        }else{
            $quantity = $request->quantity;
            $subtotal = $quantity * $cart->price;
            $cart->update([
                'quantity' => $quantity,
                'subtotal' => $subtotal
            ]);
        }
        return $this->apiSuccess($msg);
    }

    /**
     * @OA\Get(
     *     security={{"sanctum": {}}},
     *     path="/remove-cart-item/{uuid}",
     *     summary="Remove a cart item of a logged in user.NOTE: slug value can be: package or product.",
     *     description="Remove a cart item of a logged in user.",
     *     operationId="MyCartItemRemover",
     *     tags={"Cart"},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="Item uuid of an item(product/package)",
     *         @OA\Schema(type="string", example="c3f6d58b-90f3-47ad-82ed-e1385df0ea2a")
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
    function cartItemRemover(Request $request, Cart $cart) {
        $user = Auth::user();
        throw_if($cart->user->isNot($user), UnauthorizedException::class);
        $user->cart()->where([
            ['item_type', Product::class],
            ['item_id', $cart->item_id]
        ])->delete();
        return $this->apiSuccess('Item has been removed from cart.');
    }
}
