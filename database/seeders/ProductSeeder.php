<?php

namespace Database\Seeders;

use App\Models\Categories;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            for ($i = 1; $i <= 100; $i++) {
                $product = [
                    'added_by' => 1,
                    'brand_id' => rand(1, 15),
                    'name' => fake()->sentence(),
                    'description' => implode('', array_map(fn($text) => "<p>{$text}</p>", fake()->paragraphs())),
                ];

                $categories = range(1, 15);
                $count = rand(1, 3);
                $randomKeys = array_rand($categories, $count);
                $random_categories = is_array($randomKeys)
                    ? array_intersect_key($categories, array_flip($randomKeys))
                    : [$categories[$randomKeys]];

                $tags = range(1, 46);
                $count = rand(1, 3);
                $randomKeys = array_rand($tags, $count);
                $random_tags = is_array($randomKeys)
                    ? array_intersect_key($categories, array_flip($randomKeys))
                    : [$tags[$randomKeys]];

                $variations = [
                    [
                        "size_value" => 100,
                        "size_unit" => "gm",
                    ],
                    [
                        "size_value" => 200,
                        "size_unit" => "gm"
                    ],
                    [
                        "size_value" => 500,
                        "size_unit" => "gm"
                    ],
                    [
                        "size_value" => 650,
                        "size_unit" => "gm"
                    ],
                    [
                        "size_value" => 800,
                        "size_unit" => "gm"
                    ]
                ];

                $count = rand(1, count($variations));
                $randomKeys = array_rand($variations, $count);
                $random_variations = is_array($randomKeys)
                    ? array_values(array_intersect_key($variations, array_flip($randomKeys)))
                    : [$variations[$randomKeys]];


                $product = Product::create($product);
                $product->categories()->attach($random_categories);
                $product->tags()->attach($random_tags);
                $product->variations()->createMany($random_variations);

                $capsule = public_path('assets/img/tablets.jpg');
                $cream   = public_path('assets/img/cream.jpg');
                $syrup   = public_path('assets/img/syrup.jpg');
                $medi_plaster   = public_path('assets/img/medi-plaster.png');
                $visc_inhaler_example   = public_path('assets/img/visc-inhaler.jpg');

                $product_media = [
                    $capsule,
                    $cream,
                    $syrup,
                    $medi_plaster,
                    $visc_inhaler_example
                ];
                $randomKey   = array_rand($product_media);
                $randomImage = $product_media[$randomKey];

                // Add random image to feature
                $product->addMedia($randomImage)->preservingOriginal()->toMediaCollection(Product::PRODUCT_FEATURE);
                unset($product_media[$randomKey]);
                foreach ($product_media as $GI) {
                    $product->addMedia($GI)->preservingOriginal()->toMediaCollection(Product::PRODUCT_GALLERY);
                }
            }
        });
    }
}
