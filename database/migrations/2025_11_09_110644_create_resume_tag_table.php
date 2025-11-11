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
        Schema::create('resume_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resume_id')->constrained('resumes')->nullOnDelete();
            $table->foreignId('tag_id')->constrained('tags')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resume_tag');
    }
};
