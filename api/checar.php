<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/supabase.php';

$name = strtolower(trim($_GET['name'] ?? ''));

if (!$name) {
    echo json_encode(['available' => false]);
    exit;
}

$res = supabase('GET', 'profiles?profile_name=eq.' . urlencode($name) . '&select=profile_name&limit=1');

echo json_encode(['available' => empty($res['data'])]);
