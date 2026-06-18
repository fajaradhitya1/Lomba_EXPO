<?php

namespace App\Jobs;

use App\Models\Course;
use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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
        $db = new FirestoreClient([
            'keyFilePath' => storage_path('app/firebase-auth.json'),
            'transport'   => 'rest'
        ]);

        // Gunakan format ID yang sama dengan logic di Job Modul
        $courseDocumentId = str_replace(' ', '_', strtolower($this->course->name));

        // Sync ke Firestore
        $db->collection('courses')
           ->document($courseDocumentId)
           ->set([
               'name' => $this->course->name,
               'semester' => $this->course->semester ?? 1,
               'total_materi' => 0, // Inisialisasi awal 0
               'created_at' => now()->toIso8601String(),
           ], ['merge' => true]);
    }
}