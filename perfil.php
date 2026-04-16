<?php
require_once __DIR__ . '/api/supabase.php';

$profileName = strtolower(trim($_GET['name'] ?? ''));
$profile = null;

if ($profileName) {
    $res = supabase('GET', 'profiles?profile_name=eq.' . urlencode($profileName) . '&limit=1');
    if (!empty($res['data'][0])) {
        $profile = mapProfile($res['data'][0]);
        // Atomic visit counter — no race conditions
        supabaseRpc('increment_visit', ['p_name' => $profileName], SUPABASE_SERVICE_KEY);
    }
}

if (!$profile) {
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="google-adsense-account" content="ca-pub-1714761269980983">
        <title>ezlk - Perfil não encontrado</title>
        <link rel="icon" type="image/png" href="/assets/ezlklogo.png">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700;900&display=swap" rel="stylesheet">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
            body {
                display: flex;
                align-items: center;
                justify-content: center;
                height: 100vh;
                background: #f4f6f8;
                color: #1a1a1a;
                margin: 0;
            }
            .box {
                text-align: center;
                background: #fff;
                padding: 48px 40px;
                border-radius: 20px;
                max-width: 400px;
                border: 1px solid rgba(0,0,0,0.05);
            }
            .code { font-size: 5rem; font-weight: 900; letter-spacing: -3px; margin-bottom: 8px; }
            h1 { font-size: 1.2rem; font-weight: 800; margin-bottom: 8px; letter-spacing: -0.3px; }
            p { color: #999; font-size: 0.88rem; margin-bottom: 28px; }
            a {
                display: inline-block;
                background: #1a1a1a;
                color: #fff;
                text-decoration: none;
                padding: 14px 32px;
                border-radius: 50px;
                font-weight: 700;
                font-size: 0.85rem;
                transition: transform 0.2s;
            }
            a:hover { transform: translateY(-2px); }
        </style>
    </head>
    <body>
        <div class="box">
            <div class="code">404</div>
            <h1>Perfil não encontrado</h1>
            <p>Esse perfil ainda não existe.</p>
            <a href="/">Criar o seu agora ↗</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$name = htmlspecialchars($profile['profileName'], ENT_QUOTES, 'UTF-8');
$avatar = $profile['avatar'] ? htmlspecialchars($profile['avatar'], ENT_QUOTES, 'UTF-8') : null;
$links = $profile['links'];
$customLinks = $profile['customLinks'] ?? [];
$bgColor = htmlspecialchars($profile['bgColor'] ?? '#f4f6f8', ENT_QUOTES, 'UTF-8');
$bgImage = $profile['bgImage'] ?? null;

function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// If there's a bg image, force dark mode styling (image gets a dark overlay)
if ($bgImage) {
    $isDark = true;
} else {
    $hex = ltrim($bgColor, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
    $isDark = $luminance < 0.5;
}
$textColor = $isDark ? '#ffffff' : '#1a1a1a';
$subtextColor = $isDark ? 'rgba(255,255,255,0.5)' : '#999';

// Button color
$btnColor = $profile['btnColor'] ?? '#1a1a2e';
$btnHex = ltrim($btnColor, '#');
$btnR = hexdec(substr($btnHex, 0, 2));
$btnG = hexdec(substr($btnHex, 2, 2));
$btnB = hexdec(substr($btnHex, 4, 2));
$btnLum = (0.299 * $btnR + 0.587 * $btnG + 0.114 * $btnB) / 255;
$btnTextColor = $btnLum < 0.5 ? '#ffffff' : '#1a1a1a';

// Button shape
$btnShape = $profile['btnShape'] ?? 'rounded';
$btnRadius = match($btnShape) {
    'pill' => '50px',
    'square' => '4px',
    default => '14px'
};

// Glass effect
$btnGlass = !empty($profile['btnGlass']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google-adsense-account" content="ca-pub-1714761269980983">
    <title><?= $name ?> - ezlk</title>
    <link rel="icon" type="image/png" href="/assets/ezlklogo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&family=Iosevka+Charon&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }

        body {
            background: <?= $bgColor ?>;
            color: <?= $textColor ?>;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 20px 40px;
            -webkit-font-smoothing: antialiased;
            <?php if ($bgImage): ?>
            background-image: url('<?= e($bgImage) ?>');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            <?php endif; ?>
        }

        <?php if ($bgImage): ?>
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 0;
            pointer-events: none;
        }
        <?php endif; ?>

        .profile-card {
            position: relative;
            z-index: 1;
            max-width: 440px;
            width: 100%;
            text-align: center;
            animation: fadeUp 0.6s cubic-bezier(0.23, 1, 0.32, 1);
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .avatar-wrap {
            margin-bottom: 24px;
            display: flex;
            justify-content: center;
        }

        .avatar {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid <?= $isDark ? 'rgba(255,255,255,0.15)' : 'rgba(0,0,0,0.06)' ?>;
            box-shadow: 0 12px 40px <?= $isDark ? 'rgba(0,0,0,0.4)' : 'rgba(0,0,0,0.08)' ?>;
            transition: transform 0.3s;
        }

        .avatar:hover {
            transform: scale(1.05);
        }

        .avatar-placeholder {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: linear-gradient(135deg, #fdd835, #f9a825);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 42px;
            font-weight: 900;
            color: #1a1a1a;
            box-shadow: 0 12px 40px <?= $isDark ? 'rgba(0,0,0,0.4)' : 'rgba(0,0,0,0.08)' ?>;
        }

        .profile-name {
            font-size: 26px;
            font-weight: 900;
            margin-bottom: 4px;
            letter-spacing: -0.8px;
            <?php if ($bgImage): ?>
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
            <?php endif; ?>
        }

        .profile-handle {
            font-size: 14px;
            color: <?= $subtextColor ?>;
            margin-bottom: 36px;
            font-weight: 400;
            letter-spacing: 0.3px;
        }

        .social-icons {
            display: flex;
            justify-content: center;
            gap: 14px;
            margin-bottom: 28px;
            flex-wrap: wrap;
        }

        .social-icon {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.35s cubic-bezier(0.23, 1, 0.32, 1);
            <?php if ($btnGlass && $isDark): ?>
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            color: rgba(255, 255, 255, 0.95);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            <?php elseif ($btnGlass && !$isDark): ?>
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            color: rgba(0, 0, 0, 0.8);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.06);
            <?php else: ?>
            background: <?= e($btnColor) ?>;
            color: <?= $btnTextColor ?>;
            border: none;
            box-shadow: 0 4px 16px <?= $isDark ? 'rgba(0,0,0,0.3)' : 'rgba(0,0,0,0.08)' ?>;
            <?php endif; ?>
        }

        .social-icon:hover {
            transform: translateY(-3px) scale(1.08);
            box-shadow: 0 8px 28px <?= $isDark ? 'rgba(0,0,0,0.4)' : 'rgba(0,0,0,0.12)' ?>;
        }

        .social-icon svg {
            flex-shrink: 0;
        }

        .links {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .qr-section {
            position: relative;
            z-index: 1;
            margin-top: 40px;
            text-align: center;
        }

        .qr-section img {
            border-radius: 12px;
            background: #fff;
            padding: 10px;
        }

        .qr-label {
            font-size: 11px;
            color: <?= $subtextColor ?>;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 500;
            margin-bottom: 12px;
        }

        .link-btn {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 16px 24px;
            border-radius: <?= $btnRadius ?>;
            <?php if ($btnGlass && $isDark): ?>
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-bottom-color: rgba(255, 255, 255, 0.06);
            border-right-color: rgba(255, 255, 255, 0.06);
            color: rgba(255, 255, 255, 0.95);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2), inset 0 1px 0 rgba(255,255,255,0.08);
            <?php elseif ($btnGlass && !$isDark): ?>
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-bottom-color: rgba(0, 0, 0, 0.05);
            border-right-color: rgba(0, 0, 0, 0.05);
            color: rgba(0, 0, 0, 0.8);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.06), inset 0 1px 0 rgba(255,255,255,0.5);
            <?php else: ?>
            background: <?= e($btnColor) ?>;
            color: <?= $btnTextColor ?>;
            border: none;
            box-shadow: 0 4px 16px <?= $isDark ? 'rgba(0,0,0,0.3)' : 'rgba(0,0,0,0.08)' ?>;
            <?php endif; ?>
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
            transition: all 0.35s cubic-bezier(0.23, 1, 0.32, 1);
            letter-spacing: 0.2px;
        }

        <?php if ($btnGlass): ?>
        .link-btn::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 50%;
            background: linear-gradient(180deg, <?= $isDark ? 'rgba(255,255,255,0.08)' : 'rgba(255,255,255,0.3)' ?>, transparent);
            border-radius: <?= $btnRadius ?>;
            pointer-events: none;
        }
        .link-btn:hover {
            <?php if ($isDark): ?>
            background: rgba(255, 255, 255, 0.18);
            border-color: rgba(255, 255, 255, 0.3);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255,255,255,0.12);
            color: #ffffff;
            <?php else: ?>
            background: rgba(255, 255, 255, 0.7);
            border-color: rgba(255, 255, 255, 0.8);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08), inset 0 1px 0 rgba(255,255,255,0.6);
            color: rgba(0, 0, 0, 0.9);
            <?php endif; ?>
            transform: translateY(-3px);
        }
        <?php else: ?>
        .link-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 28px <?= $isDark ? 'rgba(0,0,0,0.4)' : 'rgba(0,0,0,0.12)' ?>;
        }
        <?php endif; ?>

        .link-btn:active {
            transform: translateY(-1px);
        }

        .link-btn svg {
            flex-shrink: 0;
            opacity: 0.7;
        }

        .footer {
            position: relative;
            z-index: 1;
            margin-top: auto;
            padding-top: 48px;
            font-size: 11px;
            color: <?= $subtextColor ?>;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 500;
        }
        .footer a {
            color: <?= $textColor ?>;
            text-decoration: none;
            font-weight: 900;
            font-family: 'Iosevka Charon', monospace;
        }

        @media (max-width: 480px) {
            body { padding: 48px 16px 32px; }
            .avatar, .avatar-placeholder { width: 96px; height: 96px; }
            .avatar-placeholder { font-size: 36px; }
            .profile-name { font-size: 22px; }
            .social-icon { width: 46px; height: 46px; }
            .social-icon svg { width: 20px; height: 20px; }
            .link-btn { padding: 14px 20px; font-size: 14px; }
        }
    </style>
