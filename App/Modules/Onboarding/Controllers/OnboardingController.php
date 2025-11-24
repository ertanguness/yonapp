<?php
namespace App\Modules\Onboarding\Controllers;

use App\Modules\Onboarding\Services\OnboardingService;

class OnboardingController
{
    private OnboardingService $service;

    public function __construct()
    {
        $this->service = new OnboardingService();
    }

    public function status(int $userId, ?int $siteId): array
    {
        $this->service->ensureMigrations();
        $this->service->seedDefaults();
        $status = $this->service->getStatus($userId, $siteId);
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if ($status['should_show']) {
            $_SESSION['onboarding_shown_this_login'] = true;
        }
        return $status;
    }

    public function complete(int $userId, ?int $siteId, string $taskKey): array
    {
        $this->service->ensureMigrations();
        $this->service->seedDefaults();
        $this->service->completeTask($userId, $taskKey, $siteId, 'manual');
        return ['status' => 'success'];
    }

    public function dismiss(int $userId, ?int $siteId): array
    {
        $this->service->ensureMigrations();
        $this->service->seedDefaults();
        $this->service->dismiss();
        return ['status' => 'success'];
    }
}