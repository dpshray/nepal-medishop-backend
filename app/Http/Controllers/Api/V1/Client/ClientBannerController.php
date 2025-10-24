<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\ClientBannerListResource;
use App\Models\Banner;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class ClientBannerController extends Controller
{
    use ResponseTrait;

    /**
     * Handle the incoming request.
     */
    /**
     * @OA\Get(
     *     path="/banner",
     *     summary="Get all banners",
     *     description="Get all banners.",
     *     operationId="ClientBannerList",
     *     tags={"Banner"},
     *     @OA\Response(
     *         response=200,
     *         description="List of banners.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="List of banners."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="title", type="string", nullable=true, example="Look ma, a banner!"),
     *                     @OA\Property(property="url", type="string", nullable=true, example="https://inboxes.com/"),
     *                     @OA\Property(property="image", type="string", format="url", example="http://192.168.100.23:8008/storage/2643/sunset-7007680_1920.jpg")
     *                 )
     *             ),
     *             @OA\Property(property="success", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function __invoke(Request $request)
    {
        $banners = Banner::visible()
            ->orderBy('order','ASC')
            ->orderBy('id','ASC')
            ->get();
        $data = ClientBannerListResource::collection($banners);
        if ($banners->isEmpty()) { #default banner data
            $data = [
                [
                    'title' => null,
                    'url' => null,
                    'image' => asset('assets/img/default-banner.jpg')
                ]
            ];
        }
        return $this->apiSuccess('List of banners.', $data);
    }
}
