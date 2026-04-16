<?php require_once __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar - ezlk</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="icon" type="image/png" href="/assets/ezlklogo.png">
    <style>
        .auth-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f4f6f8;
            padding: 20px;
        }
        .auth-card {
            background: #fff;
            border-radius: 20px;
            padding: 48px 40px;
            max-width: 420px;
            width: 100%;
            border: 1px solid rgba(0,0,0,0.05);
        }
        .auth-card h1 {
            font-size: 1.8rem;
            font-weight: 900;
            letter-spacing: -1px;
            margin-bottom: 6px;
        }
        .auth-card .subtitle {
            color: #999;
            font-size: 0.88rem;
            margin-bottom: 32px;
        }
        .auth-card .form-group {
            margin-bottom: 18px;
        }
        .auth-card label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            margin-bottom: 6px;
            color: #1a1a1a;
        }
        .auth-card input[type="email"],
        .auth-card input[type="password"],
        .auth-card input[type="text"] {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 12px;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            outline: none;
            transition: border-color 0.2s;
        }
        .auth-card input:focus {
            border-color: #1a1a2e;
        }
        .auth-btn {
            width: 100%;
            padding: 16px;
            background: #1a1a2e;
            color: #fff;
            border: none;
            border-radius: 14px;
            font-size: 0.9rem;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: transform 0.2s, opacity 0.2s;
            margin-top: 8px;
        }
        .auth-btn:hover { transform: translateY(-2px); }
        .auth-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
        .auth-toggle {
            text-align: center;
            margin-top: 24px;
            font-size: 0.85rem;
            color: #999;
        }
        .auth-toggle a {
            color: #1a1a2e;
            font-weight: 700;
            text-decoration: none;
        }
        .auth-message {
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 0.85rem;
            margin-top: 16px;
            display: none;
        }
        .auth-message.error {
            display: block;
            background: #fff0f0;
            color: #d32f2f;
            border: 1px solid #ffd6d6;
        }
        .auth-message.success {
            display: block;
            background: #f0fff4;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 24px;
            font-size: 0.82rem;
            color: #999;
            text-decoration: none;
            font-weight: 500;
        }
        .back-link:hover { color: #1a1a1a; }
        .password-wrapper {
            position: relative;
        }
        .password-wrapper input {
            padding-right: 44px;
        }
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            color: #999;
            display: flex;
            align-items: center;
        }
        .password-toggle:hover { color: #1a1a1a; }
        .password-toggle svg { width: 20px; height: 20px; }
    </style>
</head>
<body>
    <div class="auth-page">
        <div class="auth-card">
            <a href="/" class="back-link">&larr; Voltar</a>

            <div id="loginView">
                <h1>Entrar</h1>
                <p class="subtitle">Acesse sua conta para gerenciar seus perfis.</p>
                <form id="loginForm">
                    <div class="form-group">
                        <label for="loginEmail">Email</label>
                        <input type="email" id="loginEmail" required placeholder="seu@email.com">
                    </div>
                    <div class="form-group">
                        <label for="loginPassword">Senha</label>
                        <div class="password-wrapper">
                            <input type="password" id="loginPassword" required placeholder="Sua senha" minlength="6">
                            <button type="button" class="password-toggle" data-target="loginPassword">
                                <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg class="eye-closed" style="display:none;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="auth-btn" id="loginBtn">Entrar</button>
                    <div id="loginMessage" class="auth-message"></div>
                </form>
                <p class="auth-toggle">
                    Ainda nao tem conta? <a href="#" id="showSignup">Criar conta</a>
                </p>
            </div>

            <div id="signupView" style="display:none;">
                <h1>Criar conta</h1>
                <p class="subtitle">Crie uma conta para gerenciar seus perfis.</p>
                <form id="signupForm">
                    <div class="form-group">
                        <label for="signupEmail">Email</label>
                        <input type="email" id="signupEmail" required placeholder="seu@email.com">
                    </div>
                    <div class="form-group">
                        <label for="signupPassword">Senha</label>
                        <div class="password-wrapper">
                            <input type="password" id="signupPassword" required placeholder="Minimo 6 caracteres" minlength="6">
                            <button type="button" class="password-toggle" data-target="signupPassword">
                                <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg class="eye-closed" style="display:none;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="auth-btn" id="signupBtn">Criar conta</button>
                    <div id="signupMessage" class="auth-message"></div>
                </form>
                <p class="auth-toggle">
                    Ja tem conta? <a href="#" id="showLogin">Entrar</a>
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2/dist/umd/supabase.min.js"></script>
    <script>
        if (typeof window.supabase === 'undefined' || typeof window.supabase.createClient !== 'function') {
            document.querySelector('.auth-card').innerHTML = '<p style="color:red;font-family:Inter,sans-serif;">Erro: nao foi possivel carregar o Supabase SDK. Verifique sua conexao.</p>';
            throw new Error('Supabase SDK not loaded');
        }

        const sb = window.supabase.createClient(
            '<?= SUPABASE_URL ?>',
            '<?= SUPABASE_ANON_KEY ?>'
        );

        // If already logged in, redirect to dashboard
        sb.auth.getSession().then(({ data: { session } }) => {
            if (session) window.location.href = '/painel';
        });

        // Password visibility toggle
        document.querySelectorAll('.password-toggle').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = document.getElementById(btn.dataset.target);
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                btn.querySelector('.eye-open').style.display = isPassword ? 'none' : 'block';
                btn.querySelector('.eye-closed').style.display = isPassword ? 'block' : 'none';
            });
        });

        // Toggle views
        document.getElementById('showSignup').addEventListener('click', (e) => {
            e.preventDefault();
            document.getElementById('loginView').style.display = 'none';
            document.getElementById('signupView').style.display = 'block';
        });
        document.getElementById('showLogin').addEventListener('click', (e) => {
            e.preventDefault();
            document.getElementById('signupView').style.display = 'none';
            document.getElementById('loginView').style.display = 'block';
        });

        // Login
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const msg = document.getElementById('loginMessage');
            const btn = document.getElementById('loginBtn');
            msg.className = 'auth-message';
            msg.style.display = 'none';
            btn.disabled = true;
            btn.textContent = 'Entrando...';

            const { error } = await sb.auth.signInWithPassword({
                email: document.getElementById('loginEmail').value,
                password: document.getElementById('loginPassword').value
            });

            if (error) {
                const errMsg = {
                    'Invalid login credentials': 'Email ou senha incorretos.',
                    'Email not confirmed': 'Email nao confirmado. Verifique sua caixa de entrada.',
                    'Too many requests': 'Muitas tentativas. Aguarde alguns minutos.',
                }[error.message] || error.message;
                msg.textContent = errMsg;
                msg.className = 'auth-message error';
                msg.style.display = 'block';
                btn.disabled = false;
                btn.textContent = 'Entrar';
            } else {
                window.location.href = '/painel';
            }
        });

        // Signup
        document.getElementById('signupForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const msg = document.getElementById('signupMessage');
            const btn = document.getElementById('signupBtn');
            msg.className = 'auth-message';
            msg.style.display = 'none';
            btn.disabled = true;
            btn.textContent = 'Criando...';

            const { error } = await sb.auth.signUp({
                email: document.getElementById('signupEmail').value,
                password: document.getElementById('signupPassword').value
            });

            if (error) {
                const errMsg = {
                    'User already registered': 'Esse email ja esta cadastrado.',
                    'Password should be at least 6 characters': 'A senha deve ter no minimo 6 caracteres.',
                    'Too many requests': 'Muitas tentativas. Aguarde alguns minutos.',
                    'Email rate limit exceeded': 'Muitas tentativas. Aguarde alguns minutos.',
                }[error.message] || error.message;
                msg.textContent = errMsg;
                msg.className = 'auth-message error';
                msg.style.display = 'block';
                btn.disabled = false;
                btn.textContent = 'Criar conta';
            } else {
                msg.textContent = 'Conta criada! Verifique seu email para confirmar.';
                msg.className = 'auth-message success';
                msg.style.display = 'block';
                btn.disabled = false;
                btn.textContent = 'Criar conta';
            }
        });
    </script>
</body>
</html>
