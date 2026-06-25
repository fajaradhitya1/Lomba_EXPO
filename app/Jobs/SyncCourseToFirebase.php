<?php

namespace App\Jobs;

use App\Models\Course;
use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

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
        $this->course = $this->course->fresh();

        Log::info("Job Started: Syncing Course ID " . $this->course->id);

        try {
            $cred = json_decode(env('FIREBASE_CREDENTIALS_JSON'), true);

            $db = new FirestoreClient([
                'projectId'   => $cred['project_id'],
                'credentials' => $cred,
                'transport'   => 'rest'
            ]);

            $courseDocumentId = str_replace(' ', '_', strtolower($this->course->name));

            $db->collection('courses')
               ->document($courseDocumentId)
               ->set([
                   'name' => $this->course->name,
                   'semester' => $this->course->semester,
               ], ['merge' => true]);

            Log::info("Job Success: Course " . $this->course->id . " synced to " . $courseDocumentId);

        } catch (\Exception $e) {
            Log::error("Firebase Sync Error (Course): " . $e->getMessage());
            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error("JOB GAGAL TOTAL (Course): " . $exception->getMessage());
    }
}