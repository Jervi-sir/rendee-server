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
        Schema::create('wilayas', function (Blueprint $table) {
            $table->string('code')->primary();
            $table->string('number')->nullable();
            $table->string('en')->nullable();
            $table->string('fr')->nullable();
            $table->string('ar')->nullable();
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->timestamps();
        });

        Schema::create('communes', function (Blueprint $table) {
            $table->id();
            $table->string('wilaya_code')->nullable()->index();
            $table->string('code')->unique();
            $table->string('postal_code')->nullable();
            $table->string('en')->nullable();
            $table->string('fr')->nullable();
            $table->string('ar')->nullable();
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->timestamps();

            $table->foreign('wilaya_code')->references('code')->on('wilayas')->nullOnDelete()->cascadeOnUpdate();
        });

        Schema::create('contact_platforms', function (Blueprint $table) {
            $table->string('code')->primary();
            $table->string('en')->nullable();
            $table->string('fr')->nullable();
            $table->string('ar')->nullable();
            $table->timestamps();
        });

        Schema::create('statuses', function (Blueprint $table) {
            $table->string('code')->primary();
            $table->string('en')->nullable();
            $table->string('fr')->nullable();
            $table->string('ar')->nullable();
            $table->timestamps();
        });


        Schema::create('service_catalogs', function (Blueprint $table) {
            $table->string('code')->primary();
            $table->string('source')->nullable();
            $table->string('en')->nullable();
            $table->string('fr')->nullable();
            $table->string('ar')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_catalogs');
        Schema::dropIfExists('statuses');
        Schema::dropIfExists('contact_platforms');
        Schema::dropIfExists('communes');
        Schema::dropIfExists('wilayas');
    }
};
