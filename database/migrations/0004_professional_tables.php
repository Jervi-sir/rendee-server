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
        Schema::create('professionals', function (Blueprint $table) {
            $table->id();
            $table->string('profession_code');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('professional_speciality_code')->nullable();
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

            $table->softDeletes();
            $table->timestamps();

            $table->foreign('profession_code')->references('code')->on('professions')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('professional_speciality_code')->references('code')->on('professional_specialities')->nullOnDelete();
            $table->foreign('wilaya_code')->references('code')->on('wilayas')->nullOnDelete();
        });

        Schema::create('professional_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professional_id')->constrained('professionals')->onDelete('cascade');
            $table->integer('day_of_week'); // 0-6 (Sunday-Saturday)
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->index(['professional_id', 'day_of_week']);
            $table->index(['is_active']);
        });

        Schema::create('professional_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professional_id')->constrained('professionals')->onDelete('cascade');
            $table->string('service_catalog_code')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->timestamps();
            $table->foreign('service_catalog_code')->references('code')->on('service_catalogs')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('professionals');
        Schema::dropIfExists('professional_contacts');
        Schema::dropIfExists('professional_schedules');
    }
};
