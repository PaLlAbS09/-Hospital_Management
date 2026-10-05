<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait CreatesHospitalSchema
{
    protected function createHospitalSchema(): void
    {

        if (Schema::hasTable('admin')) {
            return;
        }

        Schema::create('admin', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });

        Schema::create('clinics', function (Blueprint $table) {
            $table->increments('clinic_id');
            $table->string('clinic_name', 100);
            $table->string('area', 100);
            $table->string('address', 255)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('email', 100)->unique();
            $table->string('password');
            $table->string('contact_number', 15);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('created_at')->nullable();
            $table->text('about')->nullable();
            $table->string('banner_image')->nullable();
        });

        Schema::create('doctors', function (Blueprint $table) {
            $table->increments('doctor_id');
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('specialization', 100);
            $table->string('email', 100)->unique();
            $table->string('contact', 15);
            $table->string('password');
            $table->timestamp('created_at')->nullable();
            $table->unsignedSmallInteger('experience_years')->nullable();
            $table->text('experience_note')->nullable();
        });

        Schema::create('patients', function (Blueprint $table) {
            $table->increments('patient_id');
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('gender', 10);
            $table->string('email', 100)->unique();
            $table->string('contact', 15);
            $table->string('password');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('clinic_schedules', function (Blueprint $table) {
            $table->increments('schedule_id');
            $table->unsignedInteger('clinic_id');
            $table->unsignedInteger('doctor_id');
            $table->date('schedule_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('patient_capacity')->default(20);
        });

        Schema::create('appointments', function (Blueprint $table) {
            $table->increments('appointment_id');
            $table->unsignedInteger('patient_id');
            $table->unsignedInteger('clinic_id');
            $table->unsignedInteger('doctor_id');
            $table->string('contact_phone', 15)->nullable();
            $table->date('appointment_date');
            $table->time('appointment_time');
            $table->enum('status', ['Active', 'Cancelled_by_Patient', 'Cancelled_by_Doctor', 'Completed', 'No_Show'])
                ->default('Active');
            $table->timestamp('checked_in_at')->nullable();
            $table->string('disease')->nullable();
            $table->string('allergies')->nullable();
            $table->text('prescription_details')->nullable();
            $table->timestamp('rating_request_sent_at')->nullable();
        });

        Schema::create('clinic_announcements', function (Blueprint $table) {
            $table->increments('announcement_id');
            $table->unsignedInteger('clinic_id');
            $table->unsignedInteger('doctor_id')->nullable();
            $table->string('department', 100);
            $table->date('joining_date')->nullable();
            $table->time('joining_time')->nullable();
            $table->text('message')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('clinic_offers', function (Blueprint $table) {
            $table->increments('offer_id');
            $table->unsignedInteger('clinic_id');
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('discount_percent');
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('doctor_reviews', function (Blueprint $table) {
            $table->increments('review_id');
            $table->unsignedInteger('appointment_id')->unique();
            $table->unsignedInteger('patient_id');
            $table->unsignedInteger('doctor_id');
            $table->unsignedInteger('clinic_id');
            $table->unsignedTinyInteger('rating');
            $table->text('experience')->nullable();
            $table->boolean('is_public')->default(true);
            $table->timestamps();
        });

        Schema::create('doctor_session_logs', function (Blueprint $table) {
            $table->increments('log_id');
            $table->unsignedInteger('doctor_id');
            $table->timestamp('login_time')->nullable();
            $table->timestamp('logout_time')->nullable();
        });

        Schema::create('system_queries', function (Blueprint $table) {
            $table->increments('query_id');
            $table->string('user_name', 100);
            $table->string('email', 100);
            $table->string('contact_number', 15);
            $table->text('message');
            $table->timestamp('submitted_at')->nullable();
        });
    }
}
