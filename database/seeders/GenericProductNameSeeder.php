<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GenericProductNameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            "Paracetamol",
            "Ibuprofen",
            "Amoxicillin",
            "Azithromycin",
            "Metformin",
            "Omeprazole",
            "Pantoprazole",
            "Cetirizine",
            "Loratadine",
            "Losartan",
            "Amlodipine",
            "Atorvastatin",
            "Simvastatin",
            "Metoprolol",
            "Diclofenac",
            "Ciprofloxacin",
            "Doxycycline",
            "Levocetirizine",
            "Montelukast",
            "Prednisolone",
            "Ascorbic Acid",
            "Folic Acid",
            "Ferrous Sulphate",
            "Calcium Carbonate",
            "Zinc Sulphate",
            "Hydrocortisone",
            "Clopidogrel",
            "Sildenafil",
            "Insulin",
            "Rabeprazole"
        ];

        foreach ($data as $item) {
            $data = [
                'status' => true,
                'name' => $item,
                'slug' => str()->slug($item)
            ];
            DB::table('generic_product_names')->insert($data);
        }
    }
}
