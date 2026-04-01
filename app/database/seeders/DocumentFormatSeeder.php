<?php

namespace Database\Seeders;

use App\Models\DocumentFormat;
use Illuminate\Database\Seeder;

class DocumentFormatSeeder extends Seeder
{
    public function run(): void
    {
        $formats = [
            ['name' => 'UA Passport',        'country' => 'UA', 'width_mm' => 35, 'height_mm' => 45, 'dpi' => 300, 'is_active' => true, 'sort_order' => 1],
            ['name' => 'International Pass.','country' => 'UA', 'width_mm' => 35, 'height_mm' => 45, 'dpi' => 300, 'is_active' => true, 'sort_order' => 2],
            ['name' => 'ID Card UA',         'country' => 'UA', 'width_mm' => 25, 'height_mm' => 35, 'dpi' => 300, 'is_active' => true, 'sort_order' => 3],
            ['name' => 'US Passport',        'country' => 'US', 'width_mm' => 51, 'height_mm' => 51, 'dpi' => 300, 'is_active' => true, 'sort_order' => 4],
            ['name' => 'US Visa',            'country' => 'US', 'width_mm' => 51, 'height_mm' => 51, 'dpi' => 300, 'is_active' => true, 'sort_order' => 5],
            ['name' => 'EU Passport',        'country' => 'EU', 'width_mm' => 35, 'height_mm' => 45, 'dpi' => 300, 'is_active' => true, 'sort_order' => 6],
            ['name' => 'Schengen Visa',      'country' => 'EU', 'width_mm' => 35, 'height_mm' => 45, 'dpi' => 300, 'is_active' => true, 'sort_order' => 7],
        ];

        foreach ($formats as $format) {
            DocumentFormat::firstOrCreate(
                ['name' => $format['name'], 'country' => $format['country']],
                $format
            );
        }
    }
}
