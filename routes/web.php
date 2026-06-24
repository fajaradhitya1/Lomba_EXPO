<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ModuleController;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;

Route::post('/admin/upload-modul', [ModuleController::class, 'uploadModule'])->name('admin.upload.modul');

Route::get('/download/{filename}', function ($filename) {
    // 1. Decode URL (untuk menangani spasi jika ada)
    $filename = urldecode($filename);
    
    // 2. Tentukan path
    $path = public_path('files/' . $filename);

    // 3. Debugging: Jika file tidak ketemu, print path-nya
    if (!file_exists($path)) {
        return response()->json([
            'status' => 'error',
            'message' => 'File tidak ditemukan',
            'searched_path' => $path
        ], 404);
    }

    // 4. Download
    return Response::download($path, $filename, [
        'Content-Type' => 'application/pdf',
    ]);
});