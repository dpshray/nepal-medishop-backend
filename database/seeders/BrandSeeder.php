<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $brands = [
            ['id' => 1, 'name' => 'Pfizer', 'image' => public_path('assets/img/brands/pfizer.jpg'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 2, 'name' => 'Cipla', 'image' => public_path('assets/img/brands/cipla.png'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 3, 'name' => 'Sun Pharma', 'image' => public_path('assets/img/brands/sun-pharma.jpeg'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 4, 'name' => 'GlaxoSmithKline', 'image' => public_path('assets/img/brands/glaxosmithkline.png'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 5, 'name' => 'Bayer', 'image' => public_path('assets/img/brands/bayer.png'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 6, 'name' => 'Merck', 'image' => public_path('assets/img/brands/merck.jpg'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 7, 'name' => 'Novartis', 'image' => public_path('assets/img/brands/novartis.png'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 8, 'name' => 'Abbott', 'image' => public_path('assets/img/brands/abbott.jpg'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 9, 'name' => 'Johnson & Johnson', 'image' => public_path('assets/img/brands/johnson-johnson.jpg'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 10, 'name' => 'Roche', 'image' => public_path('assets/img/brands/roche.jpg'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 11, 'name' => 'AstraZeneca', 'image' => public_path('assets/img/brands/astrazeneca.jpg'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 12, 'name' => 'Bristol-Myers Squibb', 'image' => public_path('assets/img/brands/bristol-myers-squibb.jpg'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 13, 'name' => 'Sanofi', 'image' => public_path('assets/img/brands/sanofi.jpg'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 14, 'name' => 'Takeda', 'image' => public_path('assets/img/brands/takeda.jpg'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 15, 'name' => 'Gilead Sciences', 'image' => public_path('assets/img/brands/gilead-sciences.jpg'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 16, 'name' => 'Eli Lilly', 'image' => public_path('assets/img/brands/eli-lilly.jpg'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 17, 'name' => 'Boehringer Ingelheim', 'image' => public_path('assets/img/brands/boehringer-ingelheim.png'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 18, 'name' => 'Teva Pharmaceuticals', 'image' => public_path('assets/img/brands/teva-pharmaceuticals.png'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 19, 'name' => 'Mylan', 'image' => public_path('assets/img/brands/mylan.png'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 20, 'name' => 'Fresenius Kabi', 'image' => public_path('assets/img/brands/fresenius-kabi.png'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
        ];

        DB::transaction(function () use ($brands) {
            foreach ($brands as $item) {
                $data = [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'is_featured' => fake()->boolean(50),
                    'is_popular' => fake()->boolean(50)
                ];
                Brand::create($data)
                ->addMedia($item['image'])
                ->preservingOriginal()
                ->toMediaCollection(Brand::BRAND_IMAGE);
            }
        });
    }
}
