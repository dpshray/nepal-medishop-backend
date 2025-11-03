<?php

namespace Database\Seeders;

use App\Models\HealthCondition;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HealthConditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Schema::hasTable('health_conditions')) {
            $healthConditions = [
                [
                    "name" => "Hormonal Care",
                    "description" => "Products that help regulate hormones, including thyroid, adrenal, and reproductive hormones.",
                    "image" => public_path('assets/img/health_condition/hormonal-balance-icon-design-vector.jpg')
                ],
                [
                    "name" => "Skin Care",
                    "description" => "Medications, creams, and supplements for acne, eczema, psoriasis, and overall skin health.",
                    "image" => public_path('assets/img/health_condition/skin care.png')
                ],
                [
                    "name" => "Immunity Boosters",
                    "description" => "Vitamins, minerals, and herbal supplements to strengthen the immune system.",
                    "image" => public_path('assets/img/health_condition/immunity booster.jpg')
                ],
                [
                    "name" => "Cough & Cold",
                    "description" => "Medications for cough, cold, flu, and respiratory infections.",
                    "image" => public_path('assets/img/health_condition/cough and cold.jpeg')
                ],
                [
                    "name" => "Eye Care",
                    "description" => "Supplements and medicines to maintain eye health and treat eye-related conditions.",
                    "image" => public_path('assets/img/health_condition/eye care.png')
                ],
                [
                    "name" => "Kidney & Liver Care",
                    "description" => "Products to support kidney and liver function and detoxification.",
                    "image" => public_path('assets/img/health_condition/liver and kidney.jpeg')
                ],
                [
                    "name" => "Vitamins & Supplements",
                    "description" => null,
                    "image" => public_path('assets/img/health_condition/vitamin suppliment.png')
                ],
                [
                    "name" => "Heart & Blood Pressure",
                    "description" => "Medications and supplements for cardiovascular health, blood pressure, and heart function.",
                    "image" => public_path('assets/img/health_condition/heart and bloos presure.png')
                ],
                [
                    "name" => "Diabetes Care",
                    "description" => "Medications and supplements for blood sugar control and diabetes management.",
                    "image" => public_path('assets/img/health_condition/diabetic care.png')
                ],
                [
                    "name" => "Pain Relief & Fever",
                    "description" => "Analgesics, antipyretics, and other medications to relieve pain and reduce fever.",
                    "image" => public_path('assets/img/health_condition/pain relief and fever.png')
                ],
                [
                    "name" => "Bone & Joint Health",
                    "description" => "Supplements and medicines to support bones, joints, and prevent arthritis or osteoporosis.",
                    "image" => public_path('assets/img/health_condition/bone and joint.png')
                ],
                [
                    "name" => "Oral Care",
                    "description" => "Products for dental hygiene, tooth decay prevention, gum health, and oral infections.",
                    "image" => public_path('assets/img/health_condition/oral.png')
                ],
                [
                    "name" => "Sleep & Relaxation",
                    "description" => "Products to aid sleep, relaxation, and reduce insomnia or stress.",
                    "image" => public_path('assets/img/health_condition/sleep.png')
                ],
                [
                    "name" => "Anti-diabetic Medications",
                    "description" => "Prescription medicines for managing type 1 and type 2 diabetes.",
                    "image" => public_path('assets/img/health_condition/diabetic care.png')
                ],
                [
                    "name" => "Vaccines & Immunizations",
                    "description" => null,
                    "image" => public_path('assets/img/health_condition/vaccine and immunization.png')
                ]
            ];
            foreach ($healthConditions as $hc) {
                HealthCondition::create([
                    'name' => $hc['name'],
                    'description' => $hc['description'],
                    'slug' => str()->slug($hc['name'])
                ])
                ->addMedia($hc['image'])
                ->preservingOriginal()
                ->toMediaCollection(HealthCondition::HEALTH_CONDITION_IMAGE);
            }
        }

    }
}
