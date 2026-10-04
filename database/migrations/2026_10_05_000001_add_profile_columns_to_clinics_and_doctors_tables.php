<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            if (! Schema::hasColumn('clinics', 'about')) {
                $table->text('about')->nullable()->after('contact_number');
            }

            if (! Schema::hasColumn('clinics', 'banner_image')) {
                $table->string('banner_image')->nullable()->after('about');
            }
        });

        Schema::table('doctors', function (Blueprint $table) {
            if (! Schema::hasColumn('doctors', 'experience_years')) {
                $table->unsignedSmallInteger('experience_years')->nullable()->after('specialization');
            }

            if (! Schema::hasColumn('doctors', 'experience_note')) {
                $table->text('experience_note')->nullable()->after('experience_years');
            }
        });
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            foreach (['experience_note', 'experience_years'] as $column) {
                if (Schema::hasColumn('doctors', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('clinics', function (Blueprint $table) {
            foreach (['banner_image', 'about'] as $column) {
                if (Schema::hasColumn('clinics', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
