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

// Rate limit: max 3 creates per IP per hour
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rl = supabaseRpc('check_rate_limit', [
    'p_ip' => $ip,
    'p_action' => 'create',
    'p_window_seconds' => 3600,
    'p_max' => 3
], SUPABASE_SERVICE_KEY);

if ($rl['data'] === false) {
    http_response_code(429);
    echo json_encode(['error' => 'Voce criou perfis demais. Tente novamente em 1 hora.']);
    exit;
}

// Validate profile name (always stored lowercase)
$profileName = strtolower(trim($_POST['profileName'] ?? ''));
if (!$profileName || !preg_match('/^[a-z0-9_-]+$/', $profileName)) {
    http_response_code(400);
    echo json_encode(['error' => 'Nome de perfil invalido. Use apenas letras, numeros, hifens e underscores.']);
    exit;
}

if (strlen($profileName) > 30) {
    http_response_code(400);
    echo json_encode(['error' => 'Nome de perfil muito longo (maximo 30 caracteres).']);
    exit;
}

// Block reserved names
$reserved = ['login', 'painel', 'editar', 'api', 'assets', 'css', 'data', 'admin'];
if (in_array($profileName, $reserved)) {
    http_response_code(400);
    echo json_encode(['error' => 'Esse nome de perfil e reservado.']);
    exit;
}

// Handle avatar upload
$avatarPath = null;
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($_FILES['avatar']['tmp_name']);

    if (!in_array($mimeType, $allowedTypes)) {
        http_response_code(400);
        echo json_encode(['error' => 'Tipo de arquivo nao permitido. Use JPG, PNG, GIF ou WebP.']);
        exit;
    }

    if ($_FILES['avatar']['size'] > 5 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['error' => 'Arquivo muito grande (maximo 5MB).']);
        exit;
    }

    $ext = match($mimeType) {
        'image/jpeg' => '.jpg',
        'image/png'  => '.png',
        'image/gif'  => '.gif',
        'image/webp' => '.webp',
        default      => '.jpg'
    };

    $avatarDir = __DIR__ . '/../assets/avatars';
    if (!is_dir($avatarDir)) mkdir($avatarDir, 0755, true);

    $filename = $profileName . $ext;
    move_uploaded_file($_FILES['avatar']['tmp_name'], $avatarDir . '/' . $filename);
    $avatarPath = '/assets/avatars/' . $filename;
}

// Parse and validate custom links
$customLinks = [];
if (!empty($_POST['customLinks'])) {
    $parsed = json_decode($_POST['customLinks'], true);
    if (is_array($parsed)) {
        foreach ($parsed as $link) {
            if (!empty($link['title']) && !empty($link['url'])) {
                $url = filter_var($link['url'], FILTER_VALIDATE_URL);
                if (!$url || !preg_match('#^https?://#i', $url)) continue;
                $customLinks[] = ['title' => substr($link['title'], 0, 100), 'url' => $url];
            }
        }
    }
}

$bgColor = $_POST['bgColor'] ?? '#0f0f0f';
if (!preg_match('/^#[0-9a-fA-F]{6}$/', $bgColor)) $bgColor = '#0f0f0f';

$btnColor = $_POST['btnColor'] ?? '#1a1a2e';
if (!preg_match('/^#[0-9a-fA-F]{6}$/', $btnColor)) $btnColor = '#1a1a2e';

$btnShape = $_POST['btnShape'] ?? 'rounded';
if (!in_array($btnShape, ['rounded', 'pill', 'square'])) $btnShape = 'rounded';

$bgImage = $_POST['bgImage'] ?? '';
if ($bgImage && !preg_match('#^/assets/bgs/[a-zA-Z0-9._-]+$#', $bgImage)) $bgImage = '';

// Insert into Supabase (RLS enforces user_id = auth.uid())
$profile = [
    'user_id'      => $userId,
    'profile_name' => $profileName,
    'bg_color'     => $bgColor,
    'bg_image'     => $bgImage ?: null,
    'btn_color'    => $btnColor,
    'btn_shape'    => $btnShape,
    'btn_glass'    => !empty($_POST['btnGlass']),
    'avatar'       => $avatarPath,
    'links'        => [
        'instagram' => trim($_POST['instagram'] ?? ''),
        'tiktok'    => trim($_POST['tiktok'] ?? ''),
        'youtube'   => trim($_POST['youtube'] ?? ''),
        'whatsapp'  => preg_replace('/[^0-9]/', '', $_POST['whatsapp'] ?? '')
    ],
    'custom_links' => $customLinks,
];

$res = supabase('POST', 'profiles', $profile, $token, ['Prefer: return=representation']);

if ($res['status'] === 201) {
    echo json_encode(['success' => true, 'url' => '/' . $profileName]);
} elseif ($res['status'] === 409) {
    http_response_code(400);
    $msg = $res['data']['message'] ?? '';
    if (str_contains($msg, 'user_id')) {
        echo json_encode(['error' => 'Voce ja possui um perfil. Cada conta pode ter apenas um perfil.']);
    } else {
        echo json_encode(['error' => 'Esse nome de perfil ja esta em uso.']);
    }
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao criar perfil. Tente novamente.']);
}
