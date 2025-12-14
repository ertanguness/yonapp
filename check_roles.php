<?php
require_once 'configs/bootstrap.php';
$db = getDbConnection();
$stmt = $db->query("SELECT * FROM user_roles");
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($roles);
echo "</pre>";
