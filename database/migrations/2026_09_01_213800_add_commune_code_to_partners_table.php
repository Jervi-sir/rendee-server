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
        // 1. Ensure communes table has unique constraint on code
        Schema::table('communes', function (Blueprint $table) {
            $table->unique('code', 'communes_code_unique');
        });

        // 2. Add commune_code to partners and foreign key
        Schema::table('partners', function (Blueprint $table) {
            if (! Schema::hasColumn('partners', 'commune_code')) {
                $table->string('commune_code')->nullable()->after('wilaya_code');
            }
            $table->foreign('commune_code')->references('code')->on('communes')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            if (Schema::hasColumn('partners', 'commune_code')) {
                $table->dropForeign(['commune_code']);
                $table->dropColumn('commune_code');
            }
        });

        Schema::table('communes', function (Blueprint $table) {
            $table->dropUnique('communes_code_unique');
        });
    }
};
