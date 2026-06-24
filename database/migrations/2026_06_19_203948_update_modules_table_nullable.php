<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->string('pdf_file')->nullable()->change();
            $table->string('type')->default('module')->change();
        });
    }

    public function down(): void
    {
        // kembalikan jika perlu
    }
};
