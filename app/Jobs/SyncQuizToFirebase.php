<?php
namespace App\Jobs;

use App\Models\Module; // Gunakan Module
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Google\Cloud\Firestore\FirestoreClient;

class SyncQuizToFirebase implements ShouldQueue
{
    use Dispatchable, Queueable;

    public $module;

    public function __construct(Module $module) // Parameter harus Module
    {
        $this->module = $module;
    }

   public function handle(): void
{
    // Gunakan withoutEvents agar tidak memicu Observer lagi saat Job berjalan
    \App\Models\Module::withoutEvents(function () {
        $this->module = $this->module->fresh();
        $this->module->load('course');

        if (!$this->module->course) return;

        try {
            $db = new FirestoreClient([
                'keyFilePath' => storage_path('app/firebase-auth.json'),
                'transport'   => 'rest'
            ]);

            $courseDocumentId = str_replace(' ', '_', strtolower($this->module->course->name));
            
            $db->collection('courses')
               ->document($courseDocumentId)
               ->collection('modules') 
               ->document('modul_' . $this->module->id) 
               ->set([
                   'title' => $this->module->title,
                   'quiz_questions' => $this->module->quiz_questions, 
                   'order' => $this->module->order ?? 0,
                   'type' => 'quiz', 
                   'is_completed' => false,
               ], ['merge' => true]);

            \Illuminate\Support\Facades\Log::info("Job Success: Kuis " . $this->module->id . " synced");

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Firebase Sync Quiz Error: " . $e->getMessage());
            throw $e;
        }
    });
}
}