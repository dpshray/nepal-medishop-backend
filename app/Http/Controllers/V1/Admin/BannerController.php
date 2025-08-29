<?php

namespace App\Http\Controllers\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Banner\BannerRequest;
use App\Http\Requests\Banner\UpdateBannerRequest;
use App\Models\Banner;
use App\ResponseTrait as AppResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\ResponseTrait;

class BannerController extends Controller
{
    //
    use AppResponseTrait;
    function add_banner(BannerRequest $request)
    {
        $banner = Banner::create([
            'title' => $request->title,
            'sub_title' => $request->subtitle,
            'url' => $request->url,
        ]);
        if (!$banner) {
            return $this->apiError('Failed to add banner');
        }

        if ($request->hasFile('image')) {
            $banner->addMedia($request->image)->toMediaCollection(Banner::MEDIA_NAME);
        }
        return $this->apiSuccess('Banner has been added successfull', $banner);
    }

    public function update_banner(UpdateBannerRequest $request, Banner $banner)
    {
        $updated = $banner->update([
            'title' => $request->title,
            'sub_title' => $request->subtitle,
            'url' => $request->url,
        ]);

        if (!$updated) {
            return $this->apiError('Failed to update banner');
        }

        // Handle images
        if ($request->hasFile('image')) {
            $banner->clearMediaCollection(Banner::MEDIA_NAME);
            $banner->addMedia($request->image)->toMediaCollection(Banner::MEDIA_NAME);
        }

        return $this->apiSuccess('Banner has been successfully updated', $banner);
    }

    function delete_banner(Banner $banner)
    {
        $banner->delete();
        $banner->clearMediaCollection(Banner::MEDIA_NAME);
        return $this->apiSuccess('Banner has been successfull deleted', null);
    }
}
