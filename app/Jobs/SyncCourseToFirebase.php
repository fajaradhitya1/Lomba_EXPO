<?php

namespace App\Jobs;

use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Kreait\Laravel\Firebase\Facades\Firebase; // Gunakan Facade Kreait

class SyncCourseToFirebase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $course;

    public function __construct(Course $course)
    {
        $this->course = $course;
    }

    public function handle(): void
    {
        // Akses Firestore melalui Kreait Firebase
        $firestore = Firebase::firestore();
        $db = $firestore->database();

        $courseDocumentId = str_replace(' ', '_', strtolower($this->course->name));

        // Sync ke Firestore menggunakan syntax Kreait
        $db->collection('courses')
           ->document($courseDocumentId)
           ->set([
               'name' => $this->course->name,
               'semester' => $this->course->semester ?? 1,
               'total_materi' => 0,
               'created_at' => now()->toIso8601String(),
           ], ['merge' => true]);
    }
}