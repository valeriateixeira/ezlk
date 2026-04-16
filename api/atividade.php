<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/supabase.php';

// Now requires authentication
$user = requireAuth();

$inactiveDays = intval($_GET['dias'] ?? 30);
$now = time();

$res = supabase('GET', 'profiles?select=profile_name,created_at,last_visited_at,updated_at,visit_count', null, SUPABASE_SERVICE_KEY);

if (empty($res['data'])) {
    echo json_encode([
        'summary' => ['total' => 0, 'active' => 0, 'inactive' => 0, 'inactiveThresholdDays' => $inactiveDays],
        'profiles' => []
    ]);
    exit;
}

$result = [];
foreach ($res['data'] as $p) {
    $lastVisit = $p['last_visited_at'] ? strtotime($p['last_visited_at']) : null;
    $created = $p['created_at'] ? strtotime($p['created_at']) : null;
    $lastActivity = $lastVisit ?: $created;

    $daysSinceActivity = $lastActivity ? floor(($now - $lastActivity) / 86400) : null;
    $isActive = $daysSinceActivity !== null && $daysSinceActivity < $inactiveDays;

    $result[] = [
        'profileName'       => $p['profile_name'],
        'createdAt'         => $p['created_at'],
        'lastVisitedAt'     => $p['last_visited_at'],
        'updatedAt'         => $p['updated_at'],
        'visitCount'        => $p['visit_count'] ?? 0,
        'daysSinceActivity' => $daysSinceActivity,
        'status'            => $isActive ? 'active' : 'inactive',
    ];
}

usort($result, function ($a, $b) {
    if ($a['status'] !== $b['status']) return $a['status'] === 'inactive' ? -1 : 1;
    return ($b['daysSinceActivity'] ?? PHP_INT_MAX) <=> ($a['daysSinceActivity'] ?? PHP_INT_MAX);
});

echo json_encode([
    'summary' => [
        'total'    => count($result),
        'active'   => count(array_filter($result, fn($p) => $p['status'] === 'active')),
        'inactive' => count(array_filter($result, fn($p) => $p['status'] === 'inactive')),
        'inactiveThresholdDays' => $inactiveDays,
    ],
    'profiles' => $result,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
