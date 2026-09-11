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
            $table->foreignId('partner_id')->nullable()->constrained('partners')->onDelete('cascade');
            $table->foreignId('partner_service_id')->nullable()->constrained('partner_services')->onDelete('cascade');
            $table->foreignId('partner_schedule_id')->nullable()->constrained('partner_schedules')->onDelete('cascade');
            $table->string('status_code')->nullable();

            $table->unsignedBigInteger('schedule_id')->nullable();
            $table->string('patient_name');
            $table->string('patient_phone');
            $table->date('booking_date');
            $table->time('booking_time');
            $table->boolean('is_center')->default(false);
            $table->date('proposed_date')->nullable();
            $table->time('proposed_time')->nullable();
            $table->boolean('has_pending_proposal')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('status_code')->references('code')->on('statuses')->nullOnDelete();
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
