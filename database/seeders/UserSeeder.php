<?php

namespace Database\Seeders;

use App\Enums\UserTypeEnum;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        // User::factory(10)->create();
        $faker = Faker::create();
        User::create([
            'uuid' => $faker->uuid(),
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'mobile_number'=>$faker->numerify('98########'),
            'password' => Hash::make('password123'),
            'user_type' => UserTypeEnum::ADMIN->value,
            'email_verified_at' => now(),
        ]);
        User::create([
            'uuid' => $faker->uuid(),
            'name' => 'user00',
            'email' => 'user@gmail.com',
            'mobile_number'=>$faker->numerify('98########'),
            'password' => Hash::make('password123'),
            'user_type' => UserTypeEnum::USER->value,
            'email_verified_at' => now(),
        ]);
        User::create([
            'uuid' => $faker->uuid(),
            'name' => 'vendor00',
            'email' => 'vendor@gmail.com',
            'mobile_number'=>$faker->numerify('98########'),
            'password' => Hash::make('password123'),
            'user_type' => UserTypeEnum::VENDOR->value,
            'email_verified_at' => now(),
        ]);
    }
}
