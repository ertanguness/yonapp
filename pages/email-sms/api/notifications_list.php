<?php
require_once dirname(__DIR__, levels: 3) . '/configs/bootstrap.php';

$pdo = getDbConnection();
$pdo->exec("CREATE TABLE IF NOT EXISTS notifications (id INT AUTO_INCREMENT PRIMARY KEY, type VARCHAR(10) NOT NULL, recipients TEXT NOT NULL, subject VARCHAR(255) NULL, message TEXT NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

use Model\SmsModel;
use Model\EmailModel;

$smsModel = new SmsModel();
$emailModel = new EmailModel();

$sms = $smsModel->listAll();
$email = $emailModel->listAll();

$rows = array_merge($sms, $email);
usort($rows, function($a, $b){ return ($b['id'] <=> $a['id']); });
header('Content-Type: application/json; charset=utf-8');
echo json_encode($rows, JSON_UNESCAPED_UNICODE);