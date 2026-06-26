<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Gunakan ifNotExists agar tidak error jika tabel sudah terlanjur ada
        if (!Schema::hasTable('modules')) {
            Schema::create('modules', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('type');
                $table->integer('pertemuan')->default(1);
                $table->unsignedBigInteger('course_id')->nullable();
                $table->integer('order')->default(0);
                $table->json('quiz_questions')->nullable();
                $table->timestamps();
            });
        } else {
            // JIKA TABEL SUDAH ADA, kita pastikan kolom 'pertemuan' ada
            Schema::table('modules', function (Blueprint $table) {
                if (!Schema::hasColumn('modules', 'pertemuan')) {
                    $table->integer('pertemuan')->default(1);
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};