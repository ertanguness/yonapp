<?php
// public/register-handler.php
require_once __DIR__ . '/../configs/bootstrap.php';
use App\Controllers\RegisterController;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'saveUser') {
    RegisterController::handleRegister($_POST);
}
// Her durumda register.php'ye yönlendir
header('Location: /register.php?email=' . urlencode($_POST['email'] ?? ''));
exit;
