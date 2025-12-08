<?php
require_once dirname(__DIR__) . '/configs/bootstrap.php';
use App\Modules\Onboarding\Services\OnboardingService;
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$_SESSION['user'] = (object)[
  'id' => 777,
  'owner_id' => 0,
  'roles' => 3,
  'full_name' => 'Resident User'
];
$_SESSION['owner_id'] = 777;
unset($_SESSION['onboarding_shown_this_login'], $_SESSION['onboarding_dismissed']);
$service = new OnboardingService();
$service->ensureMigrations();
$service->seedDefaults();
$status = $service->getStatus(777, null);
echo json_encode([
  'should_show' => $status['should_show'] ?? null,
  'completed_count' => $status['completed_count'] ?? null,
  'total_count' => $status['total_count'] ?? null
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
