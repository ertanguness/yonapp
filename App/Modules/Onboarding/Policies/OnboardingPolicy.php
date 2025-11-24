<?php
namespace App\Modules\Onboarding\Policies;

class OnboardingPolicy
{
    public static function canManage(): bool
    {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        return isset($_SESSION['user']);
    }
}