<?php
/**
 * Check profile image files in uploads folder
 */

header('Content-Type: application/json');

$userId = $_GET['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['error' => 'user_id required']);
    exit;
}

$uploadDir = __DIR__ . '/../uploads/profiles';
$pattern = $uploadDir . '/profile_' . $userId . '_*.*';
$files = glob($pattern);

$fileList = [];
foreach ($files as $file) {
    $fileList[] = [
        'name' => basename($file),
        'size' => round(filesize($file) / 1024, 2),
        'modified' => date('Y-m-d H:i:s', filemtime($file))
    ];
}

$latest = !empty($files) ? basename($files[count($files) - 1]) : null;

echo json_encode([
    'files' => $fileList,
    'latest' => $latest,
    'count' => count($fileList)
]);
