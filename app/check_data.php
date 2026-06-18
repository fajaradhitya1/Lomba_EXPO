<?php
// Memuat dependensi Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Kreait\Firebase\Factory;

// Inisialisasi Firebase
// Asumsi: Kamu sudah punya file credentials di config atau storage
// Ini menggunakan konfigurasi yang sama dengan Laravel kamu
$firestore = app('firebase.firestore');

try {
    $collection = $firestore->database()->collection('courses')->document('basis_data')->collection('modules');
    $documents = $collection->documents();

    echo "KONEKSI BERHASIL. Membaca data...\n\n";

    foreach ($documents as $doc) {
        $data = $doc->data();
        echo "ID: " . $doc->id() . "\n";
        // Cek keduanya: 'tittle' dan 'title'
        echo "Tittle (dua T): " . ($data['tittle'] ?? "TIDAK ADA") . "\n";
        echo "Title (satu T): " . ($data['title'] ?? "TIDAK ADA") . "\n";
        echo "FileUrl: " . ($data['fileUrl'] ?? "TIDAK ADA") . "\n";
        echo "------------------------------------\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}