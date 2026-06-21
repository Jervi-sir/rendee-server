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
        Schema::create('centers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('center_catalog_code')->nullable();
            $table->string('license_number')->nullable();
            $table->string('phone_public')->nullable();
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('emergency_24_7')->default(false);
            $table->boolean('is_active')->default(false);
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('center_catalog_code')->references('code')->on('center_catalogs')->nullOnDelete();
        });
        Schema::create('center_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('center_id')->constrained('centers')->onDelete('cascade');
            $table->string('platform_code')->nullable();
            $table->string('url')->nullable();
            $table->timestamps();
            $table->foreign('platform_code')->references('code')->on('contact_platforms')->nullOnDelete();
        });
        Schema::create('center_working_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('center_id')->constrained('centers')->onDelete('cascade');
            $table->date('slot_date');
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->index(['center_id', 'slot_date']);
            $table->index(['is_available']);
        });
        Schema::create('center_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('center_id')->constrained('centers')->onDelete('cascade');
            $table->string('service_catalog_code')->nullable();
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
        Schema::dropIfExists('centers');
        Schema::dropIfExists('center_contacts');
        Schema::dropIfExists('center_working_hours');
        Schema::dropIfExists('center_services');
    }
};
