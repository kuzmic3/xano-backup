<?php
// zip_backup.php

// 1. Postavi datum i putanje
$date     = date('Y-m-d');
$srcDir   = __DIR__ . "/backup/{$date}/";
$zipPath  = __DIR__ . "/backup/{$date}.zip";

// 2. Proveri da li folder postoji
if (!is_dir($srcDir)) {
    http_response_code(404);
    echo json_encode([
        'error' => "Folder za datum {$date} ne postoji."
    ]);
    exit;
}

// 3. Pronađi sve JSON fajlove u folderu
$files = glob($srcDir . '*.json');
if (empty($files)) {
    http_response_code(200);
    echo json_encode([
        'message' => "Nema JSON fajlova za arhiviranje u {$srcDir}."
    ]);
    exit;
}

// 4. Otvori ZIP arhivu za kreiranje
$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    http_response_code(500);
    echo json_encode([
        'error' => "Ne mogu da napravim ZIP arhivu na putanji {$zipPath}."
    ]);
    exit;
}

// 5. Dodaj fajlove u ZIP
foreach ($files as $file) {
    $localName = basename($file);
    if (!$zip->addFile($file, $localName)) {
        error_log("Greška pri dodavanju fajla {$file} u ZIP.");
    }
}

// 6. Zatvori arhivu
$zip->close();

// 7. Obriši originalne JSON fajlove
$deleted = [];
foreach ($files as $file) {
    if (unlink($file)) {
        $deleted[] = basename($file);
    } else {
        error_log("Greška pri brisanju fajla {$file}.");
    }
}

// 8. Vrati rezultat
echo json_encode([
    'success'   => true,
    'zip_path'  => $zipPath,
    'deleted'   => $deleted
]);
