<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('partner_types', function (Blueprint $table) {
            $table->string('code')->primary();
            $table->string('en');
            $table->string('fr');
            $table->string('ar');
            $table->timestamps();
        });

        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('partner_type_code')->nullable();
            $table->string('name')->nullable();
            $table->string('profession_code')->nullable();
            $table->string('professional_speciality_code')->nullable();
            $table->string('center_catalog_code')->nullable();
            $table->string('wilaya_code')->nullable();

            $table->string('license_number')->nullable();
            $table->string('years_experience')->nullable();
            $table->string('phone_public')->nullable();
            $table->text('bio')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_available')->default(false);
            $table->boolean('emergency_24_7')->default(false);
            $table->boolean('is_on_duty')->default(false);
            $table->boolean('is_active')->default(true);

            $table->softDeletes();
            $table->timestamps();

            $table->foreign('partner_type_code')->references('code')->on('partner_types')->nullOnDelete();
            $table->foreign('profession_code')->references('code')->on('professions')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('professional_speciality_code')->references('code')->on('professional_specialities')->nullOnDelete();
            $table->foreign('center_catalog_code')->references('code')->on('center_catalogs')->nullOnDelete();
            $table->foreign('wilaya_code')->references('code')->on('wilayas')->nullOnDelete();
        });

        Schema::create('partner_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained('partners')->onDelete('cascade');
            $table->integer('day_of_week'); // 0-6 (Sunday-Saturday)
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->index(['partner_id', 'day_of_week']);
            $table->index(['is_active']);
        });

        Schema::create('partner_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained('partners')->onDelete('cascade');
            $table->string('service_catalog_code')->nullable();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('service_catalog_code')->references('code')->on('service_catalogs')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_services');
        Schema::dropIfExists('partner_schedules');
        Schema::dropIfExists('partners');
        Schema::dropIfExists('partner_types');
    }
};
