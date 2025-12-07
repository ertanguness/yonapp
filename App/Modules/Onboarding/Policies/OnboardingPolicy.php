<?php
namespace App\Modules\Onboarding\Policies;

class OnboardingPolicy
{
    public static function canManage(): bool
    {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $user = $_SESSION['user'] ?? null;
        $ownerId = $user->owner_id ?? ($_SESSION['owner_id'] ?? null);
        return (bool)$user && (int)$ownerId === 0;
    }
}
