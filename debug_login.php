<?php
require 'configs/bootstrap.php';
$db = getDbConnection();
$stmt = $db->prepare("SELECT id, email, owner_id, status, roles, password FROM users WHERE email = ?");
$stmt->execute(['ertanguness@gmail.com']);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Users found: " . count($users) . "\n";
foreach ($users as $u) {
    echo "ID: " . $u['id'] . ", Role: " . $u['roles'] . ", Owner: " . $u['owner_id'] . ", Status: " . $u['status'] . "\n";
    $verify = password_verify('1234', $u['password']) ? 'TRUE' : 'FALSE';
    echo "Password '1234' Verify: " . $verify . "\n";
}
