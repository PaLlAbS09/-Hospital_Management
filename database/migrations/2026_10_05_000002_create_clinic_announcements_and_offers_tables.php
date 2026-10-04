<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('clinic_announcements')) {
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

                // The existing hospital tables store plain unsigned integer keys
                // without foreign constraints, so the new tables match that style.
                $table->index('clinic_id');
                $table->index('doctor_id');
            });
        }

        if (! Schema::hasTable('clinic_offers')) {
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

                $table->index('clinic_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_offers');
        Schema::dropIfExists('clinic_announcements');
    }
};