</head>
<body>
    <div class="profile-card">
        <div class="avatar-wrap">
            <?php if ($avatar): ?>
                <img src="<?= $avatar ?>" alt="<?= $name ?>" class="avatar">
            <?php else: ?>
                <div class="avatar-placeholder"><?= e(strtoupper(mb_substr($profile['profileName'], 0, 1))) ?></div>
            <?php endif; ?>
        </div>

        <h1 class="profile-name"><?= $name ?></h1>
        <p class="profile-handle"><?= $name ?></p>

        <?php
        $hasSocial = !empty($links['instagram']) || !empty($links['tiktok']) || !empty($links['youtube']) || !empty($links['whatsapp']);
        ?>
        <?php if ($hasSocial): ?>
        <div class="social-icons">
            <?php if (!empty($links['instagram'])): ?>
            <a href="https://instagram.com/<?= e($links['instagram']) ?>" target="_blank" class="social-icon" title="Instagram">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
            </a>
            <?php endif; ?>

            <?php if (!empty($links['tiktok'])): ?>
            <a href="https://tiktok.com/@<?= e($links['tiktok']) ?>" target="_blank" class="social-icon" title="TikTok">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M12.53.02C13.84 0 15.14.01 16.44 0c.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
            </a>
            <?php endif; ?>

            <?php if (!empty($links['youtube'])): ?>
            <a href="https://youtube.com/@<?= e($links['youtube']) ?>" target="_blank" class="social-icon" title="YouTube">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
            </a>
            <?php endif; ?>

            <?php if (!empty($links['whatsapp'])): ?>
            <a href="https://wa.me/<?= e($links['whatsapp']) ?>" target="_blank" class="social-icon" title="WhatsApp">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($customLinks)): ?>
        <div class="links">
            <?php foreach ($customLinks as $link): ?>
                <?php if (!empty($link['title']) && !empty($link['url'])): ?>
                <a href="<?= e($link['url']) ?>" target="_blank" rel="noopener noreferrer" class="link-btn">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
                    <?= e($link['title']) ?>
                </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="qr-section">
        <p class="qr-label">Escaneie para acessar</p>
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&format=png&color=<?= ltrim($textColor, '#') ?>&bgcolor=<?= ltrim($bgImage ? '000000' : ltrim($bgColor, '#'), '#') ?>&data=<?= urlencode('https://ezlk.com.br/' . $profile['profileName']) ?>" alt="QR Code" width="130" height="130">
    </div>

    <div class="footer">Feito com <a href="/">ezlk</a></div>
</body>
</html>
