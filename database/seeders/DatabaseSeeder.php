<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            // ProductSeeder::class,
        ]);
        DB::table('categories')->insert([
            [
                'title' => 'Tablets/Capsules',
                'slug' => 'tablets-capsules',
            ],
            [
                'title' => 'Syrup/Suspension',
                'slug' => 'syrup-suspension',
            ],
            [
                'title' => 'Injection',
                'slug' => 'injection',
            ],
            [
                'title' => 'Cream/Ointment',
                'slug' => 'cream-ointment',
            ],
            [
                'title' => 'Drops',
                'slug' => 'drops',
            ]
        ]);
    }
}
