<?php

namespace Database\Seeders;

use App\Models\Point\CouponCode;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Carbon\Carbon;

class CouponCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 0; $i < 5; $i++) {
            CouponCode::create([
                'code' => 'test' . $i,
                'discount_percent' => 10,
                'start_date' => Carbon::now()->addDays($i), // start today + i days
                'end_date' => Carbon::now()->addDays($i + 7), // end 7 days after start
                'is_active' => 1,
                'description'=>implode("\n\n", $faker->paragraphs(3)),
            ]);
        }
    }
}
