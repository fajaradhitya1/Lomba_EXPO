<?php

namespace App\Jobs;

use App\Models\Module;
use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Firestore\FieldValue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
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
                    $bucket = 'modules';
                    $objectPath = $this->module->id . '_' . basename($filePath);

                    $supabaseUrl = config('services.supabase.url');
                    $supabaseKey = config('services.supabase.key');

                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $supabaseKey,
                        'apikey'        => $supabaseKey,
                        'Content-Type'  => 'application/pdf',
                        'x-upsert'      => 'true', // biar boleh overwrite kalau file sama diupload ulang
                    ])->withBody(file_get_contents($filePath), 'application/pdf')
                      ->post("{$supabaseUrl}/storage/v1/object/{$bucket}/{$objectPath}");

                    if ($response->successful()) {
                        $fileUrl = "{$supabaseUrl}/storage/v1/object/public/{$bucket}/{$objectPath}";
                    } else {
                        Log::error("Supabase Upload Error: " . $response->body());
                    }
                } catch (\Exception $e) {
                    Log::error("Supabase Upload Exception: " . $e->getMessage());
                }
            } else {
                Log::warning("File tidak ditemukan di path: " . $filePath);
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

            $db->collection('courses')
               ->document($courseDocumentId)
               ->collection('modules')
               ->document('modul_' . $this->module->id)
               ->set([
                   'title'   => $this->module->title,
                   'fileUrl' => $fileUrl,
                   'order'   => $this->module->order ?? 0,
                   'type'    => $this->module->type,
               ], ['merge' => true]);

            $db->collection('courses')
               ->document($courseDocumentId)
               ->set(['total_materi' => FieldValue::increment(1)], ['merge' => true]);

            Log::info("Job Success: Modul " . $this->module->id . " synced | fileUrl: " . $fileUrl);

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