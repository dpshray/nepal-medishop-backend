<?php

namespace App\Http\Controllers\Api\V1\Admin\ClientFeedback;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class AdminFeedbackController extends Controller
{
    //
    use ResponseTrait, PaginationTrait;
    function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->query('search', null);
        $query = Feedback::with(['user']);
        $query->latest();
        $paginated=$query->paginate($perPage);
        $result = $this->makePaginationResponse($paginated, fn() => $data)->data;
        return $this->apiSuccess('List of Client Feedback');
    }
}
