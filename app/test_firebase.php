<?php
// test_firebase.php
require 'vendor/autoload.php';

use Kreait\Firebase\Factory;

try {
    $factory = (new Factory)->withServiceAccount('storage/app/firebase-auth.json');
    $firestore = $factory->createFirestore();
    $db = $firestore->database();
    
    // Coba tulis data dummy
    $db->collection('test_collection')->document('test_doc')->set(['status' => 'koneksi_aman']);
    echo "✅ KONEKSI BERHASIL! Data berhasil dikirim ke Firebase.\n";
} catch (\Exception $e) {
    echo "❌ KONEKSI GAGAL!\n";
    echo "Pesan Error: " . $e->getMessage() . "\n";
}