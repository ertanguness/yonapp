<?php
ob_start();
error_reporting(0);
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

try {
    // Session'ı sıfırla (profil kilidini aç)
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['profile_unlocked'] = false;
    $_SESSION['lock_attempts'] = 0;

    ob_end_clean();
    echo json_encode(['status' => 'success']);
    exit;

} catch (\Throwable $e) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}
