<?php

namespace Database\Seeders;

use App\Models\Categories;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $faker = Faker::create();
        $categoryNames = ['Hoodies', 'Pants', 'Shirts', 'T-Shirts'];
        foreach ($categoryNames as $catName) {
            $catSlug = Str::slug($catName);
            $category = Categories::create([
                'title' => $catName,
                'slug' => $catSlug,
            ]);
            $categoryIds[] = $category->id;
        }

        // Create 10 products
        for ($i = 0; $i < 10; $i++) {
            $product = Product::create([
                'name' => 'Product ' . $i,
                'slug' => 'product' . $i,
                'description' => $faker->text(80),
                'price' => $faker->randomFloat(2, 100, 500),
                'discount_price' => $faker->optional()->randomFloat(2, 50, 400),
                'pattern' => $faker->word(),
                'fabric' => $faker->word(),
                'material' => $faker->word(),
            ]);

            // Add product image
            $product->addMedia(storage_path('app/public/test.jpg'))
                ->preservingOriginal()
                ->toMediaCollection(Product::MEDIA_NAME);

            // Attach one random category
            $product->categories()->attach($faker->randomElement($categoryIds));

            // Create variants
            for ($j = 0; $j < rand(2, 4); $j++) {
                $variant = Variant::create([
                    'product_id' => $product->id,
                    'size' => $faker->randomElement(['S', 'M', 'L', 'XL','XXL']),
                    'color' => $faker->randomElement(['white','black']),
                    'price' => $faker->randomFloat(2, 100, 500),
                    'discount_price' => $faker->optional()->randomFloat(2, 50, 400),
                    'stock' => $faker->numberBetween(1, 50),
                ]);

                // Add variant image
                $variant->addMedia(storage_path('app/public/test.jpg'))
                    ->preservingOriginal()
                    ->toMediaCollection(Variant::MEDIA_NAME);
            }
        }
    }
}
