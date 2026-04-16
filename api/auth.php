<?php
/**
 * Supabase JWT verification helper.
 *
 * Usage:
 *   require_once __DIR__ . '/auth.php';
 *   $user = getAuthUser();       // returns ['sub' => 'uuid', 'email' => '...'] or null
 *   $user = requireAuth();       // same, but sends 401 and exits if not authenticated
 */

require_once __DIR__ . '/../config.php';

/**
 * Decode and verify a Supabase JWT.
 * Supports both HS256 (anon/service keys) and ES256 (user tokens).
 * For ES256 tokens, validates via Supabase's /auth/v1/user endpoint.
 * Returns the payload array on success, null on failure.
 */
function verifySupabaseJWT(string $token): ?array
{
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;

    [$headerB64, $payloadB64, $signature] = $parts;

    // Decode header to check algorithm
    $headerData = json_decode(base64_decode(strtr($headerB64, '-_', '+/')), true);
    $alg = $headerData['alg'] ?? '';

    // Decode payload
    $data = json_decode(base64_decode(strtr($payloadB64, '-_', '+/')), true);
    if (!$data) return null;

    // Check expiration
    if (($data['exp'] ?? 0) < time()) return null;

    if ($alg === 'HS256') {
        // Verify HMAC signature locally
        $expected = rtrim(strtr(base64_encode(
            hash_hmac('sha256', "$headerB64.$payloadB64", SUPABASE_JWT_SECRET, true)
        ), '+/', '-_'), '=');

        if (!hash_equals($expected, $signature)) return null;
        return $data;
    }

    // For ES256 (user tokens): validate via Supabase API
    $ch = curl_init(SUPABASE_URL . '/auth/v1/user');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token,
            'apikey: ' . SUPABASE_ANON_KEY,
        ],
        CURLOPT_TIMEOUT => 5,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($httpCode !== 200) return null;

    $user = json_decode($response, true);
    if (!$user || empty($user['id'])) return null;

    // Return the decoded JWT payload (has sub, email, role, etc.)
    return $data;
}

/**
 * Extract the Bearer token from the Authorization header.
 */
function getBearerToken(): ?string
{
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    // Fallback: use getallheaders()
    if (!$header && function_exists('getallheaders')) {
        $headers = getallheaders();
        $header = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    }
    if (preg_match('/^Bearer\s+(.+)$/i', $header, $m)) {
        return $m[1];
    }
    return null;
}

/**
 * Get the authenticated user from the Authorization header.
 * Returns ['sub' => 'user-uuid', 'email' => '...', ...] or null.
 */
function getAuthUser(): ?array
{
    $token = getBearerToken();
    if (!$token) return null;
    return verifySupabaseJWT($token);
}

/**
 * Require authentication — sends 401 and exits if not authenticated.
 */
function requireAuth(): array
{
    $user = getAuthUser();
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Autenticação necessária. Faça login para continuar.']);
        exit;
    }
    return $user;
}
