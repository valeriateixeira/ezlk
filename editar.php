<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/api/supabase.php';

$profileName = strtolower(trim($_GET['name'] ?? ''));
$profile = null;

if ($profileName) {
    $res = supabase('GET', 'profiles?profile_name=eq.' . urlencode($profileName) . '&limit=1');
    if (!empty($res['data'][0])) {
        $profile = mapProfile($res['data'][0]);
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
        <title>ezlk - Perfil nao encontrado</title>
        <link rel="icon" type="image/png" href="/assets/ezlklogo.png">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
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
            <h1>Perfil nao encontrado</h1>
            <p>Esse perfil nao existe.</p>
            <a href="/">Voltar para o inicio</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

$name = $profile['profileName'];
$links = $profile['links'];
$customLinks = $profile['customLinks'] ?? [];
$products = $profile['products'] ?? [];
$avatar = $profile['avatar'] ?? null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar <?= e($name) ?> - ezlk</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&family=Iosevka+Charon&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="icon" type="image/png" href="/assets/ezlklogo.png">
</head>
<body>
    <!-- Auth gate: shown while checking login -->
    <div id="authGate" style="display:flex;align-items:center;justify-content:center;height:100vh;background:#f4f6f8;">
        <div style="text-align:center;background:#fff;padding:48px 40px;border-radius:20px;max-width:400px;border:1px solid rgba(0,0,0,0.05);">
            <p style="color:#999;font-size:0.9rem;font-family:'Inter',sans-serif;">Verificando acesso...</p>
        </div>
    </div>

    <!-- Main edit content: hidden until auth verified -->
    <div id="editContent" style="display:none;">
    <header class="edit-header">
        <nav class="nav">
            <a href="/" class="logo">EZLK<span>,</span></a>
            <div style="display:flex;gap:10px;align-items:center;">
                <a href="/painel" style="font-size:0.8rem;color:#999;text-decoration:none;font-weight:600;font-family:'Inter',sans-serif;">Meu Perfil</a>
                <button id="logoutBtn" style="padding:8px 16px;border-radius:50px;font-size:0.78rem;font-weight:700;font-family:'Inter',sans-serif;cursor:pointer;background:#fff;color:#1a1a1a;border:1px solid rgba(0,0,0,0.1);">Sair</button>
            </div>
        </nav>
    </header>

    <section class="form-preview-section" id="editar">
        <div class="form-preview-header">
            <h2>Editar <?= e($name) ?></h2>
            <p class="form-subtitle">Altere seus dados e veja o resultado em tempo real.</p>
        </div>

        <div class="form-preview-layout">
        <div class="form-side">
        <div class="form-card">
            <form id="editForm" class="profile-form" enctype="multipart/form-data">
                <input type="hidden" name="profileName" value="<?= e($name) ?>">

                <!-- Background Color -->
                <div class="form-group">
                    <label for="bgColor">Cor de fundo da sua pagina</label>
                    <div class="color-picker-wrap">
                        <input type="color" id="bgColor" name="bgColor" value="<?= e($profile['bgColor'] ?? '#fafafa') ?>">
                        <input type="text" class="color-hex-input" id="colorLabel" value="<?= e($profile['bgColor'] ?? '#fafafa') ?>" maxlength="7" spellcheck="false">
                    </div>
                </div>

                <!-- Background Image -->
                <div class="form-group">
                    <label>Imagem de fundo</label>
                    <p class="bg-picker-label">Escolha uma imagem ou deixe em branco para usar a cor de fundo.</p>
                    <div class="bg-picker" id="bgPicker">
                        <div class="bg-option bg-option-none <?= empty($profile['bgImage']) ? 'selected' : '' ?>" data-bg="">Nenhuma</div>
                    </div>
                    <input type="hidden" name="bgImage" id="bgImage" value="<?= e($profile['bgImage'] ?? '') ?>">
                </div>

                <!-- Button Color -->
                <div class="form-group">
                    <label for="btnColor">Cor dos botoes</label>
                    <div class="color-picker-wrap">
                        <input type="color" id="btnColor" name="btnColor" value="<?= e($profile['btnColor'] ?? '#1a1a2e') ?>">
                        <input type="text" class="color-hex-input" id="btnColorLabel" value="<?= e($profile['btnColor'] ?? '#1a1a2e') ?>" maxlength="7" spellcheck="false">
                    </div>
                </div>

                <!-- Button Shape -->
                <?php $shape = $profile['btnShape'] ?? 'rounded'; ?>
                <div class="form-group">
                    <label>Formato dos botoes</label>
                    <div class="shape-picker">
                        <label class="shape-option <?= $shape === 'rounded' ? 'selected' : '' ?>" data-shape="rounded">
                            <input type="radio" name="btnShape" value="rounded" <?= $shape === 'rounded' ? 'checked' : '' ?> hidden>
                            <div class="shape-preview" style="border-radius:12px;"></div>
                            <span>Arredondado</span>
                        </label>
                        <label class="shape-option <?= $shape === 'pill' ? 'selected' : '' ?>" data-shape="pill">
                            <input type="radio" name="btnShape" value="pill" <?= $shape === 'pill' ? 'checked' : '' ?> hidden>
                            <div class="shape-preview" style="border-radius:50px;"></div>
                            <span>Pilula</span>
                        </label>
                        <label class="shape-option <?= $shape === 'square' ? 'selected' : '' ?>" data-shape="square">
                            <input type="radio" name="btnShape" value="square" <?= $shape === 'square' ? 'checked' : '' ?> hidden>
                            <div class="shape-preview" style="border-radius:4px;"></div>
                            <span>Reto</span>
                        </label>
                    </div>
                </div>

                <!-- Glass Effect -->
                <?php $isGlass = !empty($profile['btnGlass']); ?>
                <div class="form-group">
                    <label class="toggle-label">
                        <input type="checkbox" id="btnGlass" name="btnGlass" value="1" <?= $isGlass ? 'checked' : '' ?>>
                        <div class="toggle-switch"><div class="toggle-knob"></div></div>
                        <span>Efeito vidro (glass)</span>
                    </label>
                </div>

                <!-- Avatar -->
                <div class="form-group">
                    <label for="avatar">Foto de perfil</label>
                    <div class="avatar-upload" id="avatarUpload">
                        <div class="avatar-preview" id="avatarPreview">
                            <?php if ($avatar): ?>
                                <img src="<?= e($avatar) ?>" alt="<?= e($name) ?>">
                            <?php else: ?>
                                <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                            <?php endif; ?>
                        </div>
                        <span>Clique para trocar a foto</span>
                        <input type="file" id="avatar" name="avatar" accept="image/*" hidden>
                    </div>
                </div>

                <!-- Social Links -->
                <div class="form-group">
                    <label>Redes sociais</label>
                    <div class="social-input">
                        <span class="social-icon instagram-icon">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                        </span>
                        <input type="text" name="instagram" placeholder="seu.usuario" value="<?= e($links['instagram']) ?>">
                    </div>
                    <div class="social-input">
                        <span class="social-icon tiktok-icon">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12.53.02C13.84 0 15.14.01 16.44 0c.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                        </span>
                        <input type="text" name="tiktok" placeholder="seu.usuario" value="<?= e($links['tiktok']) ?>">
                    </div>
                    <div class="social-input">
                        <span class="social-icon youtube-icon">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        </span>
                        <input type="text" name="youtube" placeholder="seu.canal" value="<?= e($links['youtube']) ?>">
                    </div>
                    <div class="social-input">
                        <span class="social-icon whatsapp-icon">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </span>
                        <input type="text" name="whatsapp" placeholder="5511999999999" value="<?= e($links['whatsapp'] ?? '') ?>">
                    </div>
                </div>

                <!-- Custom Links -->
                <div class="form-group">
                    <label>Links personalizados</label>
                    <div id="customLinksContainer">
                        <?php foreach ($customLinks as $link): ?>
                        <div class="custom-link-row">
                            <input type="text" placeholder="Titulo (ex: Meu Site)" class="custom-title" value="<?= e($link['title']) ?>">
                            <input type="url" placeholder="https://..." class="custom-url" value="<?= e($link['url']) ?>">
                            <button type="button" class="remove-link-btn" title="Remover">&times;</button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" id="addLinkBtn" class="add-link-btn">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Adicionar link
                    </button>
                </div>

                <!-- Products -->
                <div class="form-group">
                    <label>Produtos</label>
                    <div class="form-group" style="margin-top:4px;">
                        <label for="productCardColor" style="font-size:0.82rem;color:#666;">Cor do card</label>
                        <div class="color-picker-wrap">
                            <input type="color" id="productCardColor" name="productCardColor" value="<?= e($profile['productCardColor'] ?? '#ffffff') ?>">
                            <input type="text" class="color-hex-input" id="productCardColorLabel" value="<?= e($profile['productCardColor'] ?? '#ffffff') ?>" maxlength="7" spellcheck="false">
                        </div>
                    </div>
                    <div id="productsContainer">
                        <?php foreach ($products as $i => $product): ?>
                        <div class="product-row">
                            <div class="product-row-header">
                                <div class="product-icon-upload" title="Clique para trocar icone"<?php if (!empty($product['icon'])): ?> data-existing="<?= e($product['icon']) ?>"<?php endif; ?>>
                                    <?php if (!empty($product['icon'])): ?>
                                        <img src="<?= e($product['icon']) ?>" alt="<?= e($product['title']) ?>">
                                    <?php else: ?>
                                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                                    <?php endif; ?>
                                    <input type="file" accept="image/*" hidden>
                                </div>
                                <div class="product-row-fields">
                                    <input type="text" placeholder="Nome do produto" class="product-title" value="<?= e($product['title'] ?? '') ?>">
                                    <input type="text" placeholder="Descricao curta" class="product-desc" value="<?= e($product['description'] ?? '') ?>">
                                    <input type="url" placeholder="https://link-do-produto.com" class="product-url" value="<?= e($product['url'] ?? '') ?>">
                                </div>
                                <button type="button" class="remove-link-btn" title="Remover">&times;</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" id="addProductBtn" class="add-product-btn">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Adicionar produto
                    </button>
                </div>

                <button type="submit" class="submit-btn" id="submitBtn">
                    Salvar alteracoes
                </button>

                <div id="formMessage" class="form-message"></div>
            </form>

            <div style="margin-top:48px;padding-top:24px;border-top:1px solid rgba(0,0,0,0.05);">
                <button type="button" id="deleteBtn" style="background:none;border:none;color:#e53935;font-size:0.82rem;font-weight:600;cursor:pointer;font-family:'Inter',sans-serif;padding:0;">Deletar minha pagina</button>
                <div id="deleteConfirm" style="display:none;margin-top:16px;">
                    <p style="font-size:0.82rem;color:#666;margin-bottom:12px;">Tem certeza? Essa acao nao pode ser desfeita.</p>
                    <div style="display:flex;gap:10px;">
                        <button type="button" id="deleteYes" style="padding:10px 20px;border-radius:10px;font-size:0.78rem;font-weight:700;font-family:'Inter',sans-serif;cursor:pointer;background:#e53935;color:#fff;border:none;">Sim, deletar</button>
                        <button type="button" id="deleteNo" style="padding:10px 20px;border-radius:10px;font-size:0.78rem;font-weight:700;font-family:'Inter',sans-serif;cursor:pointer;background:#f4f6f8;color:#1a1a1a;border:none;">Cancelar</button>
                    </div>
                    <div id="deleteMessage" class="form-message" style="margin-top:12px;"></div>
                </div>
            </div>
        </div>
        </div>

        <!-- RIGHT: Phone Preview -->
        <div class="preview-side">
          <div class="preview-sticky">
            <p class="preview-label">Preview</p>
            <div class="phone-frame">
              <div class="phone-notch"></div>
              <div class="phone-screen" id="phoneScreen" style="background:<?= e($profile['bgColor'] ?? '#fafafa') ?>">
                <div class="pv-avatar" id="pvAvatar">
                  <?php if ($avatar): ?>
                    <img src="<?= e($avatar) ?>" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                  <?php else: ?>
                    <span><?= e(strtoupper(mb_substr($name, 0, 1))) ?></span>
                  <?php endif; ?>
                </div>
                <div class="pv-name" id="pvName"><?= e($name) ?></div>
                <div class="pv-links" id="pvLinks"></div>
                <div class="pv-footer">Feito com <strong>ezlk</strong></div>
              </div>
            </div>
          </div>
        </div>
        </div>
    </section>

    <footer class="site-footer">
        <p>ezlk &mdash; Sua pagina de links, simples e gratis.</p>
        <div class="footer-dev">
          desenvolvido por
          <a href="https://kodaee.com" target="_blank" rel="noopener">
            <img src="/assets/kodaeelogo.png" alt="kodaee" class="footer-dev-logo">
            kodaee
          </a>
        </div>
        <div class="footer-socials">
          <a href="https://instagram.com/kodaee.co" target="_blank" rel="noopener" aria-label="Instagram">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M7.75 2h8.5A5.75 5.75 0 0 1 22 7.75v8.5A5.75 5.75 0 0 1 16.25 22h-8.5A5.75 5.75 0 0 1 2 16.25v-8.5A5.75 5.75 0 0 1 7.75 2Zm0 1.5A4.25 4.25 0 0 0 3.5 7.75v8.5A4.25 4.25 0 0 0 7.75 20.5h8.5A4.25 4.25 0 0 0 20.5 16.25v-8.5A4.25 4.25 0 0 0 16.25 3.5Zm4.25 3.25a5.25 5.25 0 1 1 0 10.5 5.25 5.25 0 0 1 0-10.5Zm0 1.5a3.75 3.75 0 1 0 0 7.5 3.75 3.75 0 0 0 0-7.5Zm5.5-2a.875.875 0 1 1 0 1.75.875.875 0 0 1 0-1.75Z"/></svg>
          </a>
          <a href="https://tiktok.com/@kodaee.co" target="_blank" rel="noopener" aria-label="TikTok">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M16.6 5.82A4.278 4.278 0 0 1 13.56 2h-3.09v13.67a2.59 2.59 0 0 1-2.59 2.41c-1.43 0-2.59-1.16-2.59-2.59a2.59 2.59 0 0 1 2.59-2.59c.27 0 .53.04.78.12V9.87a5.72 5.72 0 0 0-.78-.05C5.17 9.82 3 11.99 3 14.7A4.88 4.88 0 0 0 7.88 19.58a4.88 4.88 0 0 0 4.88-4.88V9.01a7.28 7.28 0 0 0 4.25 1.37V7.3a4.28 4.28 0 0 1-.41-1.48Z"/></svg>
          </a>
        </div>
    </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2/dist/umd/supabase.min.js"></script>
    <script>
        const supabaseClient = window.supabase.createClient(
            '<?= SUPABASE_URL ?>',
            '<?= SUPABASE_ANON_KEY ?>'
        );

        const profileUserId = <?= json_encode($profile['userId'] ?? null) ?>;

        // Check auth and ownership
        async function checkAccess() {
            const { data: { session } } = await supabaseClient.auth.getSession();

            if (!session) {
                window.location.href = '/login';
                return;
            }

            // Check ownership: user must own this profile
            if (profileUserId && session.user.id !== profileUserId) {
                document.getElementById('authGate').innerHTML = `
                    <div style="text-align:center;background:#fff;padding:48px 40px;border-radius:20px;max-width:400px;border:1px solid rgba(0,0,0,0.05);font-family:'Inter',sans-serif;">
                        <h2 style="font-size:1.2rem;font-weight:800;margin-bottom:8px;">Acesso negado</h2>
                        <p style="color:#999;font-size:0.88rem;margin-bottom:24px;">Voce nao tem permissao para editar esse perfil.</p>
                        <a href="/painel" style="display:inline-block;background:#1a1a1a;color:#fff;text-decoration:none;padding:14px 32px;border-radius:50px;font-weight:700;font-size:0.85rem;">Ir para meus perfis</a>
                    </div>`;
                return;
            }

            // Access granted
            document.getElementById('authGate').style.display = 'none';
            document.getElementById('editContent').style.display = 'block';
        }

        checkAccess();

        // Logout
        document.getElementById('logoutBtn').addEventListener('click', async () => {
            await supabaseClient.auth.signOut();
            window.location.href = '/';
        });

        // === Form and preview logic ===
        const form = document.getElementById('editForm');
        const formMessage = document.getElementById('formMessage');
        const submitBtn = document.getElementById('submitBtn');

        const phoneScreen = document.getElementById('phoneScreen');
        const pvAvatar = document.getElementById('pvAvatar');
        const pvName = document.getElementById('pvName');
        const pvLinks = document.getElementById('pvLinks');
        let avatarDataUrl = null;

        function esc(s) {
            const d = document.createElement('div');
            d.textContent = s;
            return d.innerHTML;
        }

        function updatePreview() {
            const bg = bgColorInput.value;
            const bgImg = bgImageInput.value;

            if (bgImg) {
                phoneScreen.style.background = `linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('${bgImg}') center/cover`;
            } else {
                phoneScreen.style.background = bg;
            }

            const r = parseInt(bg.slice(1,3), 16);
            const g = parseInt(bg.slice(3,5), 16);
            const b = parseInt(bg.slice(5,7), 16);
            const lum = (0.299*r + 0.587*g + 0.114*b) / 255;
            const isDark = bgImg ? true : lum < 0.5;
            const textColor = isDark ? '#fff' : '#1a1a2e';
            const subtextColor = isDark ? 'rgba(255,255,255,0.5)' : '#6b7280';

            pvName.style.color = textColor;

            if (avatarDataUrl) {
                pvAvatar.innerHTML = `<img src="${avatarDataUrl}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`;
            }

            const btnColor = document.getElementById('btnColor').value;
            const btnShape = document.querySelector('[name="btnShape"]:checked').value;
            const btnRadius = btnShape === 'pill' ? '50px' : btnShape === 'square' ? '4px' : '14px';
            const br2 = parseInt(btnColor.slice(1,3), 16);
            const bg2 = parseInt(btnColor.slice(3,5), 16);
            const bb2 = parseInt(btnColor.slice(5,7), 16);
            const btnLum = (0.299*br2 + 0.587*bg2 + 0.114*bb2) / 255;
            const btnTextColor = btnLum < 0.5 ? '#fff' : '#1a1a1a';
            const isGlass = document.getElementById('btnGlass').checked;

            // Social icon circle style
            let iconCircleStyle;
            if (isGlass && isDark) {
                iconCircleStyle = `width:32px;height:32px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.18);color:rgba(255,255,255,0.95);`;
            } else if (isGlass && !isDark) {
                iconCircleStyle = `width:32px;height:32px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.5);border:1px solid rgba(255,255,255,0.6);color:rgba(0,0,0,0.8);`;
            } else {
                iconCircleStyle = `width:32px;height:32px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:${btnColor};color:${btnTextColor};border:none;`;
            }

            // Link button style
            let linkStyle;
            const baseLayout = `display:flex;align-items:center;justify-content:center;gap:6px;`;
            if (isGlass) {
                if (isDark) {
                    linkStyle = `${baseLayout}background:rgba(255,255,255,0.08);backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,0.15);color:rgba(255,255,255,0.9);padding:10px 14px;border-radius:${btnRadius};font-size:11px;font-weight:600;`;
                } else {
                    linkStyle = `${baseLayout}background:rgba(255,255,255,0.5);backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,0.6);color:rgba(0,0,0,0.8);padding:10px 14px;border-radius:${btnRadius};font-size:11px;font-weight:600;`;
                }
            } else {
                linkStyle = `${baseLayout}background:${btnColor};color:${btnTextColor};padding:10px 14px;border-radius:${btnRadius};font-size:11px;font-weight:600;border:none;`;
            }

            const iconSm = 14;
            const pvIcons = {
                ig: `<svg viewBox="0 0 24 24" width="${iconSm}" height="${iconSm}" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>`,
                tk: `<svg viewBox="0 0 24 24" width="${iconSm}" height="${iconSm}" fill="currentColor"><path d="M12.53.02C13.84 0 15.14.01 16.44 0c.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>`,
                yt: `<svg viewBox="0 0 24 24" width="${iconSm}" height="${iconSm}" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>`,
                wa: `<svg viewBox="0 0 24 24" width="${iconSm}" height="${iconSm}" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>`,
                link: `<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;opacity:0.7"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>`
            };

            const ig = form.querySelector('[name="instagram"]').value.trim();
            const tk = form.querySelector('[name="tiktok"]').value.trim();
            const yt = form.querySelector('[name="youtube"]').value.trim();
            const wa = form.querySelector('[name="whatsapp"]').value.trim();

            // Social icons as circles
            let socialHtml = '';
            if (ig) socialHtml += `<div style="${iconCircleStyle}">${pvIcons.ig}</div>`;
            if (tk) socialHtml += `<div style="${iconCircleStyle}">${pvIcons.tk}</div>`;
            if (yt) socialHtml += `<div style="${iconCircleStyle}">${pvIcons.yt}</div>`;
            if (wa) socialHtml += `<div style="${iconCircleStyle}">${pvIcons.wa}</div>`;

            // Custom links as buttons
            let linksHtml = '';
            document.querySelectorAll('.custom-link-row').forEach(row => {
                const title = row.querySelector('.custom-title').value.trim();
                if (title) linksHtml += `<div style="${linkStyle}">${pvIcons.link} ${esc(title)}</div>`;
            });

            // Product cards in preview
            let productsHtml = '';
            const pccColor = document.getElementById('productCardColor').value;
            const pccR = parseInt(pccColor.slice(1,3), 16);
            const pccG = parseInt(pccColor.slice(3,5), 16);
            const pccB = parseInt(pccColor.slice(5,7), 16);
            const pccLum = (0.299*pccR + 0.587*pccG + 0.114*pccB) / 255;
            const pccTextColor = pccLum < 0.5 ? '#fff' : '#1a1a1a';
            const pccDescColor = pccLum < 0.5 ? 'rgba(255,255,255,0.6)' : '#666';

            const productCards = [];
            document.querySelectorAll('.product-row').forEach(row => {
                const title = row.querySelector('.product-title').value.trim();
                if (!title) return;
                const desc = row.querySelector('.product-desc').value.trim();
                const iconUpload = row.querySelector('.product-icon-upload');
                const iconSrc = iconUpload.dataset.preview || '';
                const iconHtml = iconSrc
                    ? `<img src="${iconSrc}" style="width:100%;height:100%;object-fit:cover;border-radius:8px;">`
                    : `<div style="width:100%;height:100%;background:rgba(0,0,0,0.08);border-radius:8px;display:flex;align-items:center;justify-content:center;color:${pccDescColor};font-size:16px;">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                       </div>`;
                productCards.push({ title, desc, iconHtml });
            });
            productCards.forEach((card, idx) => {
                const isLastOdd = (idx === productCards.length - 1) && (productCards.length % 2 === 1);
                const spanStyle = isLastOdd ? 'grid-column:1/-1;' : '';
                productsHtml += `<div style="${spanStyle}background:${pccColor};border-radius:${btnRadius};padding:8px;display:flex;flex-direction:column;align-items:center;text-align:center;gap:6px;box-sizing:border-box;overflow:hidden;">
                    <div style="width:30px;height:30px;flex-shrink:0;border-radius:6px;overflow:hidden;">${card.iconHtml}</div>
                    <div style="width:100%;min-width:0;">
                        <div style="font-size:8px;font-weight:700;color:${pccTextColor};white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${esc(card.title)}</div>
                        ${card.desc ? `<div style="font-size:7px;color:${pccDescColor};white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${esc(card.desc)}</div>` : ''}
                    </div>
                </div>`;
            });

            let html = '';
            if (socialHtml) {
                html += `<div style="display:flex;justify-content:center;gap:8px;flex-wrap:wrap;margin-bottom:12px;">${socialHtml}</div>`;
            }
            if (productsHtml) {
                html += `<div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;width:100%;margin-bottom:20px;box-sizing:border-box;">${productsHtml}</div>`;
            }
            if (linksHtml) {
                html += `<div style="display:flex;flex-direction:column;gap:8px;width:100%;">${linksHtml}</div>`;
            }

            // QR code
            const profileName = '<?= e($name) ?>';
            const qrColor = isDark ? 'ffffff' : '1a1a1a';
            const qrBg = bgImg ? '000000' : bg.replace('#', '');
            html += `<div style="margin-top:14px;text-align:center;">
                <div style="font-size:7px;color:${subtextColor};text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Escaneie para acessar</div>
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&format=png&color=${qrColor}&bgcolor=${qrBg}&data=${encodeURIComponent('https://ezlk.com.br/' + profileName)}" width="60" height="60" style="border-radius:6px;background:#fff;padding:4px;">
            </div>`;

            if (!socialHtml && !linksHtml && !productsHtml) {
                pvLinks.innerHTML = `<div class="pv-placeholder" style="color:${subtextColor}">Seus links aparecem aqui</div>`;
            } else {
                pvLinks.innerHTML = html;
            }

            const pvFooter = phoneScreen.querySelector('.pv-footer');
            if (pvFooter) pvFooter.style.color = subtextColor;
        }

        // === Background image picker ===
        const bgPicker = document.getElementById('bgPicker');
        const bgImageInput = document.getElementById('bgImage');
        const currentBgImage = bgImageInput.value;

        fetch('/api/backgrounds.php')
            .then(r => r.json())
            .then(bgs => {
                bgs.forEach(src => {
                    const opt = document.createElement('div');
                    opt.className = 'bg-option' + (src === currentBgImage ? ' selected' : '');
                    opt.dataset.bg = src;
                    opt.innerHTML = `<img src="${src}" alt="" loading="lazy">`;
                    bgPicker.appendChild(opt);
                });
                if (currentBgImage) {
                    bgPicker.querySelector('.bg-option-none').classList.remove('selected');
                }
                bgPicker.querySelectorAll('.bg-option').forEach(opt => {
                    opt.addEventListener('click', () => {
                        bgPicker.querySelectorAll('.bg-option').forEach(o => o.classList.remove('selected'));
                        opt.classList.add('selected');
                        bgImageInput.value = opt.dataset.bg;
                        updatePreview();
                    });
                });
            })
            .catch(() => {});

        // Color picker label + preview
        const bgColorInput = document.getElementById('bgColor');
        const colorLabel = document.getElementById('colorLabel');
        bgColorInput.addEventListener('input', () => {
            colorLabel.value = bgColorInput.value;
            updatePreview();
        });
        colorLabel.addEventListener('input', () => {
            const v = colorLabel.value.startsWith('#') ? colorLabel.value : '#' + colorLabel.value;
            if (/^#[0-9a-fA-F]{6}$/.test(v)) {
                bgColorInput.value = v;
                updatePreview();
            }
        });

        // Button color
        const btnColorInput = document.getElementById('btnColor');
        const btnColorLabel = document.getElementById('btnColorLabel');
        btnColorInput.addEventListener('input', () => {
            btnColorLabel.value = btnColorInput.value;
            updatePreview();
        });
        btnColorLabel.addEventListener('input', () => {
            const v = btnColorLabel.value.startsWith('#') ? btnColorLabel.value : '#' + btnColorLabel.value;
            if (/^#[0-9a-fA-F]{6}$/.test(v)) {
                btnColorInput.value = v;
                updatePreview();
            }
        });

        // Button shape
        document.querySelectorAll('.shape-option').forEach(opt => {
            opt.addEventListener('click', () => {
                document.querySelectorAll('.shape-option').forEach(o => o.classList.remove('selected'));
                opt.classList.add('selected');
                updatePreview();
            });
        });

        // Glass effect toggle
        document.getElementById('btnGlass').addEventListener('change', updatePreview);

        // Product card color
        const productCardColorInput = document.getElementById('productCardColor');
        const productCardColorLabel = document.getElementById('productCardColorLabel');
        productCardColorInput.addEventListener('input', () => {
            productCardColorLabel.value = productCardColorInput.value;
            updatePreview();
        });
        productCardColorLabel.addEventListener('input', () => {
            const v = productCardColorLabel.value.startsWith('#') ? productCardColorLabel.value : '#' + productCardColorLabel.value;
            if (/^#[0-9a-fA-F]{6}$/.test(v)) {
                productCardColorInput.value = v;
                updatePreview();
            }
        });

        // Avatar upload preview
        const avatarUpload = document.getElementById('avatarUpload');
        const avatarInput = document.getElementById('avatar');
        const avatarPreview = document.getElementById('avatarPreview');

        avatarUpload.addEventListener('click', () => avatarInput.click());
        avatarInput.addEventListener('change', () => {
            const file = avatarInput.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    avatarPreview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                    avatarDataUrl = e.target.result;
                    updatePreview();
                };
                reader.readAsDataURL(file);
            }
        });

        // Social link inputs -> preview
        form.querySelectorAll('[name="instagram"],[name="tiktok"],[name="youtube"],[name="whatsapp"]').forEach(input => {
            input.addEventListener('input', updatePreview);
        });

        // Remove link buttons (for existing links)
        document.querySelectorAll('.remove-link-btn').forEach(btn => {
            btn.addEventListener('click', () => { btn.closest('.custom-link-row').remove(); updatePreview(); });
        });

        // Existing custom link title inputs -> preview
        document.querySelectorAll('.custom-link-row .custom-title').forEach(input => {
            input.addEventListener('input', updatePreview);
        });

        // Add custom link
        const customLinksContainer = document.getElementById('customLinksContainer');
        const addLinkBtn = document.getElementById('addLinkBtn');

        addLinkBtn.addEventListener('click', () => {
            const row = document.createElement('div');
            row.className = 'custom-link-row';
            row.innerHTML = `
                <input type="text" placeholder="Titulo (ex: Meu Site)" class="custom-title">
                <input type="url" placeholder="https://..." class="custom-url">
                <button type="button" class="remove-link-btn" title="Remover">&times;</button>
            `;
            row.querySelector('.remove-link-btn').addEventListener('click', () => { row.remove(); updatePreview(); });
            row.querySelector('.custom-title').addEventListener('input', updatePreview);
            customLinksContainer.appendChild(row);
            updatePreview();
        });

        // Products
        const productsContainer = document.getElementById('productsContainer');
        const addProductBtn = document.getElementById('addProductBtn');

        function initProductRow(row) {
            const iconUpload = row.querySelector('.product-icon-upload');
            let fileInput = row.querySelector('input[type="file"]');
            if (iconUpload.dataset.existing) {
                iconUpload.dataset.preview = iconUpload.dataset.existing;
            }
            iconUpload.addEventListener('click', () => {
                const currentInput = row.querySelector('.product-icon-upload input[type="file"]');
                if (currentInput) currentInput.click();
            });
            function handleFileChange(input) {
                const file = input.files[0];
                if (file) {
                    row._selectedFile = file;
                    const reader = new FileReader();
                    reader.onload = (ev) => {
                        iconUpload.innerHTML = `<img src="${ev.target.result}" alt="Icon"><input type="file" accept="image/*" hidden>`;
                        const newInput = iconUpload.querySelector('input[type="file"]');
                        newInput.addEventListener('change', () => handleFileChange(newInput));
                        iconUpload.dataset.preview = ev.target.result;
                        delete iconUpload.dataset.existing;
                        updatePreview();
                    };
                    reader.readAsDataURL(file);
                }
            }
            fileInput.addEventListener('change', () => handleFileChange(fileInput));
            row.querySelector('.remove-link-btn').addEventListener('click', () => { row.remove(); updatePreview(); });
            row.querySelectorAll('.product-title, .product-desc').forEach(input => {
                input.addEventListener('input', updatePreview);
            });
        }

        // Init existing product rows
        document.querySelectorAll('.product-row').forEach(initProductRow);

        addProductBtn.addEventListener('click', () => {
            const row = document.createElement('div');
            row.className = 'product-row';
            row.innerHTML = `
                <div class="product-row-header">
                    <div class="product-icon-upload" title="Clique para adicionar icone">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                        <input type="file" accept="image/*" hidden>
                    </div>
                    <div class="product-row-fields">
                        <input type="text" placeholder="Nome do produto" class="product-title">
                        <input type="text" placeholder="Descricao curta" class="product-desc">
                        <input type="url" placeholder="https://link-do-produto.com" class="product-url">
                    </div>
                    <button type="button" class="remove-link-btn" title="Remover">&times;</button>
                </div>
            `;
            initProductRow(row);
            productsContainer.appendChild(row);
            updatePreview();
        });

        // Initial preview render
        updatePreview();

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            formMessage.textContent = '';
            formMessage.className = 'form-message';
            submitBtn.disabled = true;
            submitBtn.textContent = 'Salvando...';

            const { data: { session } } = await supabaseClient.auth.getSession();
            if (!session) {
                formMessage.textContent = 'Sessao expirada. Faca login novamente.';
                formMessage.className = 'form-message error';
                submitBtn.disabled = false;
                submitBtn.textContent = 'Salvar alteracoes';
                return;
            }

            const formData = new FormData(form);

            const customLinks = [];
            document.querySelectorAll('.custom-link-row').forEach(row => {
                const title = row.querySelector('.custom-title').value.trim();
                const url = row.querySelector('.custom-url').value.trim();
                if (title && url) {
                    customLinks.push({ title, url });
                }
            });
            formData.append('customLinks', JSON.stringify(customLinks));

            const productsList = [];
            document.querySelectorAll('.product-row').forEach((row, i) => {
                const title = row.querySelector('.product-title').value.trim();
                const description = row.querySelector('.product-desc').value.trim();
                const url = row.querySelector('.product-url').value.trim();
                if (title) {
                    const iconUpload = row.querySelector('.product-icon-upload');
                    const existingIcon = iconUpload.dataset.existing || '';
                    const newFile = row._selectedFile || null;
                    productsList.push({ title, description, url, existingIcon: newFile ? '' : existingIcon });
                    if (newFile) {
                        formData.append('product_icon_' + i, newFile);
                    }
                }
            });
            formData.append('products', JSON.stringify(productsList));

            try {
                const res = await fetch('/api/editar.php', {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + session.access_token },
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    formMessage.innerHTML = `Alteracoes salvas! <a href="${data.url}" target="_blank">Ver minha pagina</a>`;
                    formMessage.className = 'form-message success';
                } else {
                    formMessage.textContent = data.error;
                    formMessage.className = 'form-message error';
                }
            } catch {
                formMessage.textContent = 'Erro ao salvar. Tente novamente.';
                formMessage.className = 'form-message error';
            }

            submitBtn.disabled = false;
            submitBtn.textContent = 'Salvar alteracoes';
        });

        // Delete profile
        document.getElementById('deleteBtn').addEventListener('click', () => {
            document.getElementById('deleteConfirm').style.display = 'block';
        });
        document.getElementById('deleteNo').addEventListener('click', () => {
            document.getElementById('deleteConfirm').style.display = 'none';
        });
        document.getElementById('deleteYes').addEventListener('click', async () => {
            const deleteMsg = document.getElementById('deleteMessage');
            const deleteYes = document.getElementById('deleteYes');
            deleteYes.disabled = true;
            deleteYes.textContent = 'Deletando...';

            const { data: { session } } = await supabaseClient.auth.getSession();
            if (!session) {
                deleteMsg.textContent = 'Sessao expirada. Faca login novamente.';
                deleteMsg.className = 'form-message error';
                deleteYes.disabled = false;
                deleteYes.textContent = 'Sim, deletar';
                return;
            }

            try {
                const formData = new FormData();
                formData.append('profileName', '<?= e($name) ?>');
                const res = await fetch('/api/deletar.php', {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + session.access_token },
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    window.location.href = '/painel';
                } else {
                    deleteMsg.textContent = data.error;
                    deleteMsg.className = 'form-message error';
                }
            } catch {
                deleteMsg.textContent = 'Erro ao deletar. Tente novamente.';
                deleteMsg.className = 'form-message error';
            }
            deleteYes.disabled = false;
            deleteYes.textContent = 'Sim, deletar';
        });
    </script>
</body>
</html>
