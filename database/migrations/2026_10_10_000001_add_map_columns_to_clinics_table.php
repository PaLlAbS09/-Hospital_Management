<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Where each clinic sits on the map. `address` is the human readable part
     * patients read, the coordinates are optional and only pin the marker more
     * precisely when a clinic knows its exact position.
     */
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            if (! Schema::hasColumn('clinics', 'address')) {
                $table->string('address', 255)->nullable()->after('area');
            }

            if (! Schema::hasColumn('clinics', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('address');
            }

            if (! Schema::hasColumn('clinics', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            foreach (['longitude', 'latitude', 'address'] as $column) {
                if (Schema::hasColumn('clinics', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
