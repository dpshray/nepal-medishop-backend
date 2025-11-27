<?php

namespace App\Http\Controllers\Api\V1\Admin\DashBoard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class AdminDashBoardController extends Controller
{
    //
    use ResponseTrait;
    function main() {}
    function user()
    {
        $total_client = User::where('user_type', 3)->count();
        $active_users = User::where('status', 1)->count();
        $inactive_users = User::where('status', 0)->count();
        $new_users_month = User::where('created_at', '>=', now()->startOfMonth())->count();
        $data=[
            'total_user'=>$total_client,
            'active_users'=>$active_users,
            'inactive_users'=>$inactive_users,
            'new_users_month'=>$new_users_month,
        ];
        return $this->apiSuccess('User Dashboard',$data);
    }
    function vendor()
    {

    }
    function product()
    {
        $total_products = Product::count();
        $new_products_month = Product::where('created_at', '>=', now()->startOfMonth())->count();

    }
}
