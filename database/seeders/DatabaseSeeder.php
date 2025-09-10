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
        $brands = [
            ['id' => 1, 'name' => 'Pfizer', 'slug' => str()->slug('Pfizer','-')],
            ['id' => 2, 'name' => 'Cipla', 'slug' => str()->slug('Cipla','-')],
            ['id' => 3, 'name' => 'Sun Pharma', 'slug' => str()->slug('Sun Pharma','-')],
            ['id' => 4, 'name' => 'GlaxoSmithKline', 'slug' => str()->slug('GlaxoSmithKline','-')],
            ['id' => 5, 'name' => 'Bayer', 'slug' => str()->slug('Bayer','-')],
        ];
        DB::table('brands')->insert($brands);


        $categories = [
            ['id' => 1, 'name' => 'Pain Relief', 'slug' => str()->slug('Pain Relief')],
            ['id' => 2, 'name' => 'Antibiotics', 'slug' => str()->slug('Antibiotics')],
            ['id' => 3, 'name' => 'Vitamins & Supplements', 'slug' => str()->slug('Vitamins & Supplements')],
            ['id' => 4, 'name' => 'Cough & Cold', 'slug' => str()->slug('Cough & Cold')],
            ['id' => 5, 'name' => 'Skin Care', 'slug' => str()->slug('Skin Care')],
            ['id' => 6, 'name' => 'Diabetes Care', 'slug' => str()->slug('Diabetes Care')],
        ];
        DB::table('categories')->insert($categories);

        $tags = [
            // Pain Relief
            ['id' => 1, 'category_id' => 1, 'name' => 'Paracetamol', 'slug' => str()->slug('Paracetamol')],
            ['id' => 2, 'category_id' => 1, 'name' => 'Ibuprofen', 'slug' => str()->slug('Ibuprofen')],
            ['id' => 3, 'category_id' => 1, 'name' => 'Muscle Relaxant', 'slug' => str()->slug('Muscle Relaxant')],
            ['id' => 4, 'category_id' => 1, 'name' => 'Aspirin', 'slug' => str()->slug('Aspirin')],

            // Antibiotics
            ['id' => 5, 'category_id' => 2, 'name' => 'Amoxicillin', 'slug' => str()->slug('Amoxicillin')],
            ['id' => 6, 'category_id' => 2, 'name' => 'Azithromycin', 'slug' => str()->slug('Azithromycin')],
            ['id' => 7, 'category_id' => 2, 'name' => 'Ciprofloxacin', 'slug' => str()->slug('Ciprofloxacin')],

            // Vitamins & Supplements
            ['id' => 8, 'category_id' => 3, 'name' => 'Vitamin C', 'slug' => str()->slug('Vitamin C')],
            ['id' => 9, 'category_id' => 3, 'name' => 'Multivitamin', 'slug' => str()->slug('Multivitamin')],
            ['id' => 10, 'category_id' => 3, 'name' => 'Calcium', 'slug' => str()->slug('Calcium')],
            ['id' => 11, 'category_id' => 3, 'name' => 'Omega-3', 'slug' => str()->slug('Omega-3')],

            // Cough & Cold
            ['id' => 12, 'category_id' => 4, 'name' => 'Syrup', 'slug' => str()->slug('Syrup')],
            ['id' => 13, 'category_id' => 4, 'name' => 'Lozenges', 'slug' => str()->slug('Lozenges')],
            ['id' => 14, 'category_id' => 4, 'name' => 'Decongestant', 'slug' => str()->slug('Decongestant')],
            ['id' => 15, 'category_id' => 4, 'name' => 'Antihistamine', 'slug' => str()->slug('Antihistamine')],

            // Skin Care
            ['id' => 16, 'category_id' => 5, 'name' => 'Anti-fungal', 'slug' => str()->slug('Anti-fungal')],
            ['id' => 17, 'category_id' => 5, 'name' => 'Acne Treatment', 'slug' => str()->slug('Acne Treatment')],
            ['id' => 18, 'category_id' => 5, 'name' => 'Moisturizer', 'slug' => str()->slug('Moisturizer')],
            ['id' => 19, 'category_id' => 5, 'name' => 'Sunscreen', 'slug' => str()->slug('Sunscreen')],

            // Diabetes Care
            ['id' => 20, 'category_id' => 6, 'name' => 'Insulin', 'slug' => str()->slug('Insulin')],
            ['id' => 21, 'category_id' => 6, 'name' => 'Glucose Monitor', 'slug' => str()->slug('Glucose Monitor')],
            ['id' => 22, 'category_id' => 6, 'name' => 'Metformin', 'slug' => str()->slug('Metformin')],
            ['id' => 23, 'category_id' => 6, 'name' => 'Test Strips', 'slug' => str()->slug('Test Strips')],
        ];

        DB::table('tags')->insert($tags);
    }
}
