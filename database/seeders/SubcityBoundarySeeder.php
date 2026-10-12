<?php

namespace Database\Seeders;

use App\Models\SubcityBoundary;
use Illuminate\Database\Seeder;

class SubcityBoundarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subcities = [
            [
                'city' => 'Addis Ababa',
                'name' => 'Bole',
                'amharic_name' => 'ቦሌ',
                'om_name' => 'Boolee',
                'center_lat' => 9.0016,
                'center_lng' => 38.7844,
                'coordinates' => [
                    [9.0300, 38.7750],
                    [9.0250, 38.8300],
                    [8.9700, 38.8250],
                    [8.9650, 38.7650],
                    [9.0050, 38.7600],
                ],
            ],
            [
                'city' => 'Addis Ababa',
                'name' => 'Kirkos',
                'amharic_name' => 'ቂርቆስ',
                'om_name' => 'Qirqoos',
                'center_lat' => 9.0120,
                'center_lng' => 38.7620,
                'coordinates' => [
                    [9.0250, 38.7500],
                    [9.0220, 38.7750],
                    [8.9950, 38.7750],
                    [8.9900, 38.7450],
                    [9.0150, 38.7450],
                ],
            ],
            [
                'city' => 'Addis Ababa',
                'name' => 'Yeka',
                'amharic_name' => 'የካ',
                'om_name' => 'Yakkā',
                'center_lat' => 9.0320,
                'center_lng' => 38.8150,
                'coordinates' => [
                    [9.0700, 38.7900],
                    [9.0600, 38.8450],
                    [9.0100, 38.8350],
                    [9.0200, 38.7850],
                ],
            ],
            [
                'city' => 'Addis Ababa',
                'name' => 'Arada',
                'amharic_name' => 'አራዳ',
                'om_name' => 'Aradaa',
                'center_lat' => 9.0350,
                'center_lng' => 38.7520,
                'coordinates' => [
                    [9.0500, 38.7400],
                    [9.0480, 38.7650],
                    [9.0220, 38.7620],
                    [9.0200, 38.7420],
                ],
            ],
            [
                'city' => 'Addis Ababa',
                'name' => 'Lideta',
                'amharic_name' => 'ልደታ',
                'om_name' => 'Liddataa',
                'center_lat' => 9.0100,
                'center_lng' => 38.7400,
                'coordinates' => [
                    [9.0200, 38.7300],
                    [9.0200, 38.7500],
                    [9.0000, 38.7500],
                    [8.9980, 38.7300],
                ],
            ],
            [
                'city' => 'Addis Ababa',
                'name' => 'Addis Ketema',
                'amharic_name' => 'አዲስ ከተማ',
                'om_name' => 'Magaalaa Haaraa',
                'center_lat' => 9.0300,
                'center_lng' => 38.7350,
                'coordinates' => [
                    [9.0450, 38.7200],
                    [9.0450, 38.7450],
                    [9.0180, 38.7420],
                    [9.0180, 38.7200],
                ],
            ],
            [
                'city' => 'Addis Ababa',
                'name' => 'Gulele',
                'amharic_name' => 'ጉለሌ',
                'om_name' => 'Gullallee',
                'center_lat' => 9.0650,
                'center_lng' => 38.7400,
                'coordinates' => [
                    [9.0900, 38.7150],
                    [9.0900, 38.7650],
                    [9.0450, 38.7600],
                    [9.0450, 38.7200],
                ],
            ],
            [
                'city' => 'Addis Ababa',
                'name' => 'Kolfe Keranio',
                'amharic_name' => 'ኮልፌ ቀራኒዮ',
                'om_name' => 'Kolfē Qaraaniyoo',
                'center_lat' => 9.0200,
                'center_lng' => 38.7100,
                'coordinates' => [
                    [9.0500, 38.6700],
                    [9.0500, 38.7200],
                    [8.9900, 38.7200],
                    [8.9900, 38.6700],
                ],
            ],
            [
                'city' => 'Addis Ababa',
                'name' => 'Nifas Silk-Lafto',
                'amharic_name' => 'ንፋስ ስልክ ላፍቶ',
                'om_name' => 'Nifas Silk Laaftoo',
                'center_lat' => 8.9750,
                'center_lng' => 38.7300,
                'coordinates' => [
                    [9.0000, 38.7050],
                    [9.0000, 38.7500],
                    [8.9400, 38.7500],
                    [8.9400, 38.7050],
                ],
            ],
            [
                'city' => 'Addis Ababa',
                'name' => 'Akaky Kaliti',
                'amharic_name' => 'አቃቂ ቃሊቲ',
                'om_name' => 'Aqaaqii Qaallittii',
                'center_lat' => 8.9200,
                'center_lng' => 38.7600,
                'coordinates' => [
                    [8.9500, 38.7300],
                    [8.9500, 38.8000],
                    [8.8700, 38.8000],
                    [8.8700, 38.7300],
                ],
            ],
            [
                'city' => 'Addis Ababa',
                'name' => 'Lemi Kura',
                'amharic_name' => 'ለሚ ኩራ',
                'om_name' => 'Lammī Kuraa',
                'center_lat' => 9.0280,
                'center_lng' => 38.8500,
                'coordinates' => [
                    [9.0600, 38.8350],
                    [9.0600, 38.8950],
                    [8.9950, 38.8950],
                    [8.9950, 38.8350],
                ],
            ],
        ];

        foreach ($subcities as $data) {
            SubcityBoundary::updateOrCreate(
                [
                    'city' => $data['city'],
                    'name' => $data['name'],
                ],
                $data
            );
        }
    }
}
