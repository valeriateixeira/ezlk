<?php require_once __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="google-adsense-account" content="ca-pub-1714761269980983">
  <title>EZLK — Easy Link | Sua página de links grátis</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&family=Iosevka+Charon&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/css/style.css">
  <link rel="icon" type="image/png" href="/assets/ezlklogo.png">
</head>
<body>
  <!-- NAV -->
  <header class="hero">
    <nav class="nav">
      <div class="logo">EZLK<span>,</span> <span class="logo-full">Easy Link</span></div>
      <div id="authNav" style="display:flex;gap:10px;align-items:center;">
        <a href="/login" id="navLogin" class="hero-cta" style="font-size:0.8rem;padding:10px 22px;display:none;">Entrar</a>
        <a href="/painel" id="navDashboard" style="font-size:0.8rem;color:#1a1a1a;text-decoration:none;font-weight:600;display:none;">Meu Perfil</a>
        <button id="navLogout" style="padding:8px 16px;border-radius:50px;font-size:0.78rem;font-weight:700;font-family:'Inter',sans-serif;cursor:pointer;background:#1a1a2e;color:#fff;border:none;display:none;">Sair</button>
      </div>
    </nav>

    <!-- HERO -->
    <div class="hero-grid">
      <div class="hero-left">
        <span class="hero-since">Crie seu link na bio</span>
        <h1>Link na<br><span class="accent">Bio</span></h1>
        <p class="hero-desc">Crie sua lista de links personalizada e compartilhe tudo com um único link. <strong>100% gratuito</strong>.</p>
        <span class="hero-tags">Instagram__TikTok__YouTube__WhatsApp</span>
        <a href="/painel" class="hero-cta">Criar minha página ↗</a>
      </div>

      <div class="hero-right">
        <p>Seu link na bio com todas as suas redes, portfólio e contato em um só lugar. Sem pagar nada, para sempre.</p>
        <div class="hero-hashtag">#SEUSLINKS</div>
      </div>
    </div>

    <div class="hero-photos">
      <img src="/assets/websiteimages/girl_glasses.jpg" alt="Criador de conteúdo" decoding="async" width="420" height="520">
    </div>

    <div class="hero-huge-text">— Style & Links</div>

    <a href="/painel" class="floating-badge" style="top: 35%; left: 38%;">
      <span class="badge-arrow">↗</span>
      <span class="badge-text">Crie<br>Agora!</span>
    </a>
  </header>

  <!-- FEATURES -->
  <section class="features-photo">
    <div class="feature-block" style="background-image: url('/assets/websiteimages/fashion_girl.jpg');">
      <div class="feature-overlay">
        <span class="feature-number">01</span>
        <h3>Rápido e fácil</h3>
        <p>Preencha o formulário e sua página fica pronta na hora. Sem cadastro complicado.</p>
      </div>
    </div>
    <div class="feature-block" style="background-image: url('/assets/websiteimages/man_headset.jpg');">
      <div class="feature-overlay">
        <span class="feature-number">02</span>
        <h3>Totalmente grátis</h3>
        <p>Sem planos pagos, sem pegadinha. Sua página de links é gratuita para sempre.</p>
      </div>
    </div>
    <div class="feature-block" style="background-image: url('/assets/websiteimages/worker_girl.jpg');">
      <div class="feature-overlay">
        <span class="feature-number">03</span>
        <h3>Seu link único</h3>
        <p>Escolha seu nome e compartilhe: <strong>ezlk.com.br/seunome</strong></p>
      </div>
    </div>
  </section>

  <!-- BANNER -->
  <section class="banner-section" style="background-image: url('/assets/websiteimages/girls_posing.jpg');">
    <div class="banner-overlay">
      <span class="banner-tag">Para todos</span>
      <h2>Um link para todos os seus perfis, portfólio, contato e serviços</h2>
      <div class="banner-items">
        <span>Redes Sociais</span>
        <span>Moda & Estilo</span>
        <span>Música & Podcasts</span>
        <span>Negócios</span>
        <span>Looks & Collabs</span>
        <span>Sua Loja</span>
      </div>
      <a href="/painel" class="banner-cta">Comece agora ↗</a>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="site-footer">
    <p>EZLK — Easy Link | Sua página de links, simples e grátis.</p>
    <div class="footer-dev">
      desenvolvido por
      <a href="https://kodaee.com" target="_blank" rel="noopener">
        <img src="assets/kodaeelogo.png" alt="kodaee" class="footer-dev-logo" loading="lazy">
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

  <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2/dist/umd/supabase.min.js"></script>
  <script>
    if (typeof window.supabase !== 'undefined' && typeof window.supabase.createClient === 'function') {
      const sb = window.supabase.createClient(
        '<?= SUPABASE_URL ?>',
        '<?= SUPABASE_ANON_KEY ?>'
      );

      sb.auth.getSession().then(({ data: { session } }) => {
        if (session) {
          document.getElementById('navDashboard').style.display = 'inline';
          document.getElementById('navLogout').style.display = 'inline';
        } else {
          document.getElementById('navLogin').style.display = 'inline';
        }
      });

      document.getElementById('navLogout').addEventListener('click', async () => {
        await sb.auth.signOut();
        window.location.reload();
      });
    } else {
      document.getElementById('navLogin').style.display = 'inline';
    }
  </script>
</body>
</html>
