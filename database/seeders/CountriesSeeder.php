<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Add basic countries
        $countries = [
            ['id' => 1, 'name_ar' => 'المملكة العربية السعودية', 'name_en' => 'Saudi Arabia', 'code' => 'SA'],
            ['id' => 2, 'name_ar' => 'جمهورية مصر العربية', 'name_en' => 'Egypt', 'code' => 'EG'],
            ['id' => 3, 'name_ar' => 'الإمارات العربية المتحدة', 'name_en' => 'United Arab Emirates', 'code' => 'AE'],
            ['id' => 4, 'name_ar' => 'المملكة الأردنية الهاشمية', 'name_en' => 'Jordan', 'code' => 'JO'],
            ['id' => 5, 'name_ar' => 'الجمهورية اليمنية', 'name_en' => 'Yemen', 'code' => 'YE'],
            ['id' => 6, 'name_ar' => 'دولة الكويت', 'name_en' => 'Kuwait', 'code' => 'KW'],
            ['id' => 7, 'name_ar' => 'مملكة البحرين', 'name_en' => 'Bahrain', 'code' => 'BH'],
            ['id' => 8, 'name_ar' => 'سلطنة عمان', 'name_en' => 'Oman', 'code' => 'OM'],
            ['id' => 9, 'name_ar' => 'دولة قطر', 'name_en' => 'Qatar', 'code' => 'QA'],
        ];

        DB::table('countries')->insertOrIgnore($countries);
    }
}
