<?php

namespace Database\Seeders;

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
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'mobile_number'=>$faker->numerify('98########'),
            'password' => Hash::make('password123'),
            'is_admin' => 1,
            'email_verified_at' => now(),
        ]);
        User::create([
            'name' => 'test',
            'email' => 'test@gmail.com',
            'mobile_number'=>$faker->numerify('98########'),
            'password' => Hash::make('password123'),
            'is_admin' => 0,
            'email_verified_at' => now(),
        ]);
    }
}
