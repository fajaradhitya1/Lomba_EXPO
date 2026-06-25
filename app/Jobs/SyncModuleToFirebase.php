<?php

namespace App\Jobs;

use App\Models\Module;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;
use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Firestore\FieldValue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncModuleToFirebase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $module;

    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    public function handle(): void
    {
        $this->module = $this->module->fresh();

        Log::info("Job Started: Syncing Module ID " . $this->module->id);

        Configuration::instance([
            'cloud' => [
                'cloud_name' => config('cloudinary.cloud_name'),
                'api_key'    => config('cloudinary.api_key'),
                'api_secret' => config('cloudinary.api_secret'),
            ]
        ]);

        $this->module->load('course');

        if (!$this->module->course) {
            Log::error("Job Failed: Course tidak ditemukan untuk Modul ID: " . $this->module->id);
            return;
        }

        $fileUrl = '';
        if (filled($this->module->pdf_file)) {
            $filePath = storage_path('app/public/' . $this->module->pdf_file);
            if (file_exists($filePath)) {
                try {
                    $uploader = new UploadApi();
                    $result = $uploader->upload($filePath, [
                        'folder' => 'modules',
                        'resource_type' => 'raw'
                    ]);
                    $fileUrl = $result['secure_url'];
                } catch (\Exception $e) {
                    Log::error("Cloudinary Upload Error: " . $e->getMessage());
                }
            }
        }

        try {
            $cred = json_decode(env('FIREBASE_CREDENTIALS_JSON'), true);

            $db = new FirestoreClient([
                'projectId'   => $cred['project_id'],
                'credentials' => $cred,
                'transport'   => 'rest'
            ]);

            $courseDocumentId = str_replace(' ', '_', strtolower($this->module->course->name));

            // 1. Sync Modul
            $db->collection('courses')
               ->document($courseDocumentId)
               ->collection('modules')
               ->document('modul_' . $this->module->id)
               ->set([
                   'title' => $this->module->title,
                   'fileUrl' => $fileUrl,
                   'order' => $this->module->order ?? 0,
                   'type' => $this->module->type,
               ], ['merge' => true]);

            // 2. Increment Counter
            $db->collection('courses')
               ->document($courseDocumentId)
               ->set([
                   'total_materi' => FieldValue::increment(1)
               ], ['merge' => true]);

            Log::info("Job Success: Modul " . $this->module->id . " synced to " . $courseDocumentId);

        } catch (\Exception $e) {
            Log::error("Firebase Sync Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error("JOB GAGAL TOTAL: " . $exception->getMessage());
    }
}