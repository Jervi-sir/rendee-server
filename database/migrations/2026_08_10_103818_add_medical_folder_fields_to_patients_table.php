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
        Schema::table('patients', function (Blueprint $table) {
            $table->string('blood_type', 10)->nullable()->after('city');
            $table->json('allergies')->nullable()->after('blood_type');
            $table->json('chronic_diseases')->nullable()->after('allergies');
            $table->json('medications')->nullable()->after('chronic_diseases');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['blood_type', 'allergies', 'chronic_diseases', 'medications']);
        });
    }
};
