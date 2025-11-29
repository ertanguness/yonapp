<?php
namespace App\Services;

use App\Helper\Security;
use Database\Db;

class InviteLinkService
{
    public static function baseUrl(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protocol . '://' . $host;
    }

    public static function buildFullLink(int $kisiId, ?string $email, ?string $phone, string $method): string
    {
        $encKisi = Security::encrypt($kisiId);
        $params = [
            'kisi' => $encKisi,
        ];
        $query = http_build_query($params);
        return self::baseUrl() . '/register-member.php?' . $query;
    }

    public static function ensureInviteLinksTable(): void
    {
        $pdo = Db::getInstance()->connect();
        $pdo->exec("CREATE TABLE IF NOT EXISTS invite_links (
            id INT AUTO_INCREMENT PRIMARY KEY,
            token VARCHAR(32) UNIQUE NOT NULL,
            target_url TEXT NOT NULL,
            method VARCHAR(16) NOT NULL,
            expires_at DATETIME NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci");
    }

    public static function createShortLink(string $fullUrl, string $method, int $ttlMinutes = 120): string
    {
        self::ensureInviteLinksTable();
        $pdo = Db::getInstance()->connect();
        $token = bin2hex(random_bytes(5));
        $expires = date('Y-m-d H:i:s', time() + $ttlMinutes * 60);
        $stmt = $pdo->prepare('INSERT INTO invite_links (token, target_url, method, expires_at) VALUES (?, ?, ?, ?)');
        $stmt->execute([$token, $fullUrl, $method, $expires]);
        return self::baseUrl() . '/public/invite.php?t=' . $token;
    }
}