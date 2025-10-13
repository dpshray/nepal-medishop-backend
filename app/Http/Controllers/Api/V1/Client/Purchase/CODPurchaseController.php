<?php

namespace App\Http\Controllers\Api\V1\Client\Purchase;

use App\Enums\Purchase\OrderStatusEnum;
use App\Enums\Purchase\PaymentMethodEnum;
use App\Enums\Purchase\PaymentStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Purchase\CODRequest;
use App\Models\Package;
use App\Models\Product;
use App\Models\Purchase\Order;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CODPurchaseController extends Controller
{
    use ResponseTrait;

    /**
     * @OA\Post(
     *     path="/cash-on-delivery",
     *     summary="Cash on delivery order.",
     *     description="Cash on delivery order.NOTE: name, email, mobile fields are only needed for GUEST USER.",
     *     operationId="CODOrder",
     *     tags={"Order"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","mobile","address","products"},
     *             @OA\Property(property="name", type="string", example="James P. Sullivan"),
     *             @OA\Property(property="email", type="string", format="email", example="james.sullivan100@example.com"),
     *             @OA\Property(property="mobile", type="string", example="9854112547"),
     *             @OA\Property(property="address", type="string", example="Shyambhu, Kathmandu"),
     *             @OA\Property(property="description", type="string", example="some description of this order COD"),
     *             @OA\Property(
     *                 property="products",
     *                 type="array",
     *                 @OA\Items(
     *                     required={"product_slug","variant_id","quantity"},
     *                     @OA\Property(property="product_slug", type="string", example="unde-a-maiores-et-omnis"),
     *                     @OA\Property(property="variant_id", type="integer", example=2),
     *                     @OA\Property(property="quantity", type="integer", example=1)
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="packages",
     *                 type="array",
     *                 @OA\Items(
     *                     required={"package_slug","quantity"},
     *                     @OA\Property(property="package_slug", type="string", example="deluxe-box"),
     *                     @OA\Property(property="quantity", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful registration",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Your order has been placed successfully."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    function __invoke(CODRequest $request)
    {
        // return $request->all();
        $products_ordered = [];
        if ($request->has('products')) {
            $product_slug = $request->collect('products')->pluck('product_slug');
            $products = Product::select('id','slug','discount_percent')->with(['variations:id,product_id,platform_price'])->whereIn('slug', $product_slug)->get()->keyBy('slug');
            
            $products_ordered = $request->collect('products')->map(function($item) use($products){
                $product = $products[$item['product_slug']];
                $product_variant_price = collect($product['variations'])->firstWhere('id', $item['variant_id'])->platform_price;
                $product_discount_percent = $product['discount_percent']; 
                $price = empty($product_discount_percent) ? $product_variant_price : ($product_variant_price - ($product_variant_price * $product_discount_percent) / 100);
                $quantity = $item['quantity'];
                return [
                    'item_type' => Product::class,
                    'item_id' => $products[$item['product_slug']]->id,
                    'item_variant_id' => $item['variant_id'],
                    'quantity' => $quantity,
                    'price' => $price,
                    'total' => $price * $quantity
                ];
            });
        }

        $packages_ordered = [];
        if ($request->has('packages')) {
            $product_slug = $request->collect('packages')->pluck('package_slug');
            $packages = Package::select('id','slug','price','discount_percent')->whereIn('slug', $product_slug)->get()->keyBy('slug');
            
            $packages_ordered = $request->collect('packages')->map(function ($item) use ($packages) {
                $package = $packages[$item['package_slug']];
                $actual_package_price = $package['price'];
                $package_discount_precent = $package['discount_percent'];
                $package_price = empty($package_discount_precent) ? $actual_package_price : ($actual_package_price - ($actual_package_price * $package_discount_precent) / 100);
                $package_quantity = $item['quantity'];

                return [
                    'item_type' => Package::class,
                    'item_id' => $packages[$item['package_slug']]->id,
                    'quantity' => $package_quantity,
                    'price' => $package_price,
                    'total' => $package_quantity * $package_price
                ];
            });
        }
        $order_items = [...$products_ordered, ...$packages_ordered];
        $order_detail = [
            'price' => collect($order_items)->sum('total'),
            'payment_method' => PaymentMethodEnum::COD->value,
            'payment_status' => PaymentStatusEnum::PENDING->value,
            'status' => OrderStatusEnum::PENDING->value,
            'created_at' => now()
        ]; 
        
        $user = Auth::user();
        if ($user) { # Logged user
            DB::transaction(function () use($user, $request, $order_items, $order_detail){
                $order = array_merge(
                    $request->only(['address', 'description']), 
                    $order_detail, 

                );
                $user->orders()->create($order)->orderItems()->createMany($order_items);
                $user->cart()->delete();
            });
        }else{ # Guest user
            DB::transaction(function () use ($request, $order_items, $order_detail) {
                $order = array_merge(
                    $request->only(['name', 'email', 'mobile', 'address', 'description']),
                    $order_detail
                );
                Order::create($order)->OrderItems()->createMany($order_items);
            });
        }
        return $this->apiSuccess("Your order has been placed successfully.");
    }
}
