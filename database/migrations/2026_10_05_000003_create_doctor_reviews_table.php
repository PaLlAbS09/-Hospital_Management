<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('doctor_reviews')) {
            Schema::create('doctor_reviews', function (Blueprint $table) {
                $table->increments('review_id');
                $table->unsignedInteger('appointment_id');
                $table->unsignedInteger('patient_id');
                $table->unsignedInteger('doctor_id');
                $table->unsignedInteger('clinic_id');
                $table->unsignedTinyInteger('rating');
                $table->text('experience')->nullable();
                $table->boolean('is_public')->default(true);
                $table->timestamps();

                $table->unique('appointment_id');
                $table->index('patient_id');
                $table->index('doctor_id');
                $table->index('clinic_id');
            });
        }

        Schema::table('appointments', function (Blueprint $table) {
            if (! Schema::hasColumn('appointments', 'rating_request_sent_at')) {
                $table->timestamp('rating_request_sent_at')->nullable()->after('prescription_details');
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'rating_request_sent_at')) {
                $table->dropColumn('rating_request_sent_at');
            }
        });

        Schema::dropIfExists('doctor_reviews');
    }
};
