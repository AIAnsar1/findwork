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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('username')->nullable()->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('group_id')->nullable()->unique();
            $table->boolean('anti_spam_mode')->default(false);
            $table->boolean('auto_ban_user')->default(false);
            $table->string('ban_message')->nullable();
            $table->json('banned_words')->nullable();
            $table->json('banned_links')->nullable();
            $table->json('banned_usernames')->nullable();
            $table->integer('max_warnings')->default(3);
            $table->string('invite_link')->nullable();
            $table->boolean('bot_is_admin')->default(false);
            $table->string('language', 5)->default('en');
            $table->boolean('ban_on_link_username')->default(true);
            $table->unsignedBigInteger('members_count')->default(0);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
