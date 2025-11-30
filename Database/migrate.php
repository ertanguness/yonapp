<?php

require_once __DIR__ . '/../configs/bootstrap.php';

$pdo = getDbConnection();

if ($argc < 2) {
    fwrite(STDERR, "Kullanım: php Database/migrate.php <sql_dosyasi_yolu>\n");
    exit(1);
}

$sqlFile = $argv[1];
if (!file_exists($sqlFile)) {
    fwrite(STDERR, "SQL dosyası bulunamadı: {$sqlFile}\n");
    exit(1);
}

$sql = file_get_contents($sqlFile);
if ($sql === false || trim($sql) === '') {
    fwrite(STDERR, "SQL dosyası boş: {$sqlFile}\n");
    exit(1);
}

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$statements = array_filter(array_map(function ($part) {
    return trim($part);
}, preg_split('/;\s*\n|;\r?\n|;$/m', $sql)));

$applied = 0;
foreach ($statements as $stmtSql) {
    if ($stmtSql === '') { continue; }
    try {
        $pdo->exec($stmtSql);
        $applied++;
    } catch (Throwable $e) {
        fwrite(STDERR, "Hata: " . $e->getMessage() . "\n");
        exit(2);
    }
}

echo "Migration uygulandı: {$sqlFile} (" . $applied . " statement)\n";
exit(0);