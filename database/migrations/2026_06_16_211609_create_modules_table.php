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
    Schema::create('modules', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->string('type');
        $table->unsignedBigInteger('course_id')->nullable();
        $table->integer('order')->default(0);
        $table->json('quiz_questions')->nullable(); // Wajib JSON untuk menyimpan data array kuis
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
