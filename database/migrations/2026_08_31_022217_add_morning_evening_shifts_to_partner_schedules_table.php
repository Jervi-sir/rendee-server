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
        Schema::table('partner_schedules', function (Blueprint $table) {
            $table->time('morning_start_time')->nullable()->after('end_time');
            $table->time('morning_end_time')->nullable()->after('morning_start_time');
            $table->boolean('morning_is_active')->default(true)->after('morning_end_time');

            $table->time('evening_start_time')->nullable()->after('morning_is_active');
            $table->time('evening_end_time')->nullable()->after('evening_start_time');
            $table->boolean('evening_is_active')->default(true)->after('evening_end_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partner_schedules', function (Blueprint $table) {
            $table->dropColumn([
                'morning_start_time',
                'morning_end_time',
                'morning_is_active',
                'evening_start_time',
                'evening_end_time',
                'evening_is_active',
            ]);
        });
    }
};
