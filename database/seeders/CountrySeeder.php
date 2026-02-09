<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('countries')->updateOrInsert(
            ['id' => 1],
            ['name_ar' => 'المملكة العربية السعودية', 'name_en' => 'Saudi Arabia', 'code' => 'SA']
        );

        DB::table('countries')->updateOrInsert(
            ['id' => 2],
            ['name_ar' => 'مصر', 'name_en' => 'Egypt', 'code' => 'EG']
        );
    }
}
