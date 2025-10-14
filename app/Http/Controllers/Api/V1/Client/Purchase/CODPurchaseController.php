<?php

namespace App\Http\Controllers\Api\V1\Client\Purchase;

use App\Enums\OrderUserTypeEnum;
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
use Illuminate\Support\Str;

class CODPurchaseController extends Controller
{
    use ResponseTrait;

    /**
     * @OA\Post(
     *     path="/orders",
     *     summary="Submit an order.",
     *     description="Submit an order.NOTE: name, email, mobile fields are only needed for GUEST USER.",
     *     operationId="CODOrder",
     *     tags={"Order"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"payment_method","name","email","mobile","address","products"},
     *             @OA\Property(property="payment_method", type="string", example="COD"),
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
     *         description="Your order has been placed successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Your order has been placed successfully."),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="amount", type="number", format="float", example=27869.54),
     *                 @OA\Property(property="order_number", type="string", example="UJUdS1lXDpl2OpSwyQJS"),
     *                 @OA\Property(property="payment_method", type="string", example="Cash on Delivery"),
     *                 @OA\Property(property="date", type="string", example="2025/10/13"),
     *                 @OA\Property(
     *                     property="ordered_items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="item_name", type="string", example="Debitis debitis autem consectetur saepe."),
     *                         @OA\Property(property="variant_name", type="string", nullable=true, example="Variant-2"),
     *                         @OA\Property(property="quantity", type="integer", example=1),
     *                         @OA\Property(property="price", type="number", format="float", example=1385.28),
     *                         @OA\Property(property="total", type="number", format="float", example=1385.28)
     *                     )
     *                 ),
     *                 @OA\Property(property="delivery_address", type="string", example="Shyambhu, Kathmandu")
     *             )
     *         )
     *     )
     * )
     */
    function __invoke(CODRequest $request)
    {
        // return $request->all();

        $products_ordered = [];
        if (!$request->hasAny(['products', 'packages'])) {
            return $this->apiError("At least one product or package must be included in the order.", 422);
        }

        if ($request->has('products')) {
            $product_slug = $request->collect('products')->pluck('product_slug');
            $products = Product::select('id', 'slug', 'discount_percent')->with(['variations:id,product_id,platform_price'])->whereIn('slug', $product_slug)->get()->keyBy('slug');

            $products_ordered = $request->collect('products')->map(function ($item) use ($products) {
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
            $packages = Package::select('id', 'slug', 'price', 'discount_percent')->whereIn('slug', $product_slug)->get()->keyBy('slug');

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
            'payment_method' => $request->payment_method,
            'payment_status' => PaymentStatusEnum::PENDING->value,
            'status' => OrderStatusEnum::PENDING->value,
            'created_at' => now()
        ];

        $response = null;
        DB::transaction(function () use ($request, $order_detail, $order_items, &$response) {
            $user = Auth::user();
            $order_code = Str::random(20);
            if ($user) { # Logged user
                $order = array_merge(
                    $request->only(['address', 'description']),
                    $order_detail,
                    ['user_type' => OrderUserTypeEnum::USER->value, 'order_code' => $order_code, 'user_id' => $user]
                );
                $user->orders()->create($order)->orderItems()->createMany($order_items);
                $user->cart()->delete();
            } else { # Guest user
                $order = array_merge(
                    $request->only(['name', 'email', 'mobile', 'address', 'description']),
                    $order_detail,
                    ['user_type' => OrderUserTypeEnum::GUEST->value, 'order_code' => $order_code]
                );
                Order::create($order)->OrderItems()->createMany($order_items);
            }
            $user = Auth::user();
            $order = Order::where('order_code', $order_code)->firstOrFail();
            $order_items = $order
                ->orderItems()
                ->with(['package', 'product.variations'])
                ->get()
                ->map(function ($item) {
                    if ($item->item_type == Product::class) {
                        return [
                            'item_name' => $item->product->name,
                            'variant_name' => $item->product->variations->firstWhere('id', $item->item_variant_id)->name,
                            'quantity' => (int) $item->quantity,
                            'price' => (float) $item->price,
                            'total' => (float) $item->total
                        ];
                    } elseif ($item->item_type == Package::class) {
                        return [
                            'item_name' => $item->package->name,
                            'variant_name' => null,
                            'quantity' => (int) $item->quantity,
                            'price' => (float) $item->price,
                            'total' => (float) $item->total
                        ];
                    }
                });

            $response = [
                'amount' => (float) $order->price,
                'order_number' => $order->order_code,
                'payment_method' => $order->payment_method == PaymentMethodEnum::COD->value ? 'Cash on Delivery' : $order->payment_method,
                'date' => $order->created_at->format('Y/m/d'),
                'ordered_items' => $order_items,
                'delivery_address' => $order->address
            ];
        });

        return $this->apiSuccess("Your order has been placed successfully.", $response);
    }
}
