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
        Schema::create('advertisings', function (Blueprint $table) {
            $table->id();
            $table->json('content')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('language', 5)->default('en');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'published', 'failed', 'archived'])->default('draft');
            $table->string('telegram_post_id')->nullable()->index();
            $table->string('post_url')->nullable();
            $table->string('link')->nullable();
            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedBigInteger('reactions_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisings');
    }
};
