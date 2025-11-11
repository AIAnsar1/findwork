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
        Schema::create('advertising_group', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertising_id')->constrained('advertisings')->nullOnDelete();
            $table->foreignId('group_id')->constrained('groups')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertising_group');
    }
};
