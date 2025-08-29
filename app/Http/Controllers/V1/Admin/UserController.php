<?php

namespace App\Http\Controllers\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserCollection;
use App\Models\User;
use App\ResponseTrait;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //
    use ResponseTrait;
    function View_User(Request $request)
    {
        $limit = $request->input('limit', 9);
        $user = User::where('is_admin', 0)->paginate($limit);
        if (!$user) {
            return $this->apiError("user not found");
        }
        $user= new UserCollection($user);
        return $this->apiSuccess(
            'User data:',
            $user
        );
    }
    function delete(User $user)
    {
        $delete = $user->delete();
        if ($delete) {
            return $this->apiSuccess('user has been deleted');
        }
        return $this->apiError('Failed');
    }

}
