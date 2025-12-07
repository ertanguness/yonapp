<?php
require_once dirname(__DIR__) . '/configs/bootstrap.php';

use App\Modules\Onboarding\Services\OnboardingService;

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Simüle: Ana kullanıcı (owner_id = 0), oturumdaki owner_id map'lenmiş (user id)
$user = (object)[
    'id' => 123,
    'owner_id' => 0,
    'full_name' => 'Test Owner',
];
$_SESSION['user'] = $user;
$_SESSION['owner_id'] = $user->id; // mevcut performLogin davranışını taklit
unset($_SESSION['onboarding_shown_this_login']);
unset($_SESSION['onboarding_dismissed']);

$service = new OnboardingService();
$service->ensureMigrations();
$service->seedDefaults();

$status = $service->getStatus((int)$user->id, null);

echo json_encode([
    'should_show' => $status['should_show'] ?? null,
    'progress' => $status['progress'] ?? null,
    'tasks_count' => $status['total_count'] ?? null,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";

