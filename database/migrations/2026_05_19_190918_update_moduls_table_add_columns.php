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
    Schema::table('moduls', function (Blueprint $table) {
        $table->string('unit_id')->nullable();
        $table->string('dokumen_materi')->nullable();
        $table->string('image_url')->nullable();
    });
}

public function down(): void
{
    Schema::table('moduls', function (Blueprint $table) {
        $table->dropColumn(['unit_id', 'dokumen_materi', 'image_url']);
    });
}
};
