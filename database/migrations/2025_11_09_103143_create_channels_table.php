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
            $table->id();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('channel_id')->nullable()->unique();
            $table->boolean('comments_enabled')->default(false);
            $table->string('invite_link')->nullable();
            $table->boolean('bot_is_admin')->default(false);
            $table->string('language', 5)->default('en');
            $table->unsignedBigInteger('members_count')->default(0);
            $table->timestamp('last_synced_at')->nullable();
            $table->foreignId('group_id')->nullable()->after('id')->constrained('groups')->nullOnDelete();
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
