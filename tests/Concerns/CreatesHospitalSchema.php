<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates an in-memory mirror of the existing `hospital_management`
 * MySQL schema for feature tests.
 *
 * The production database already exists and must never be re-migrated,
 * so tests build these tables on the sqlite :memory: connection instead.
 */
trait CreatesHospitalSchema
{
    protected function createHospitalSchema(): void
    {
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
            $table->string('email', 100)->unique();
            $table->string('password');
            $table->string('contact_number', 15);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('created_at')->nullable();
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
            $table->date('appointment_date');
            $table->time('appointment_time');
            $table->enum('status', ['Active', 'Cancelled_by_Patient', 'Cancelled_by_Doctor', 'Completed'])
                ->default('Active');
            $table->string('disease')->nullable();
            $table->string('allergies')->nullable();
            $table->text('prescription_details')->nullable();
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