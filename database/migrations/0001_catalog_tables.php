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
            $table->id();
            $table->string('code')->unique();
            $table->string('number')->nullable();
            $table->string('en')->nullable();
            $table->string('fr')->nullable();
            $table->string('ar')->nullable();
            $table->timestamps();
        });

        Schema::create('contact_platforms', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('en')->nullable();
            $table->string('fr')->nullable();
            $table->string('ar')->nullable();
            $table->timestamps();
        });

        Schema::create('statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
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
        Schema::dropIfExists('statuses');
        Schema::dropIfExists('contact_platforms');
        Schema::dropIfExists('wilayas');
    }
};
