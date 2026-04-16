<?php
header('Content-Type: application/json; charset=utf-8');

$bgDir = __DIR__ . '/../assets/bgs';
$backgrounds = [];

if (is_dir($bgDir)) {
    $files = scandir($bgDir);
    foreach ($files as $file) {
        if (preg_match('/\.(jpg|jpeg|png|webp)$/i', $file)) {
            $backgrounds[] = '/assets/bgs/' . $file;
        }
    }
    sort($backgrounds, SORT_NATURAL);
}

echo json_encode($backgrounds);
