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
        // 1. Add lat & lng coordinates to wilayas table if not already present
        Schema::table('wilayas', function (Blueprint $table) {
            if (! Schema::hasColumn('wilayas', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable();
            }
            if (! Schema::hasColumn('wilayas', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable();
            }
            if (! Schema::hasColumn('wilayas', 'lat')) {
                $table->decimal('lat', 10, 8)->nullable();
            }
            if (! Schema::hasColumn('wilayas', 'lng')) {
                $table->decimal('lng', 11, 8)->nullable();
            }
        });

        // 2. Create communes table if it doesn't exist
        if (! Schema::hasTable('communes')) {
            Schema::create('communes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('wilaya_id')->nullable()->constrained('wilayas')->nullOnDelete();
                $table->string('wilaya_code')->nullable()->index();
                $table->string('code')->nullable()->index();
                $table->string('postal_code')->nullable();
                $table->string('en')->nullable();
                $table->string('fr')->nullable();
                $table->string('ar')->nullable();
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->decimal('lat', 10, 8)->nullable();
                $table->decimal('lng', 11, 8)->nullable();
                $table->timestamps();

                $table->foreign('wilaya_code')->references('code')->on('wilayas')->nullOnDelete()->cascadeOnUpdate();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communes');

        Schema::table('wilayas', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach (['latitude', 'longitude', 'lat', 'lng'] as $column) {
                if (Schema::hasColumn('wilayas', $column)) {
                    $columnsToDrop[] = $column;
                }
            }
            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
