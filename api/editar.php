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

// Rate limit: max 10 edits per IP per hour
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rl = supabaseRpc('check_rate_limit', [
    'p_ip' => $ip,
    'p_action' => 'edit',
    'p_window_seconds' => 3600,
    'p_max' => 10
], SUPABASE_SERVICE_KEY);

if ($rl['data'] === false) {
    http_response_code(429);
    echo json_encode(['error' => 'Muitas edicoes. Tente novamente em 1 hora.']);
    exit;
}

$profileName = strtolower(trim($_POST['profileName'] ?? ''));
if (!$profileName) {
    http_response_code(400);
    echo json_encode(['error' => 'Nome do perfil nao informado.']);
    exit;
}

// Get current profile (public read) and verify ownership
$current = supabase('GET', 'profiles?profile_name=eq.' . urlencode($profileName) . '&user_id=eq.' . urlencode($userId) . '&limit=1');

if (empty($current['data'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Voce nao tem permissao para editar esse perfil.']);
    exit;
}

$oldProfile = $current['data'][0];

// Handle avatar upload
$avatarPath = $oldProfile['avatar'];
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

    // Delete old avatar
    if ($oldProfile['avatar']) {
        $oldPath = __DIR__ . '/../' . ltrim($oldProfile['avatar'], '/');
        if (file_exists($oldPath)) unlink($oldPath);
    }

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

// Parse and validate products
$products = [];
if (!empty($_POST['products'])) {
    $parsedProducts = json_decode($_POST['products'], true);
    if (is_array($parsedProducts)) {
        $productDir = __DIR__ . '/../assets/products';
        if (!is_dir($productDir)) mkdir($productDir, 0755, true);

        // Collect which existing icons are still being used
        $keptIcons = [];

        foreach ($parsedProducts as $i => $product) {
            if (empty($product['title'])) continue;
            $url = '';
            if (!empty($product['url'])) {
                $url = filter_var($product['url'], FILTER_VALIDATE_URL);
                if (!$url || !preg_match('#^https?://#i', $url)) $url = '';
            }

            $iconPath = $product['existingIcon'] ?? null;
            if ($iconPath) $keptIcons[] = $iconPath;
            $fileKey = 'product_icon_' . $i;
            if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->file($_FILES[$fileKey]['tmp_name']);

                if (in_array($mimeType, $allowedTypes) && $_FILES[$fileKey]['size'] <= 2 * 1024 * 1024) {
                    $ext = match($mimeType) {
                        'image/jpeg' => '.jpg',
                        'image/png'  => '.png',
                        'image/gif'  => '.gif',
                        'image/webp' => '.webp',
                        default      => '.jpg'
                    };
                    $filename = $profileName . '_product_' . $i . $ext;
                    move_uploaded_file($_FILES[$fileKey]['tmp_name'], $productDir . '/' . $filename);
                    $iconPath = '/assets/products/' . $filename;
                }
            }

            $products[] = [
                'title' => substr($product['title'], 0, 100),
                'description' => substr($product['description'] ?? '', 0, 200),
                'url' => $url,
                'icon' => $iconPath,
            ];
        }

        // Clean up old product icons that are no longer used
        $oldProducts = $oldProfile['products'] ?? [];
        if (is_array($oldProducts)) {
            foreach ($oldProducts as $oldProd) {
                if (!empty($oldProd['icon']) && !in_array($oldProd['icon'], $keptIcons)) {
                    $oldPath = __DIR__ . '/../' . ltrim($oldProd['icon'], '/');
                    if (file_exists($oldPath)) unlink($oldPath);
                }
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

$update = [
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
    'products'           => $products,
    'product_card_color' => (function() {
        $c = $_POST['productCardColor'] ?? '#ffffff';
        return preg_match('/^#[0-9a-fA-F]{6}$/', $c) ? $c : '#ffffff';
    })(),
    'updated_at'   => date('c'),
];

// RLS ensures only the owner can update
$res = supabase('PATCH', 'profiles?profile_name=eq.' . urlencode($profileName), $update, $token, ['Prefer: return=representation']);

if ($res['status'] === 200 && !empty($res['data'])) {
    echo json_encode(['success' => true, 'url' => '/' . $profileName]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao salvar. Tente novamente.']);
}
