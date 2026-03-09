<?php

namespace App\Http\Controllers\Api\V1\Admin\ClientReview;

use App\Enums\GrievanceEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\Review\Grievance\UserGrievanceDetailResource;
use App\Http\Resources\User\Review\Grievance\UserGrievanceListResource;
use App\Models\Grievance;
use App\Traits\PaginationTrait;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class AdminGrievanceController extends Controller
{
    //Get all grievance of users
    use ResponseTrait;
    use PaginationTrait;
    public function index(Request $request)
    {
        $per_page = $request->query('per_page');
        $search = $request->query('search');
        $pagination = Grievance::when($search, function ($query, $search) {
            $query->where('subject', 'like', "%{$search}%");
        })->orderByDesc('created_at')->paginate($per_page ?? 10);
        $data = $this->makePaginationResponse($pagination, fn($item) => UserGrievanceListResource::collection($item))->data;
        return $this->apiSuccess('List of user grievances', $data);
    }
    public function show(Grievance $grievance)
    {
        return $this->apiSuccess('Grievance details', new UserGrievanceDetailResource($grievance));
    }

    public function updateGrievanceStatus(Request $request, Grievance $grievance)
    {
        $status = GrievanceEnum::tryFrom($request->input('status'));

        $request->validate([
            'status' => 'required|in:PENDING,UNDER_PROCESS,RESOLVED',
            'remarks' => 'nullable|string',
        ]);

        $grievance->status = $request->input('status');
        $grievance->remarks = $request->input('remarks');
        $grievance->save();

        return $this->apiSuccess('Grievance status updated successfully', new UserGrievanceDetailResource($grievance));
    }

    public function destroy(Grievance $grievance)
    {
        $grievance->delete();
        return $this->apiSuccess('Grievance deleted successfully');
    }
}
