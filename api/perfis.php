<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/supabase.php';

$user = requireAuth();
$userId = $user['sub'];

$res = supabase('GET', 'profiles?user_id=eq.' . urlencode($userId));

$profiles = [];
if (!empty($res['data'])) {
    foreach ($res['data'] as $row) {
        $profiles[] = mapProfile($row);
    }
}

echo json_encode($profiles);
