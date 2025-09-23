<?php

namespace Database\Seeders;

use App\Enums\UserTypeEnum;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        info('Removing all previous images...');
        $path = storage_path('app/public');
        $directories = File::directories($path);
        foreach ($directories as $directory) {
            File::deleteDirectory($directory);
        }
        info('Images removed');

        $this->essentialsSeeders();

        $this->call([
            UserSeeder::class,
            ProductSeeder::class,
            VendorProductSeeder::class,
            FlashSaleSeeder::class,
            FlashSaleSeeder::class,
            FlashSaleSeeder::class,
            FlashSaleSeeder::class,
            FlashSaleSeeder::class,
        ]);
    }

    private function essentialsSeeders(){
        $brands = [
            ['id' => 1, 'name' => 'Pfizer', 'slug' => str()->slug('Pfizer', '-'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 2, 'name' => 'Cipla', 'slug' => str()->slug('Cipla', '-'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 3, 'name' => 'Sun Pharma', 'slug' => str()->slug('Sun Pharma', '-'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 4, 'name' => 'GlaxoSmithKline', 'slug' => str()->slug('GlaxoSmithKline', '-'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 5, 'name' => 'Bayer', 'slug' => str()->slug('Bayer', '-'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 6, 'name' => 'Merck', 'slug' => str()->slug('Merck', '-'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 7, 'name' => 'Novartis', 'slug' => str()->slug('Novartis', '-'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 8, 'name' => 'Abbott', 'slug' => str()->slug('Abbott', '-'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 9, 'name' => 'Johnson & Johnson', 'slug' => str()->slug('Johnson & Johnson', '-'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 10, 'name' => 'Roche', 'slug' => str()->slug('Roche', '-'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 11, 'name' => 'AstraZeneca', 'slug' => str()->slug('AstraZeneca', '-'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 12, 'name' => 'Bristol-Myers Squibb', 'slug' => str()->slug('Bristol-Myers Squibb', '-'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 13, 'name' => 'Sanofi', 'slug' => str()->slug('Sanofi', '-'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 14, 'name' => 'Takeda', 'slug' => str()->slug('Takeda', '-'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 15, 'name' => 'Gilead Sciences', 'slug' => str()->slug('Gilead Sciences', '-'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 16, 'name' => 'Eli Lilly', 'slug' => str()->slug('Eli Lilly', '-'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 17, 'name' => 'Boehringer Ingelheim', 'slug' => str()->slug('Boehringer Ingelheim', '-'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 18, 'name' => 'Teva Pharmaceuticals', 'slug' => str()->slug('Teva Pharmaceuticals', '-'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 19, 'name' => 'Mylan', 'slug' => str()->slug('Mylan', '-'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 20, 'name' => 'Fresenius Kabi', 'slug' => str()->slug('Fresenius Kabi', '-'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 21, 'name' => 'Mallinckrodt', 'slug' => str()->slug('Mallinckrodt', '-'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 22, 'name' => 'Sandoz', 'slug' => str()->slug('Sandoz', '-'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 23, 'name' => 'Viatris', 'slug' => str()->slug('Viatris', '-'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 24, 'name' => 'Biocon', 'slug' => str()->slug('Biocon', '-'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
            ['id' => 25, 'name' => 'Sunovion', 'slug' => str()->slug('Sunovion', '-'), 'is_featured' => fake()->boolean(50), 'is_popular' => fake()->boolean(50)],
        ];

        DB::table('brands')->insert($brands);



        $categories = [
            ['id' => 1, 'name' => 'Pain Relief', 'slug' => str()->slug('Pain Relief')],
            ['id' => 2, 'name' => 'Antibiotics', 'slug' => str()->slug('Antibiotics')],
            ['id' => 3, 'name' => 'Vitamins & Supplements', 'slug' => str()->slug('Vitamins & Supplements')],
            ['id' => 4, 'name' => 'Cough & Cold', 'slug' => str()->slug('Cough & Cold')],
            ['id' => 5, 'name' => 'Skin Care', 'slug' => str()->slug('Skin Care')],
            ['id' => 6, 'name' => 'Diabetes Care', 'slug' => str()->slug('Diabetes Care')],
            ['id' => 7, 'name' => 'Heart Care', 'slug' => str()->slug('Heart Care')],
            ['id' => 8, 'name' => 'Digestive Health', 'slug' => str()->slug('Digestive Health')],
            ['id' => 9, 'name' => 'Eye Care', 'slug' => str()->slug('Eye Care')],
            ['id' => 10, 'name' => 'Ear, Nose & Throat', 'slug' => str()->slug('Ear, Nose & Throat')],
            ['id' => 11, 'name' => 'Immunity Boosters', 'slug' => str()->slug('Immunity Boosters')],
            ['id' => 12, 'name' => 'Neurological Care', 'slug' => str()->slug('Neurological Care')],
            ['id' => 13, 'name' => 'Weight Management', 'slug' => str()->slug('Weight Management')],
            ['id' => 14, 'name' => 'Hormonal Care', 'slug' => str()->slug('Hormonal Care')],
            ['id' => 15, 'name' => 'Kidney & Liver Care', 'slug' => str()->slug('Kidney & Liver Care')],
        ];

        DB::table('categories')->insert($categories);


        $tags = [
            // Pain Relief (Category 1)
            ['id' => 1, 'name' => 'Paracetamol', 'slug' => str()->slug('Paracetamol')],
            ['id' => 2, 'name' => 'Ibuprofen', 'slug' => str()->slug('Ibuprofen')],
            ['id' => 3, 'name' => 'Muscle Relaxant', 'slug' => str()->slug('Muscle Relaxant')],
            ['id' => 4, 'name' => 'Aspirin', 'slug' => str()->slug('Aspirin')],
            ['id' => 5, 'name' => 'Naproxen', 'slug' => str()->slug('Naproxen')],

            // Antibiotics (Category 2)
            ['id' => 6, 'name' => 'Amoxicillin', 'slug' => str()->slug('Amoxicillin')],
            ['id' => 7, 'name' => 'Azithromycin', 'slug' => str()->slug('Azithromycin')],
            ['id' => 8, 'name' => 'Ciprofloxacin', 'slug' => str()->slug('Ciprofloxacin')],
            ['id' => 9, 'name' => 'Doxycycline', 'slug' => str()->slug('Doxycycline')],
            ['id' => 10, 'name' => 'Levofloxacin', 'slug' => str()->slug('Levofloxacin')],

            // Vitamins & Supplements (Category 3)
            ['id' => 11, 'name' => 'Vitamin C', 'slug' => str()->slug('Vitamin C')],
            ['id' => 12, 'name' => 'Multivitamin', 'slug' => str()->slug('Multivitamin')],
            ['id' => 13, 'name' => 'Calcium', 'slug' => str()->slug('Calcium')],
            ['id' => 14, 'name' => 'Omega-3', 'slug' => str()->slug('Omega-3')],
            ['id' => 15, 'name' => 'Vitamin D', 'slug' => str()->slug('Vitamin D')],

            // Cough & Cold (Category 4)
            ['id' => 16, 'name' => 'Syrup', 'slug' => str()->slug('Syrup')],
            ['id' => 17, 'name' => 'Lozenges', 'slug' => str()->slug('Lozenges')],
            ['id' => 18, 'name' => 'Decongestant', 'slug' => str()->slug('Decongestant')],
            ['id' => 19, 'name' => 'Antihistamine', 'slug' => str()->slug('Antihistamine')],
            ['id' => 20, 'name' => 'Nasal Spray', 'slug' => str()->slug('Nasal Spray')],

            // Skin Care (Category 5)
            ['id' => 21, 'name' => 'Anti-fungal', 'slug' => str()->slug('Anti-fungal')],
            ['id' => 22, 'name' => 'Acne Treatment', 'slug' => str()->slug('Acne Treatment')],
            ['id' => 23, 'name' => 'Moisturizer', 'slug' => str()->slug('Moisturizer')],
            ['id' => 24, 'name' => 'Sunscreen', 'slug' => str()->slug('Sunscreen')],
            ['id' => 25, 'name' => 'Anti-aging', 'slug' => str()->slug('Anti-aging')],

            // Diabetes Care (Category 6)
            ['id' => 26, 'name' => 'Insulin', 'slug' => str()->slug('Insulin')],
            ['id' => 27, 'name' => 'Glucose Monitor', 'slug' => str()->slug('Glucose Monitor')],
            ['id' => 28, 'name' => 'Metformin', 'slug' => str()->slug('Metformin')],
            ['id' => 29, 'name' => 'Test Strips', 'slug' => str()->slug('Test Strips')],
            ['id' => 30, 'name' => 'Sitagliptin', 'slug' => str()->slug('Sitagliptin')],

            // Heart Care (Category 7)
            ['id' => 31, 'name' => 'Statins', 'slug' => str()->slug('Statins')],
            ['id' => 32, 'name' => 'Beta Blockers', 'slug' => str()->slug('Beta Blockers')],
            ['id' => 33, 'name' => 'ACE Inhibitors', 'slug' => str()->slug('ACE Inhibitors')],

            // Digestive Health (Category 8)
            ['id' => 34, 'name' => 'Antacids', 'slug' => str()->slug('Antacids')],
            ['id' => 35, 'name' => 'Probiotics', 'slug' => str()->slug('Probiotics')],

            // Eye Care (Category 9)
            ['id' => 36, 'name' => 'Eye Drops', 'slug' => str()->slug('Eye Drops')],
            ['id' => 37, 'name' => 'Lubricating Drops', 'slug' => str()->slug('Lubricating Drops')],

            // Ear, Nose & Throat (Category 10)
            ['id' => 38, 'name' => 'Ear Drops', 'slug' => str()->slug('Ear Drops')],
            ['id' => 39, 'name' => 'Throat Spray', 'slug' => str()->slug('Throat Spray')],

            // Immunity Boosters (Category 11)
            ['id' => 40, 'name' => 'Herbal Supplements', 'slug' => str()->slug('Herbal Supplements')],
            ['id' => 41, 'name' => 'Vitamin B Complex', 'slug' => str()->slug('Vitamin B Complex')],

            // Neurological Care (Category 12)
            ['id' => 42, 'name' => 'Pain Relievers', 'slug' => str()->slug('Pain Relievers')],

            // Weight Management (Category 13)
            ['id' => 43, 'name' => 'Fat Burners', 'slug' => str()->slug('Fat Burners')],

            // Hormonal Care (Category 14)
            ['id' => 44, 'name' => 'Thyroid Supplements', 'slug' => str()->slug('Thyroid Supplements')],

            // Kidney & Liver Care (Category 15)
            ['id' => 45, 'name' => 'Liver Detox', 'slug' => str()->slug('Liver Detox')],
            ['id' => 46, 'name' => 'Kidney Support', 'slug' => str()->slug('Kidney Support')],
        ];

        DB::table('tags')->insert($tags);

        $faker = Faker::create();
        User::create([
            'status' => 1,
            'id' => UserTypeEnum::ADMIN->value,
            'uuid' => $faker->uuid(),
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'mobile_number' => $faker->numerify('98########'),
            'password' => Hash::make('password123'),
            'user_type' => UserTypeEnum::ADMIN->value,
            'email_verified_at' => now(),
        ]);
        User::create([
            'status' => 1,
            'id' => UserTypeEnum::USER->value,
            'uuid' => $faker->uuid(),
            'name' => 'user00',
            'email' => 'user@gmail.com',
            'mobile_number' => $faker->numerify('98########'),
            'password' => Hash::make('password123'),
            'user_type' => UserTypeEnum::USER->value,
            'email_verified_at' => now(),
        ]);
    }
}
