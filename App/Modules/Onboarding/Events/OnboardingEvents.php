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
        $ownerId = $user->owner_id ?? ($_SESSION['owner_id'] ?? null);
        if ((int)$ownerId !== 0) { return; }
        (new OnboardingService())->completeTask((int)$user->id, $taskKey, $siteId, 'auto');
    }
}
