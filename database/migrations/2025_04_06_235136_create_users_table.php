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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('');
            $table->string('email')->unique()->default('');
            $table->string('password')->default('');
            $table->string('time_zone_id')->nullable()->default(null);
            $table->boolean('automatically_time_zone')->default(true);
            $table->string('title')->nullable()->default(null);
            $table->string('phone')->nullable()->default(null);
            $table->integer('time_format')->default(12);
            $table->json('avatar')->nullable()->default(null);
            $table->boolean('is_addon_bot')->default(false);
            $table->boolean('is_pumble_bot')->default(false);
            $table->string('workspace_id')->default('');
            $table->enum('role', ['OWNER', 'ADMIN', 'MEMBER', 'GUEST'])->default('MEMBER');
            $table->enum('status', ['ACTIVATED', 'PENDING', 'DEACTIVATED'])->default('PENDING');
            $table->json('custom_status')->nullable()->default(null);
            $table->string('invited_by')->nullable()->default(null);
            $table->bigInteger('active_until')->default(0);
            $table->timestamp('broadcast_warning_shown_ts')->nullable()->default(null);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
