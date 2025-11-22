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
        Schema::create('head_hunter_vacancies', function (Blueprint $table) {
            $table->id();
            $table->string('hh_id')->unique()->nullable();
            $table->string('external_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('salary')->nullable();
            $table->string('employer_name');
            $table->json('employer_info')->nullable();
            $table->string('area_name');
            $table->json('experience')->nullable();
            $table->json('employment')->nullable();
            $table->json('schedule')->nullable();
            $table->string('url');
            $table->boolean('auto_posting')->default(true);
            $table->timestamp('published_at');
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_to_channel_at')->nullable();
            $table->json('raw_data')->nullable();
            $table->string('hh_status')->default('active');
            $table->timestamp('last_checked_at')->nullable();
            $table->unsignedInteger('check_attempts')->default(0);
            $table->text('check_error')->nullable();
            $table->unsignedInteger('area_id')->nullable();
            $table->boolean('is_closed')->default(false);

            $table->index('area_id');
            $table->index('area_name');
            $table->index('is_approved');
            $table->index('is_published');
            $table->index('published_at');
            $table->index(['is_approved', 'is_published']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('head_hunter_vacancies');
    }
};
