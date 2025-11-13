<?php

namespace Database\Seeders;

use App\Constants\VendorContants;
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
        $user = 1;
        $just_once = true;
        while ($user <= 10) {
            $name = 'vendor' . $user.$faker->randomNumber();
            $new_user = [
                'status' => 1,
                'user_type' => UserTypeEnum::VENDOR->value,
                'uuid' => $faker->uuid(),
                'name' => $name,
                'email' => $name . '@gmail.com',
                'mobile_number' => $faker->numerify('98########'),
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ];
            if ($just_once) {
                $new_user['name'] = 'vendor00'; 
                $new_user['email'] = 'vendor@gmail.com'; 
            }
            $vendor = User::create($new_user)
            ->vendor()
            ->create([
                'verified_at' => now(),
                'store_name' => $faker->company(),
                'store_description' => $faker->realText(350),
                'location' => $faker->address(),
                'country' => 'Nepal',
                'state' => $faker->state(),
                'district' => $faker->city(),
                'municipality' => $faker->streetAddress(),
                'postal_code' => $faker->postcode(),
                'bank_name' => $faker->company(),
                'bank_account_holder_name' => $faker->name(),
                'bank_account_number' => $faker->creditCardNumber(),
            ]);
            $image = public_path('assets/img/company-registration-certificate.jpg');
            clone($vendor)->addMedia($image)->preservingOriginal()->toMediaCollection(VendorContants::VENDOR_BUSINESS_LICENSE);
            clone($vendor)->addMedia($image)->preservingOriginal()->toMediaCollection(VendorContants::VENDOR_CITIZENSHIP_CARD);
            clone($vendor)->addMedia($image)->preservingOriginal()->toMediaCollection(VendorContants::VENDOR_TAX_CERTIFICATE);
            $user++;
            $just_once = false;
        }
        $user = 1;
        while ($user <= 10) {
            $name = 'user' . $faker->randomNumber().$user;
            $vendor = User::create([
                'status' => 1,
                'user_type' => UserTypeEnum::USER->value,
                'uuid' => $faker->uuid(),
                'name' => $name,
                'email' => $name . '@gmail.com',
                'mobile_number' => $faker->numerify('98########'),
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]);
            $user++;
        }
    }
}
