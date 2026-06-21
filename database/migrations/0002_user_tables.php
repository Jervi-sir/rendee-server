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
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('en')->nullable();
            $table->string('fr')->nullable();
            $table->string('ar')->nullable();
            $table->timestamps();
        });
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('user_role_code')->nullable();

            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            $table->string('full_name')->nullable();
            $table->string('phone_number')->nullable();

            $table->text('two_factor_secret')->after('password')->nullable();
            $table->text('two_factor_recovery_codes')->after('two_factor_secret')->nullable();
            $table->timestamp('two_factor_confirmed_at')->after('two_factor_recovery_codes')->nullable();

            $table->boolean('profile_complete')->default(false);

            $table->softDeletes();
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('user_role_code')->references('code')->on('user_roles')->nullOnDelete();
        });

        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Device identification
            $table->string('device_id')->nullable(); // Unique device identifier
            $table->string('device_name')->nullable(); // iPhone 15, Samsung Galaxy, etc.
            $table->string('device_type')->nullable(); // ios, android, web
            $table->string('device_model')->nullable();
            $table->string('os_version')->nullable();
            $table->string('app_version')->nullable();

            // Push notification tokens
            $table->text('push_notification_token')->nullable(); // FCM token / APNs token
            $table->text('push_notification_token_sandbox')->nullable(); // For development environment
            $table->timestamp('push_token_last_refreshed_at')->nullable();
            $table->boolean('push_notifications_enabled')->default(true);

            // Device preferences
            $table->string('language')->default('ar');
            $table->string('timezone')->default('Africa/Algiers');
            $table->json('notification_preferences')->nullable(); // JSON for granular controls

            // Session & Activity
            $table->timestamp('last_active_at')->nullable();
            $table->timestamp('last_logged_in_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            // Device status
            $table->boolean('is_active')->default(true);
            $table->timestamp('deactivated_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'is_active']);
            $table->index(['device_id']);
            $table->index(['push_notification_token']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('passkeys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('credential_id')->unique();
            $table->json('credential');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('passkeys');
    }
};
