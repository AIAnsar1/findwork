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
        Schema::create('resumes', function (Blueprint $table) {
            $table->id();
            $table->string('full_name')->nullable();
            $table->integer('age')->nullable();
            $table->string('address')->nullable();
            $table->string('position');   
            $table->integer('salary')->nullable();
            $table->enum('employment', ['full', 'part', 'contract', 'temporary', 'intern'])->default('full');
            $table->string('schedule')->nullable();  
            $table->enum('format', ['office', 'remote', 'hybrid'])->default('office');
            $table->integer('experience_years')->nullable();
            $table->text('skills')->nullable();
            $table->json('work_experience')->nullable(); 
            $table->string('phone')->nullable();
            $table->string('telegram')->nullable();
            $table->enum('status', ['active', 'hidden', 'moderation', 'rejected'])->default('active');
            $table->text('about')->nullable();
            $table->foreignId('telegram_user_id')->constrained('telegram_users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resumes');
    }
};
