<?php
require_once 'configs/bootstrap.php';

try {
    $pdo = getDbConnection();
    echo "Database connection successful.<br>";

    // 1. Determine the correct roles table
    $roleTable = null;
    $tables = ['user_roles', 'userroles'];
    foreach ($tables as $t) {
        try {
            $pdo->query("SELECT 1 FROM $t LIMIT 1");
            $roleTable = $t;
            break;
        } catch (Exception $e) {
            continue;
        }
    }

    if (!$roleTable) {
        die("Could not find roles table (checked user_roles, userroles).");
    }
    echo "Roles table found: $roleTable<br>";

    // 2. Check columns
    $stmt = $pdo->query("SHOW COLUMNS FROM $roleTable");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columns: " . implode(', ', $columns) . "<br>";

    $colId = 'id';
    $colName = in_array('role_name', $columns) ? 'role_name' : 'roleName';
    
    // 3. Insert Superadmin Role (ID 10)
    $stmt = $pdo->prepare("SELECT * FROM $roleTable WHERE $colId = ?");
    $stmt->execute([10]);
    if ($stmt->fetch()) {
        echo "Role ID 10 already exists. Skipping insert.<br>";
    } else {
        $insertSql = "INSERT INTO $roleTable ($colId, $colName";
        $vals = "?, ?";
        $params = [10, 'Süper Admin'];

        if (in_array('firm_id', $columns)) {
            $insertSql .= ", firm_id";
            $vals .= ", 0";
        }
        if (in_array('isActive', $columns)) {
             $insertSql .= ", isActive";
             $vals .= ", 1";
        }
        
        $insertSql .= ") VALUES ($vals)";
        
        $pdo->prepare($insertSql)->execute($params);
        echo "Inserted 'Süper Admin' role with ID 10.<br>";
    }

    // 4. Update a user to be Superadmin (Optional / Demo)
    // You can manually update your user in DB: UPDATE users SET roles = 10 WHERE email = 'your@email.com';
    echo "To make a user Superadmin, execute SQL: UPDATE users SET roles = 10 WHERE email = 'your_email';<br>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
