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
        // Recent Searches Table
        Schema::create('recent_searches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('label');
            $table->string('city')->nullable();
            $table->string('speciality_code')->nullable();
            $table->timestamps();
            $table->foreign('speciality_code')->references('code')->on('specialities')->nullOnDelete();
        });
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->integer('rating')->min(1)->max(5);
            $table->text('review')->nullable();
            $table->text('reviewable_type')->nullable();     // doctor,  center
            $table->unsignedBigInteger('reviewable_id');
            $table->timestamps();
            $table->index(['reviewable_type', 'reviewable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recent_searches');
        Schema::dropIfExists('ratings');
    }
};
