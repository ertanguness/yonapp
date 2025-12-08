<?php
namespace App\Modules\Onboarding\Policies;

use App\Services\Gate;

class OnboardingPolicy
{
    public static function canManage(): bool
    {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (Gate::isResident()) { return false; }
        $user = $_SESSION['user'] ?? false;
        $ownerId = $user->owner_id ?? ($_SESSION['owner_id'] ?? null);
        return (bool)$user && (int)$ownerId === 0;
    }
}
