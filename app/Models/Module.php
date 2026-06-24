<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Course; // <--- INI YANG KURANG (PENTING!)

class Module extends Model
{
    protected $fillable = [
    'course_id', 
    'title', 
    'pdf_file', // pastikan ada
    'type',     // INI HARUS ADA!
    'order', 
    'is_completed',
    'quiz_questions' // Tambahkan ini jika Anda menyimpan soal di kolom JSON
];

    protected $casts = [
        'quiz_questions' => 'array',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}