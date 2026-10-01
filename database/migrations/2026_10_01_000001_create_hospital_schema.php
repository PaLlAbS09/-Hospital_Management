<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('admin')) {
            Schema::create('admin', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('email')->unique();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('clinics')) {
            Schema::create('clinics', function (Blueprint $table) {
                $table->increments('clinic_id');
                $table->string('clinic_name', 100);
                $table->string('area', 100);
                $table->string('email', 100)->unique();
                $table->string('password');
                $table->string('contact_number', 15);
                $table->string('status', 20)->default('pending');
                $table->rememberToken();
                $table->timestamp('created_at')->nullable();
                $table->string('email_verified_at')->nullable();
            });
        }

        if (! Schema::hasTable('doctors')) {
            Schema::create('doctors', function (Blueprint $table) {
                $table->increments('doctor_id');
                $table->string('first_name', 50);
                $table->string('last_name', 50);
                $table->string('specialization', 100);
                $table->string('email', 100)->unique();
                $table->string('contact', 15);
                $table->string('password');
                $table->rememberToken();
                $table->timestamp('created_at')->nullable();
                $table->string('email_verified_at')->nullable();
            });
        }

        if (! Schema::hasTable('patients')) {
            Schema::create('patients', function (Blueprint $table) {
                $table->increments('patient_id');
                $table->string('first_name', 50);
                $table->string('last_name', 50);
                $table->string('gender', 10);
                $table->string('email', 100)->unique();
                $table->string('contact', 15);
                $table->string('password');
                $table->rememberToken();
                $table->timestamp('created_at')->nullable();
                $table->string('email_verified_at')->nullable();
            });
        }

        if (! Schema::hasTable('clinic_schedules')) {
            Schema::create('clinic_schedules', function (Blueprint $table) {
                $table->increments('schedule_id');
                $table->unsignedInteger('clinic_id');
                $table->unsignedInteger('doctor_id');
                $table->date('schedule_date');
                $table->time('start_time');
                $table->time('end_time');
                $table->unsignedInteger('patient_capacity')->default(20);

                $table->foreign('clinic_id')->references('clinic_id')->on('clinics')->cascadeOnDelete();
                $table->foreign('doctor_id')->references('doctor_id')->on('doctors')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('appointments')) {
            Schema::create('appointments', function (Blueprint $table) {
                $table->increments('appointment_id');
                $table->unsignedInteger('patient_id');
                $table->unsignedInteger('clinic_id');
                $table->unsignedInteger('doctor_id');
                $table->date('appointment_date');
                $table->time('appointment_time');
                $table->string('status', 30)->default('Active');
                $table->string('disease')->nullable();
                $table->string('allergies')->nullable();
                $table->text('prescription_details')->nullable();

                $table->foreign('patient_id')->references('patient_id')->on('patients')->cascadeOnDelete();
                $table->foreign('clinic_id')->references('clinic_id')->on('clinics')->cascadeOnDelete();
                $table->foreign('doctor_id')->references('doctor_id')->on('doctors')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('doctor_session_logs')) {
            Schema::create('doctor_session_logs', function (Blueprint $table) {
                $table->increments('log_id');
                $table->unsignedInteger('doctor_id');
                $table->timestamp('login_time')->nullable();
                $table->timestamp('logout_time')->nullable();

                $table->foreign('doctor_id')->references('doctor_id')->on('doctors')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('system_queries')) {
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

    public function down(): void
    {
        Schema::dropIfExists('system_queries');
        Schema::dropIfExists('doctor_session_logs');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('clinic_schedules');
        Schema::dropIfExists('patients');
        Schema::dropIfExists('doctors');
        Schema::dropIfExists('clinics');
        Schema::dropIfExists('admin');
    }
};
