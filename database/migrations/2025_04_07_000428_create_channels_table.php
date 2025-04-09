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
        Schema::create('channels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('description')->nullable();
            $table->enum('channel_type', ['PUBLIC', 'PRIVATE', 'SELF'])->default('PUBLIC');
            $table->foreignId('creator_id')->constrained('users');
            $table->uuid('workspace_id');
            $table->foreign('workspace_id')->references('id')->on('workspaces');
            $table->boolean('is_member')->default(false);
            $table->boolean('is_muted')->default(false);
            $table->boolean('is_hidden')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->boolean('is_main')->default(false);
            $table->boolean('is_initial')->default(false);
            $table->string('section_id')->nullable();
            $table->timestamp('last_message_timestamp')->nullable();
            $table->bigInteger('last_message_timestamp_milli')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
