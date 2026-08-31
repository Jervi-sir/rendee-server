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
            if (! Schema::hasColumn('patients', 'wilaya_code')) {
                $table->string('wilaya_code')->nullable()->after('gender');
                $table->foreign('wilaya_code')->references('code')->on('wilayas')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            if (Schema::hasColumn('patients', 'wilaya_code')) {
                $table->dropForeign(['wilaya_code']);
                $table->dropColumn('wilaya_code');
            }
        });
    }
};
