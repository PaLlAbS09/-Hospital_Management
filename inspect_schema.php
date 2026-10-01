<?php

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$rows = Illuminate\Support\Facades\DB::select(
    "SELECT k.TABLE_NAME, k.COLUMN_NAME, k.REFERENCED_TABLE_NAME, k.REFERENCED_COLUMN_NAME, r.DELETE_RULE, r.UPDATE_RULE
     FROM information_schema.KEY_COLUMN_USAGE k
     JOIN information_schema.REFERENTIAL_CONSTRAINTS r
       ON k.CONSTRAINT_NAME = r.CONSTRAINT_NAME AND k.CONSTRAINT_SCHEMA = r.CONSTRAINT_SCHEMA
     WHERE k.TABLE_SCHEMA = 'hospital_management'
       AND k.REFERENCED_TABLE_NAME IS NOT NULL"
);

echo "FOREIGN KEYS\n";
if ($rows === []) {
    echo "  (none)\n";
}
foreach ($rows as $row) {
    printf(
        "  %s.%s -> %s.%s (ON DELETE %s / ON UPDATE %s)\n",
        $row->TABLE_NAME,
        $row->COLUMN_NAME,
        $row->REFERENCED_TABLE_NAME,
        $row->REFERENCED_COLUMN_NAME,
        $row->DELETE_RULE,
        $row->UPDATE_RULE
    );
}

echo "\nINDEXES\n";
foreach (['clinics', 'doctors', 'patients', 'appointments', 'clinic_schedules', 'doctor_session_logs', 'system_queries', 'admin'] as $table) {
    $indexes = Illuminate\Support\Facades\DB::select("SHOW INDEX FROM {$table}");
    $summary = [];
    foreach ($indexes as $index) {
        $summary[$index->Key_name][] = $index->Column_name.($index->Non_unique ? '' : ' (unique)');
    }
    $indexDetails = [];
    foreach ($summary as $keyName => $columns) {
        $indexDetails[] = $keyName.'='.implode(',', $columns);
    }
    echo '  '.$table.': '.implode('; ', $indexDetails)."\n";
}

echo "\nADMIN ROW\n";
$admin = Illuminate\Support\Facades\DB::table('admin')->first();
if ($admin) {
    echo '  id='.$admin->id.' name='.var_export($admin->name ?? null, true).' email='.$admin->email."\n";
    foreach (['password', 'admin', 'secret', 'admin123', 'password123', '12345678', 'Admin@123'] as $candidate) {
        if (Illuminate\Support\Facades\Hash::check($candidate, $admin->password)) {
            echo "  password matches: {$candidate}\n";
        }
    }
} else {
    echo "  (no rows)\n";
}
