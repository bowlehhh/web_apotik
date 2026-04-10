<?php

namespace Database\Seeders;

use App\Models\Medicine;
use Illuminate\Database\Seeder;

class MedicineSeeder extends Seeder
{
    public function run(): void
    {
        $medicines = [
            [
                'name' => 'Amoxicillin',
                'barcode' => '8991000000001',
                'trade_name' => 'Amoxsan',
                'dosage' => '500 mg',
                'category' => 'Antibiotik',
                'stock' => 120,
                'buy_price' => 22000,
                'sell_price' => 28000,
                'unit' => 'kapsul',
            ],
            [
                'name' => 'Paracetamol',
                'barcode' => '8991000000002',
                'trade_name' => 'Sanmol',
                'dosage' => '500 mg',
                'category' => 'Analgesik',
                'stock' => 250,
                'buy_price' => 9000,
                'sell_price' => 12500,
                'unit' => 'tablet',
            ],
            [
                'name' => 'Cetirizine',
                'barcode' => '8991000000003',
                'trade_name' => 'Intrizin',
                'dosage' => '10 mg',
                'category' => 'Antihistamin',
                'stock' => 80,
                'buy_price' => 14000,
                'sell_price' => 18500,
                'unit' => 'tablet',
            ],
            [
                'name' => 'Omeprazole',
                'barcode' => '8991000000004',
                'trade_name' => 'Omezol',
                'dosage' => '20 mg',
                'category' => 'Gastrointestinal',
                'stock' => 60,
                'buy_price' => 25000,
                'sell_price' => 31000,
                'unit' => 'kapsul',
            ],
            [
                'name' => 'Vitamin C',
                'barcode' => '8991000000005',
                'trade_name' => 'Ceevit',
                'dosage' => '1000 mg',
                'category' => 'Suplemen',
                'stock' => 40,
                'buy_price' => 17500,
                'sell_price' => 22000,
                'unit' => 'tablet',
            ],
            [
                'name' => 'Ibuprofen',
                'barcode' => '8991000000006',
                'trade_name' => 'Proris',
                'dosage' => '400 mg',
                'category' => 'Antiinflamasi',
                'stock' => 0,
                'buy_price' => 14500,
                'sell_price' => 19500,
                'unit' => 'tablet',
            ],
            [
                'name' => 'Metformin',
                'barcode' => '8991000000007',
                'trade_name' => 'Glucophage',
                'dosage' => '500 mg',
                'category' => 'Antidiabetik',
                'stock' => 0,
                'buy_price' => 22500,
                'sell_price' => 27000,
                'unit' => 'tablet',
            ],
        ];

        foreach ($medicines as $medicine) {
            Medicine::query()->updateOrCreate(
                ['barcode' => $medicine['barcode']],
                $medicine
            );
        }
    }
}
