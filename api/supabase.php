<?php
require_once __DIR__ . '/../config.php';

/**
 * Supabase REST API (PostgREST) helper.
 *
 * @param string      $method  GET, POST, PATCH, DELETE
 * @param string      $path    e.g. "profiles?profile_name=eq.kodaee"
 * @param mixed       $body    Array (JSON-encoded automatically) or null
 * @param string|null $token   User JWT, SUPABASE_SERVICE_KEY, or null (anon key)
 * @param array       $headers Extra headers e.g. ['Prefer: return=representation']
 */
function supabase(string $method, string $path, $body = null, ?string $token = null, array $headers = []): array
{
    $url = SUPABASE_URL . '/rest/v1/' . $path;

    $h = [
        'apikey: ' . SUPABASE_ANON_KEY,
        'Content-Type: application/json',
        'Authorization: Bearer ' . ($token ?? SUPABASE_ANON_KEY),
    ];
    $h = array_merge($h, $headers);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $h,
        CURLOPT_TIMEOUT        => 10,
    ]);

    switch (strtoupper($method)) {
        case 'POST':
            curl_setopt($ch, CURLOPT_POST, true);
            if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            break;
        case 'PATCH':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            break;
        case 'DELETE':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            break;
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['status' => $httpCode, 'data' => json_decode($response, true)];
}

function supabaseRpc(string $fn, array $params = [], ?string $token = null): array
{
    return supabase('POST', 'rpc/' . $fn, $params, $token);
}

/**
 * Map a DB row (snake_case) to the app format (camelCase).
 */
function mapProfile(array $row): array
{
    return [
        'profileName'   => $row['profile_name'] ?? '',
        'userId'        => $row['user_id'] ?? '',
        'bgColor'       => $row['bg_color'] ?? '#0f0f0f',
        'bgImage'       => $row['bg_image'] ?? null,
        'btnColor'      => $row['btn_color'] ?? '#1a1a2e',
        'btnShape'      => $row['btn_shape'] ?? 'rounded',
        'btnGlass'      => $row['btn_glass'] ?? false,
        'avatar'        => $row['avatar'] ?? null,
        'links'         => $row['links'] ?? ['instagram' => '', 'tiktok' => '', 'youtube' => '', 'whatsapp' => ''],
        'customLinks'   => $row['custom_links'] ?? [],
        'visitCount'    => $row['visit_count'] ?? 0,
        'lastVisitedAt' => $row['last_visited_at'] ?? null,
        'createdAt'     => $row['created_at'] ?? null,
        'updatedAt'     => $row['updated_at'] ?? null,
    ];
}
