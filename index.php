<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['files'])) {
    $files = $_FILES['files'];
    $date = date('Y-m-d');
    $backupDir = __DIR__ . "/backup/";

    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0777, true);
    }

    $zipName = $backupDir . $date . '.zip';
    $zip = new ZipArchive();

    if ($zip->open($zipName, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create ZIP archive.']);
        exit;
    }

    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['type'][$i] !== 'application/json') {
            continue;
        }

        if (is_uploaded_file($files['tmp_name'][$i])) {
            $zip->addFile($files['tmp_name'][$i], basename($files['name'][$i]));
        }
    }

    $zip->close();

    echo json_encode([
        'success' => true,
        'zip_path' => $zipName
    ]);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'No files uploaded or wrong request method.']);
}