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
        Schema::create('vacancies', function (Blueprint $table) {
            $table->id();
            $table->string('company')->nullable();
            $table->string('salary')->nullable();
            $table->string('experience')->nullable(); 
            $table->enum('employment', ['full','part', 'contract','temporary','intern' ])->default('full');
            $table->string('schedule')->nullable(); 
            $table->string('work_hours')->nullable();  
            $table->enum('format', ['office', 'remote', 'hybrid'])->default('office');
            $table->text('responsibilities')->nullable();
            $table->text('requirements')->nullable();
            $table->text('conditions')->nullable();
            $table->text('benefits')->nullable();
            $table->boolean('auto_posting')->default(true);
            $table->timestamp('last_posted_at')->nullable();
            $table->string('position')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_telegram')->nullable();
            $table->enum('status', ['open', 'closed', 'moderation', 'rejected'])->default('open');
            $table->string('address')->nullable();
            $table->foreignId('telegram_user_id')->constrained('telegram_users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacancies');
    }
};
