<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];

    if ($file['type'] !== 'application/json') {
        http_response_code(400);
        echo json_encode(['error' => 'Only JSON files are allowed.']);
        exit;
    }

    $date = date('Y-m-d');
    $dir = __DIR__ . "/backup/$date/";
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $filename = basename($file['name']);
    $targetPath = $dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        echo json_encode(['success' => true, 'path' => $targetPath]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to move uploaded file.']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded or wrong request method.']);
}