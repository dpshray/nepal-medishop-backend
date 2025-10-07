<?php

namespace Database\Seeders;

use App\Enums\UserTypeEnum;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $half_products = (int) DB::table('products')->count() / 2;
        $user_ids = User::where('user_type', UserTypeEnum::USER->value)->take(25)->pluck('id')->all();
        DB::transaction(function () use ($half_products, $user_ids) {
            Product::active()->take($half_products)->each(function ($product) use ($user_ids) {
                $temp = [];
                foreach ($user_ids as $user_id) {
                    $created_at = fake()->dateTimeBetween('-7 days', '-1 days');
                    $temp[] = [
                        'user_id' => $user_id,
                        'review' => fake()->realText(fake()->numberBetween(200,500)),
                        'rating' => rand(1, 5),
                        'created_at' => $created_at,
                        'updated_at' => fake()->boolean(20) ? now() : $created_at
                    ];
                }
                $product->reviews()->createMany($temp);
            });
        });
    }
}
