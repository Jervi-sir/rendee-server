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
            if (! Schema::hasColumn('patients', 'commune_code')) {
                $table->string('commune_code')->nullable()->after('wilaya_code');
                $table->foreign('commune_code')->references('code')->on('communes')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            if (Schema::hasColumn('patients', 'commune_code')) {
                $table->dropForeign(['commune_code']);
                $table->dropColumn('commune_code');
            }
        });
    }
};
