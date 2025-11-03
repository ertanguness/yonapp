<?php
/**
 * Tam veritabanı yedeği alan PHP betiği
 * Örnek: backup_2025-10-15_09-30-22.sql
 */

// === AYARLAR ===
$host       = 'localhost';
$username   = 'root';
$password   = ''; // MySQL şifrenizi yazın
$database   = 'aydinogu_aydinogullari'; // Yedeklenecek veritabanı adı
$backupDir  = __DIR__ . '/backups'; // Yedeklerin kaydedileceği klasör

// === KLASÖRÜ OLUŞTUR ===
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}

// === DOSYA ADI ===
$date = date('Y-m-d_H-i-s');
$backupFile = "{$backupDir}/backup_{$database}_{$date}.sql";

// === Yedekleme Komutu ===
$command = sprintf(
    'mysqldump --user=%s --password=%s --host=%s %s > %s',
    escapeshellarg($username),
    escapeshellarg($password),
    escapeshellarg($host),
    escapeshellarg($database),
    escapeshellarg($backupFile)
);

// === KOMUTU ÇALIŞTIR ===
$output = null;
$returnVar = null;
exec($command, $output, $returnVar);

// === SONUÇ KONTROLÜ ===
if ($returnVar === 0) {
    echo "✅ Yedekleme başarıyla oluşturuldu: {$backupFile}";
} else {
    echo "❌ Yedekleme sırasında bir hata oluştu!";
}
?>
