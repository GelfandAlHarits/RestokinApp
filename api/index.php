<?php

// ============================================================
// DEBUG ENTRY POINT — untuk isolasi penyebab 500 error di Vercel
// Setelah masalah ketemu, ganti balik ke versi index.php normal
// (tanpa echo checkpoint) atau bersihkan bagian debug-nya.
// ============================================================

// Paksa PHP menampilkan semua error ke response, apapun kondisi php.ini runtime
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

header('Content-Type: text/plain');
echo "Checkpoint 1: Reached api/index.php\n";

try {

    // ------------------------------------------------------
    // Filesystem di Vercel read-only kecuali /tmp.
    // Laravel butuh nulis ke storage/ & bootstrap/cache/,
    // jadi kita arahkan ke /tmp sebelum apapun di-load.
    // ------------------------------------------------------
    $tmpBase = '/tmp/storage';
    foreach (['app', 'app/public', 'framework', 'framework/cache', 'framework/cache/data', 'framework/sessions', 'framework/testing', 'framework/views', 'logs'] as $dir) {
        $path = $tmpBase . '/' . $dir;
        if (!is_dir($path)) {
            @mkdir($path, 0775, true);
        }
    }
    if (!is_dir('/tmp/cache')) {
        @mkdir('/tmp/cache', 0775, true);
    }
    echo "Checkpoint 2: /tmp storage folders prepared\n";

    // ------------------------------------------------------
    // Load autoload Composer
    // ------------------------------------------------------
    require __DIR__ . '/../vendor/autoload.php';
    echo "Checkpoint 3: vendor/autoload.php loaded\n";

    // ------------------------------------------------------
    // Bootstrap aplikasi Laravel
    // ------------------------------------------------------
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    echo "Checkpoint 4: bootstrap/app.php loaded, \$app created\n";

    // Override path storage & cache SETELAH $app dibuat,
    // supaya semua service provider pakai /tmp
    $app->useStoragePath($tmpBase);
    $app->instance('path.storage', $tmpBase);
    echo "Checkpoint 5: storage path overridden to /tmp\n";

    // ------------------------------------------------------
    // Buat kernel HTTP
    // ------------------------------------------------------
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    echo "Checkpoint 6: HTTP Kernel created\n";

    // ------------------------------------------------------
    // Tangani request
    // ------------------------------------------------------
    $request = \Illuminate\Http\Request::capture();
    echo "Checkpoint 7: Request captured\n";

    // Dari titik ini, kita sudah cukup yakin Laravel jalan.
    // Matikan mode debug teks polos dan biarkan Laravel
    // menangani response secara normal.
    // (Hapus / comment blok "echo Checkpoint" di atas
    //  begitu sudah tahu di checkpoint mana crash terjadi)

    $response = $kernel->handle($request);
    echo "Checkpoint 8: Request handled by kernel\n";

    // Kirim response asli dari Laravel
    // (baris echo checkpoint di atas akan bikin output rusak
    //  kalau sampai sini — makanya kalau sudah lolos checkpoint 8,
    //  segera hapus semua echo checkpoint dan biarkan hanya ini:)
    $response->send();

    $kernel->terminate($request, $response);

} catch (\Throwable $e) {
    // Tangkap SEMUA jenis error/exception termasuk fatal error class-level
    http_response_code(500);
    echo "\n=== FATAL ERROR TERTANGKAP ===\n";
    echo "Pesan : " . $e->getMessage() . "\n";
    echo "File  : " . $e->getFile() . "\n";
    echo "Baris : " . $e->getLine() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
}