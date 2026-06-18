<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;
use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Support\Facades\Log;

class ModuleController extends Controller
{
    public function uploadModule(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'course_id' => 'required|string',
            'module_id' => 'required|string', // ID contoh: modul_1
            'level_number' => 'required|integer',
            'module_title' => 'required|string',
            'file_module' => 'required|file|mimes:pdf,doc,docx|max:10240',
        ]);

        try {
            // Setup Konfigurasi Cloudinary Manual (Bypass Facade)
            Configuration::instance([
                'cloud' => [
                    'cloud_name' => config('cloudinary.cloud_name'),
                    'api_key'    => config('cloudinary.api_key'),
                    'api_secret' => config('cloudinary.api_secret'),
                ]
            ]);

            $courseId = strtolower($request->course_id);
            $moduleId = $request->module_id; 
            $file = $request->file('file_module');

            // 2. Upload ke Cloudinary (SDK langsung)
            $uploader = new UploadApi();
            $result = $uploader->upload($file->getRealPath(), [
                'folder' => 'modules',
                'resource_type' => 'raw'
            ]);

            $fileUrl = $result['secure_url'];
            $fileName = $file->getClientOriginalName();

            // 3. Update ke Firestore
            $db = new FirestoreClient([
                'keyFilePath' => storage_path('app/firebase-auth.json'),
                'transport'   => 'rest'
            ]);
            
            // Referensi dokumen
            $moduleRef = $db->collection('courses')
                ->document($courseId)
                ->collection('modules')
                ->document($moduleId);

            // Update data
            $moduleRef->set([
                'fileUrl'      => $fileUrl,
                'title'        => $request->module_title, // Sudah diperbaiki dari 'tittle'
                'fileName'     => $fileName,
                'order'        => (int)$request->level_number,
                'is_completed' => true,
            ], ['merge' => true]);

            return back()->with('success', 'Modul berhasil diupdate & disinkronisasi!');

        } catch (\Exception $e) {
            Log::error("Controller Upload Error: " . $e->getMessage());
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }
}