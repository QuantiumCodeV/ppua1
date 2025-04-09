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
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workspace_id');
            $table->foreign('workspace_id')->references('id')->on('workspaces');
            $table->uuid('channel_id');
            $table->foreign('channel_id')->references('id')->on('channels');
            $table->foreignId('author')->constrained('users');
            $table->text('text');
            $table->timestamp('timestamp')->nullable();
            $table->bigInteger('timestamp_milli')->nullable();
            $table->string('subtype')->nullable();
            $table->json('reactions')->nullable();
            $table->json('link_previews')->nullable();
            $table->boolean('is_following')->default(false);
            $table->json('thread_root_info')->nullable();
            $table->json('thread_reply_info')->nullable();
            $table->json('files')->nullable();
            $table->boolean('deleted')->default(false);
            $table->boolean('edited')->default(false);
            $table->string('local_id');
            $table->json('attachments')->nullable();
            $table->bigInteger('saved_timestamp_milli')->default(0);
            $table->json('blocks')->nullable();
            $table->json('meta')->nullable();
            $table->string('author_app_id')->nullable();
            $table->boolean('system_message')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
