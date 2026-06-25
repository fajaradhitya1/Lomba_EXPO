<?php
namespace App\Jobs;

use App\Models\Module;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Google\Cloud\Firestore\FirestoreClient;

class SyncQuizToFirebase implements ShouldQueue
{
    use Dispatchable, Queueable;

    public $module;

    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    public function handle(): void
    {
        \App\Models\Module::withoutEvents(function () {
            $this->module = $this->module->fresh();
            $this->module->load('course');

            if (!$this->module->course) return;

            try {
                // FIX: Ganti keyFilePath dengan credentials dari env variable
                $cred = json_decode(env('FIREBASE_CREDENTIALS_JSON'), true);

                $db = new FirestoreClient([
                    'projectId'   => $cred['project_id'],
                    'credentials' => $cred,
                    'transport'   => 'rest'
                ]);

                $courseDocumentId = str_replace(' ', '_', strtolower($this->module->course->name));

                $db->collection('courses')
                   ->document($courseDocumentId)
                   ->collection('modules')
                   ->document('modul_' . $this->module->id)
                   ->set([
                       'title'          => $this->module->title,
                       'quiz_questions' => $this->module->quiz_questions,
                       'order'          => $this->module->order ?? 0,
                       'type'           => 'quiz',
                       'is_completed'   => false,
                   ], ['merge' => true]);

                \Illuminate\Support\Facades\Log::info("Job Success: Kuis " . $this->module->id . " synced");

            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Firebase Sync Quiz Error: " . $e->getMessage());
                throw $e;
            }
        });
    }
}