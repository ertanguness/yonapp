<?php
namespace App\Modules\Onboarding\Events;

use App\Modules\Onboarding\Services\OnboardingService;

class OnboardingEvents
{
    public static function complete(string $taskKey, ?int $siteId = null): void
    {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $user = $_SESSION['user'] ?? null;
        if (!$user) { return; }
        (new OnboardingService())->completeTask((int)$user->id, $taskKey, $siteId, 'auto');
    }
}