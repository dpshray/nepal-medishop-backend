<?php

namespace Database\Seeders;

use App\Models\ProductVariantType\FormType;
use App\Models\ProductVariantType\PackageType;
use App\Models\ProductVariantType\UnitType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FormTypeSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'name' => 'Tablet',
                'package_types' => [
                    [
                        'name' => 'Strip',
                        'unit_types' => ['tablets'],
                    ],
                    [
                        'name' => 'Blister',
                        'unit_types' => ['tablets'],
                    ],
                    [
                        'name' => 'Bottle',
                        'unit_types' => ['tablets'],
                    ],
                    [
                        'name' => 'Box',
                        'unit_types' => ['tablets'],
                    ],
                ],
            ],
            [
                'name' => 'Capsule',
                'package_types' => [
                    ['name' => 'Strip', 'unit_types' => ['capsules']],
                    ['name' => 'Blister', 'unit_types' => ['capsules']],
                    ['name' => 'Bottle', 'unit_types' => ['capsules']],
                    ['name' => 'Box', 'unit_types' => ['capsules']],
                ],
            ],
            [
                'name' => 'Syrup',
                'package_types' => [
                    ['name' => 'Bottle', 'unit_types' => ['ml']],
                ],
            ],
            [
                'name' => 'Suspension',
                'package_types' => [
                    ['name' => 'Bottle', 'unit_types' => ['ml']],
                ],
            ],
            [
                'name' => 'Drops',
                'package_types' => [
                    ['name' => 'Bottle', 'unit_types' => ['ml']],
                ],
            ],
            [
                'name' => 'Injection',
                'package_types' => [
                    ['name' => 'Vial', 'unit_types' => ['ml']],
                    ['name' => 'Ampoule', 'unit_types' => ['ml']],
                    ['name' => 'Prefilled Syringe', 'unit_types' => ['ml']],
                ],
            ],
            [
                'name' => 'Cream',
                'package_types' => [
                    ['name' => 'Tube', 'unit_types' => ['g', 'ml']],
                    ['name' => 'Jar', 'unit_types' => ['g', 'ml']],
                ],
            ],
            [
                'name' => 'Ointment',
                'package_types' => [
                    ['name' => 'Tube', 'unit_types' => ['g']],
                    ['name' => 'Jar', 'unit_types' => ['g']],
                ],
            ],
            [
                'name' => 'Gel',
                'package_types' => [
                    ['name' => 'Tube', 'unit_types' => ['g', 'ml', 'pic']],
                    ['name' => 'Pump Bottle', 'unit_types' => ['g', 'ml', 'pic']],
                    ['name' => 'Pack', 'unit_types' => ['g', 'ml', 'pic']],
                ],
            ],
            [
                'name' => 'Lotion',
                'package_types' => [
                    ['name' => 'Bottle', 'unit_types' => ['ml', 'gram']],
                    ['name' => 'Pump Bottle', 'unit_types' => ['ml', 'gram']],
                    ['name' => 'Tube', 'unit_types' => ['ml', 'gram']],
                ],
            ],
            [
                'name' => 'Powder',
                'package_types' => [
                    ['name' => 'Sachet', 'unit_types' => ['g']],
                    ['name' => 'Bottle', 'unit_types' => ['g']],
                    ['name' => 'Jar', 'unit_types' => ['g']],
                    ['name' => 'Box', 'unit_types' => ['g']],
                ],
            ],
            [
                'name' => 'Inhaler',
                'package_types' => [
                    ['name' => 'Inhaler Device', 'unit_types' => ['doses']],
                ],
            ],
            [
                'name' => 'Spray',
                'package_types' => [
                    ['name' => 'Spray Bottle', 'unit_types' => ['ml']],
                ],
            ],
            [
                'name' => 'Sachet',
                'package_types' => [
                    ['name' => 'Sachet', 'unit_types' => ['g', 'ml']],
                    ['name' => 'Box', 'unit_types' => ['g', 'ml']],
                ],
            ],
            [
                'name' => 'Patch',
                'package_types' => [
                    ['name' => 'Box', 'unit_types' => ['patches']],
                    ['name' => 'Pouch', 'unit_types' => ['patches']],
                ],
            ],
            [
                'name' => 'Suppository',
                'package_types' => [
                    ['name' => 'Box', 'unit_types' => ['units']],
                    ['name' => 'Strip', 'unit_types' => ['units']],
                ],
            ],
            [
                'name' => 'Eye Drops',
                'package_types' => [
                    ['name' => 'Bottle', 'unit_types' => ['ml']],
                ],
            ],
            [
                'name' => 'Ear Drops',
                'package_types' => [
                    ['name' => 'Bottle', 'unit_types' => ['ml']],
                ],
            ],
            [
                'name' => 'Nasal Drops',
                'package_types' => [
                    ['name' => 'Bottle', 'unit_types' => ['ml']],
                ],
            ],
            [
                'name' => 'Effervescent Tablet',
                'package_types' => [
                    ['name' => 'Strip', 'unit_types' => ['tablets']],
                    ['name' => 'Box', 'unit_types' => ['tablets']],
                ],
            ],
            [
                'name' => 'Diaper',
                'package_types' => [
                    ['name' => 'Pack', 'unit_types' => ['pieces']],
                    ['name' => 'Box', 'unit_types' => ['pieces']],
                ],
            ],
            [
                'name' => 'Formula Milk',
                'package_types' => [
                    ['name' => 'Tin', 'unit_types' => ['g', 'kg']],
                    ['name' => 'Can', 'unit_types' => ['g', 'kg']],
                ],
            ],
            [
                'name' => 'Wipes',
                'package_types' => [
                    ['name' => 'Pack', 'unit_types' => ['pieces']],
                    ['name' => 'Box', 'unit_types' => ['pieces']],
                ],
            ],
            [
                'name' => 'Baby Oil',
                'package_types' => [
                    ['name' => 'Bottle', 'unit_types' => ['ml']],
                    ['name' => 'Pump Bottle', 'unit_types' => ['ml']],
                ],
            ],
            [
                'name' => 'Baby Lotion',
                'package_types' => [
                    ['name' => 'Bottle', 'unit_types' => ['ml']],
                    ['name' => 'Pump Bottle', 'unit_types' => ['ml']],
                ],
            ],
            [
                'name' => 'Liquid',
                'package_types' => [
                    ['name' => 'Bottle', 'unit_types' => ['ml', 'oz']],
                    ['name' => 'Pump Bottle', 'unit_types' => ['ml', 'oz']],
                    ['name' => 'Tube', 'unit_types' => ['ml', 'oz']],
                ],
            ],
            [
                'name' => 'Foam',
                'package_types' => [
                    ['name' => 'Bottle', 'unit_types' => ['ml', 'oz', 'gram']],
                    ['name' => 'Pump Bottle', 'unit_types' => ['ml', 'oz', 'gram']],
                    ['name' => 'Tube', 'unit_types' => ['ml', 'oz', 'gram']],
                ],
            ],
            [
                'name' => 'Soap',
                'package_types' => [
                    ['name' => 'Bar', 'unit_types' => ['gram']],
                ],
            ],
        ];

        foreach ($data as $form_type_data) {
            $form_type = FormType::firstOrCreate(
                ['name' => $form_type_data['name']],
                ['uuid' => (string) Str::uuid()]
            );

            foreach ($form_type_data['package_types'] as $package_type_data) {
                $package_type = PackageType::firstOrCreate(
                    [
                        'name' => $package_type_data['name'],
                        'form_type_id' => $form_type->id,
                    ],
                    ['uuid' => (string) Str::uuid()]
                );

                foreach ($package_type_data['unit_types'] as $unit_type_name) {
                    UnitType::firstOrCreate(
                        [
                            'name' => $unit_type_name,
                            'package_type_id' => $package_type->id,
                        ],
                        ['uuid' => (string) Str::uuid()]
                    );
                }
            }
        }
    }
}
