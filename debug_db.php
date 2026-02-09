<?php

use Illuminate\Support\Facades\DB;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Existing Academic Years:\n";
$years = AcademicYear::all();
foreach ($years as $year) {
    echo "ID: {$year->id}, Name: {$year->name}, Status: {$year->status->value}, Financial: {$year->financial_status}\n";
}

echo "\nIndexes on academic_years:\n";
$indexes = DB::select("PRAGMA index_list('academic_years')");
foreach ($indexes as $index) {
    echo "Index: {$index->name}, Unique: {$index->unique}, Partial: {$index->partial}\n";
    $info = DB::select("PRAGMA index_info('{$index->name}')");
    foreach ($info as $col) {
        echo "  - Column: {$col->name}\n";
    }
}
