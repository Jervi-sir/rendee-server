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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('patient_id')->nullable()->constrained('patients')->onDelete('set null');
            $table->text('bookable_type')->nullable(); // doctor, center
            $table->unsignedBigInteger('bookable_id');
            $table->foreignId('center_service_id')->nullable()->constrained('center_services')->onDelete('set null');
            $table->foreignId('doctor_service_id')->nullable()->constrained('doctor_services')->onDelete('set null');
            $table->foreignId('doctor_schedule_id')->nullable()->constrained('doctor_schedules')->onDelete('set null');
            $table->foreignId('center_working_hour_id')->nullable()->constrained('center_working_hours')->onDelete('set null');
            $table->string('patient_name');
            $table->string('patient_phone');
            $table->date('booking_date');
            $table->time('booking_time');
            $table->string('status_code')->nullable();
            $table->boolean('is_center')->default(false);
            $table->date('proposed_date')->nullable();
            $table->time('proposed_time')->nullable();
            $table->boolean('has_pending_proposal')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['bookable_type', 'bookable_id']);
            $table->foreign('status_code')->references('code')->on('statuses')->nullOnDelete();

            $table->index(['booking_date', 'status_code']);
                        $table->index(['patient_id', 'status_code']);
        });
        Schema::create('booking_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->string('status_code')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->foreign('status_code')->references('code')->on('statuses')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('booking_histories');
    }
};
