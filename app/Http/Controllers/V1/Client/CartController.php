<?php

namespace App\Http\Controllers\V1\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\CartRequest;
use App\Http\Requests\Cart\UpdateCartRequest;
use App\Http\Resources\Cart\CartCollection;
use App\Models\Cart;
use App\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    //
    use ResponseTrait;
    function view_cart()
    {
        $subtotal=0;
        $total=0;
        $delivery_charge=200;
        $user = Auth::user();
        $cart = Cart::with('product', 'variant')->where('user_id', $user->id)->get();
        $total_cart = Cart::where('user_id', $user->id)->count();
        if (!$cart) {
            return $this->apiError('cart not found .Add product on cart');
        }
        foreach($cart as $item)
        {
            $originalPrice = $item->variant->price;
            $sellingPrice = $item->variant->discount_price ?? $originalPrice;
            $subtotal += $sellingPrice * $item->quantity;
        }
        $total=$subtotal+$delivery_charge;
        $cart = new CartCollection($cart);
        // $cart = $cart->merge(['total_cart' => $total_cart]);
        return $this->apiSuccess('cart data', [
            'data'=>$cart,
            'total_cart'=>$total_cart,
            'delivery_charge'=>$delivery_charge,
            'subtotal'=>$subtotal,
            'total'=>$total,
        ]);
    }

    function add_cart(CartRequest $request)
    {
        $user = Auth::user();
        $exists = Cart::where('user_id', $user->id)->where('variant_id', $request->variant_id)->exists();
        if ($exists) {
            return $this->apiError('This product already exist on cart');
        }
        $cart = Cart::create([
            'user_id' => $user->id,
            'product_id' => $request->product_id,
            'variant_id' => $request->variant_id,
            'quantity' => $request->quantity,
        ]);
        if (!$cart) {
            return $this->apiError('Failed to add in cart');
        }
        return $this->apiSuccess('product has added to cart successful', $cart);
    }
    function update_cart(Cart $cart, UpdateCartRequest $request)
    {
        $user = Auth::user();
        $data = $cart->update([
            'quantity' => $request->quantity,
        ]);
        if (!$data) {
            return $this->apiError('Failed to update in cart');
        }
        return $this->apiSuccess('cart has been update successful', $cart);
    }
    function delete_from_cart(Cart $cart)
    {
        $user = Auth::user();
        $data = $cart->delete();
        if(!$data)
        {
            return $this->apiError('Cart is empty');
        }
        return $this->apiSuccess('product has been remove from cart', null);
    }
}
