<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Metodo nao permitido.']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/supabase.php';

$user = requireAuth();
$userId = $user['sub'];
$token = getBearerToken();

$profileName = strtolower(trim($_POST['profileName'] ?? ''));
if (!$profileName) {
    http_response_code(400);
    echo json_encode(['error' => 'Nome do perfil nao informado.']);
    exit;
}

// Get profile to verify ownership and get avatar path
$current = supabase('GET', 'profiles?profile_name=eq.' . urlencode($profileName) . '&user_id=eq.' . urlencode($userId) . '&limit=1');

if (empty($current['data'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Voce nao tem permissao para deletar esse perfil.']);
    exit;
}

$profile = $current['data'][0];

// Delete avatar file
if (!empty($profile['avatar'])) {
    $avatarPath = __DIR__ . '/../' . ltrim($profile['avatar'], '/');
    if (file_exists($avatarPath)) unlink($avatarPath);
}

// Delete from Supabase (RLS ensures ownership)
$res = supabase('DELETE', 'profiles?profile_name=eq.' . urlencode($profileName), null, $token, ['Prefer: return=representation']);

if ($res['status'] === 200 && !empty($res['data'])) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao deletar. Tente novamente.']);
}
