<?php

namespace Database\Seeders;

use App\Models\Product\Service\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'name' => 'Complete Blood Count (CBC)',
                'description' => 'Measures red blood cells, white blood cells, hemoglobin, and platelets to detect infections and anemia.',
                'test_requirements' => 'No fasting required. Stay hydrated before test.',
                'price' => 1200,
                'image' => public_path('assets/img/services/Complete-Blood-Count-CBC.jpeg')
            ],
            [
                'name' => 'Thyroid Function Test',
                'description' => 'Checks thyroid hormone levels (T3, T4, TSH) to diagnose thyroid disorders.',
                'test_requirements' => 'Fasting not required. Morning sample preferred.',
                'price' => 1800,
                'image' => public_path('assets/img/services/Thyroid Function Test.jpg'),
                'discount_percent' => rand(1,2)
            ],
            [
                'name' => 'Liver Function Test (LFT)',
                'description' => 'Examines liver enzymes and bilirubin levels to assess liver health.',
                'test_requirements' => 'Fast for 8 hours. Avoid alcohol 24 hours before test.',
                'price' => 1500,
                'image' => public_path('assets/img/services/liver-function-tests-blood-that-600nw-2587246243.webp')
            ],
            [
                'name' => 'Kidney Function Test (KFT)',
                'description' => 'Evaluates creatinine and urea levels to check kidney performance.',
                'test_requirements' => 'Drink plenty of water before test.',
                'price' => 1400,
                'image' => public_path('assets/img/services/Kidney Function Test (KFT).webp')
            ],
            [
                'name' => 'Lipid Profile',
                'description' => 'Measures cholesterol and triglycerides to evaluate heart disease risk.',
                'test_requirements' => 'Fast for 9–12 hours before test.',
                'price' => 1600,
                'image' => public_path('assets/img/services/Lipid Profile.jpg'),
                'discount_percent' => rand(1, 2)
            ],
            [
                'name' => 'Blood Sugar (Fasting)',
                'description' => 'Determines blood glucose level to identify diabetes.',
                'test_requirements' => '8 hours fasting required.',
                'price' => 400,
                'image' => public_path('assets/img/services/Blood Sugar (Fasting).jpg')
            ],
            [
                'name' => 'Vitamin D Test',
                'description' => 'Checks vitamin D levels to detect deficiency and bone-related problems.',
                'test_requirements' => 'No fasting required.',
                'price' => 2200,
                'image' => public_path('assets/img/services/Vitamin D Test.webp')
            ],
            [
                'name' => 'Urine Routine Examination',
                'description' => 'Analyzes urine for infections, dehydration, and kidney conditions.',
                'test_requirements' => 'Morning sample recommended.',
                'price' => 350,
                'image' => public_path('assets/img/services/Urine Routine Examination.jpg')
            ],
            [
                'name' => 'COVID-19 PCR Test',
                'description' => 'Detects active COVID-19 infection using nasal or throat sample.',
                'test_requirements' => 'Avoid eating or drinking 30 minutes before test.',
                'price' => 2500,
                'image' => public_path('assets/img/services/COVID-19 PCR Test.jpg')
            ],
            [
                'name' => 'Electrocardiogram (ECG)',
                'description' => 'Records electrical activity of the heart to detect heart conditions.',
                'test_requirements' => 'Wear comfortable clothing. Avoid lotions on chest area.',
                'price' => 900,
                'image' => public_path('assets/img/services/Electrocardiogram (ECG).jpg'),
                'discount_percent' => rand(1, 2)
            ]
        ];
        DB::transaction(function () use($services){ 
            foreach ($services as $service) {
                $data = [
                    'name' => $service['name'],
                    'description' => $service['description'],
                    'test_requirements' => $service['test_requirements'],
                    'price' => $service['price'],
                    'discount_percent' => array_key_exists('discount_percent', $service) ? $service['discount_percent'] : null
                ];
                Service::create($data)->addMedia($service['image'])
                    ->preservingOriginal()
                    ->toMediaCollection(Service::SERVICE_MEDIA);
            }
        });
    }
}
