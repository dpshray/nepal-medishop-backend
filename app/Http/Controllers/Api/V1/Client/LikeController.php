<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Product;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    use ResponseTrait;

    function toggleProductFavourite(Product $product){
        $query = $product->likes();
        $user_like = $query->where('user_id', Auth::id()); 
        $msg = null;
        if ($user_like->exists()) {
            $user_like->delete();
            $msg = 'Item removed to favourite';
        }else{
            $msg = 'Item added to favourite';
            $query->create(['user_id' => Auth::id()]);
        }
        return $this->apiSuccess($msg);

    }

    function togglePackageFavourite(Package $product){

    }

}
