<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ModuleController;

Route::post('/admin/upload-modul', [ModuleController::class, 'uploadModule'])->name('admin.upload.modul');