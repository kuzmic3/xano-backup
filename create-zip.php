<?php
header('Content-Type: application/json');

// Postavi današnji datum i putanju do foldera
$date = date('Y-m-d');
$backupDir = __DIR__ . "/backup/$date/";

// Provera da li folder postoji
if (!is_dir($backupDir)) {
    http_response_code(404);
    echo json_encode(['error' => "Folder za datum $date ne postoji."]);
    exit;
}

// Skupi sve JSON fajlove iz foldera
$jsonFiles = glob($backupDir . '*.json');
if (empty($jsonFiles)) {
    echo json_encode(['error' => 'Nema JSON fajlova za arhiviranje.']);
    exit;
}

// Definiši putanju za ZIP (smešten u backup root, pored podfoldera)
$zipPath = __DIR__ . "/backup/{$date}.zip";

// Kreiraj i otvori ZIP arhivu
$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    http_response_code(500);
    echo json_encode(['error' => 'Ne mogu da kreiram ZIP arhivu.']);
    exit;
}

// Dodaj svaki JSON fajl u ZIP
foreach ($jsonFiles as $file) {
    $basename = basename($file);
    if (!$zip->addFile($file, $basename)) {
        // Ako ne može da doda fajl, prekini i izbriši ZIP
        $zip->close();
        @unlink($zipPath);
        http_response_code(500);
        echo json_encode(['error' => "Greška pri dodavanju fajla $basename u ZIP."]);
        exit;
    }
}

// Zatvori ZIP arhivu
$zip->close();

// Obriši originalne JSON fajlove
foreach ($jsonFiles as $file) {
    @unlink($file);
}

// Vrati putanju do ZIP arhive
echo json_encode([
    'success' => true,
    'zip'     => $zipPath
]);
