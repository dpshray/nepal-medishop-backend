<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // D:\laragon\www\medishop\public\assets\img\banner\banner-1.png
        $banners = [
            [
                'order' => 1,
                'title' => 'Your Trusted Online Pharmacy — Fast, Safe & Affordable Medicines',
                'banner' =>public_path('assets/img/banner/banner-1.png')
            ],[
                'order' => 2,
                'title' => 'Health at Your Doorstep — Order Genuine Medicines Anytime',
                'banner' =>public_path('assets/img/banner/banner-2.png')
            ],[
                'order' => 3,
                'title' => 'Caring for You — Reliable Medicines, Delivered with Trust',
                'banner' =>public_path('assets/img/banner/banner-3.png')
            ],
        ];
        DB::transaction(function () use($banners){
            foreach ($banners as $banner) {            
                Banner::create([
                    'order' => $banner['order'],
                    'title' => $banner['title'],
                ])
                ->addMedia($banner['banner'])
                ->preservingOriginal()
                ->toMediaCollection(Banner::BANNER_MEDIA);
            }
        });
    }
}
