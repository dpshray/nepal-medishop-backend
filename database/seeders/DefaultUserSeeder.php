<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Constants\VendorContants;
use App\Enums\UserTypeEnum;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Hash;


class DefaultUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $faker = Faker::create();
        $admin = User::create([
            'status' => 1,
            'id' => UserTypeEnum::ADMIN->value,
            'uuid' => $faker->uuid(),
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'mobile_number' => $faker->numerify('98########'),
            'password' => Hash::make('password123'),
            'user_type' => UserTypeEnum::ADMIN->value,
            'email_verified_at' => now(),
        ])
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
        clone ($admin)->addMedia($image)->preservingOriginal()->toMediaCollection(VendorContants::VENDOR_BUSINESS_LICENSE);
        clone ($admin)->addMedia($image)->preservingOriginal()->toMediaCollection(VendorContants::VENDOR_CITIZENSHIP_CARD);
        clone ($admin)->addMedia($image)->preservingOriginal()->toMediaCollection(VendorContants::VENDOR_TAX_CERTIFICATE);

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


        $vendor = User::create([
            'status' => 1,
            'user_type' => UserTypeEnum::VENDOR->value,
            'uuid' => $faker->uuid(),
            'name' => 'vendor00',
            'email' => 'vendor@gmail.com',
            'mobile_number' => $faker->numerify('98########'),
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ])
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
    }
}
