<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Course; // <--- INI YANG KURANG (PENTING!)

class Module extends Model
{
    protected $fillable = ['title', 'type', 'course_id', 'order', 'quiz_questions', 'pdf_file'];

    protected $casts = [
        'quiz_questions' => 'array',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}