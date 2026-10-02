<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (! Schema::hasColumn('appointments', 'contact_phone')) {
                $table->string('contact_phone', 15)->nullable()->after('doctor_id');
            }

            if (! Schema::hasColumn('appointments', 'checked_in_at')) {
                $table->timestamp('checked_in_at')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'checked_in_at')) {
                $table->dropColumn('checked_in_at');
            }

            if (Schema::hasColumn('appointments', 'contact_phone')) {
                $table->dropColumn('contact_phone');
            }
        });
    }
};
