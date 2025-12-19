<?php
// Session yapılandırması
// Süreyi değiştirmek için buradaki değeri güncelleyin (saniye cinsinden)
$session_lifetime = 86400; // 1 gün (86400 sn)

// PHP'nin çöp toplama ayarı: Bu süre dolmadan session dosyası silinmez.
ini_set('session.gc_maxlifetime', (string)$session_lifetime);

// Bazı ortamlarda (özellikle Windows/XAMPP) erken silinmeyi azaltmak için:
// Not: Bu ayarlar php.ini tarafından override edilebilir.
ini_set('session.gc_probability', '1');
ini_set('session.gc_divisor', '100');

// Cookie ayarları: Tarayıcı cookie'si de aynı süre yaşasın.
// Not: session.cookie_lifetime tek başına her zaman yeterli olmaz; session_start'tan önce
// session_set_cookie_params ile de set ediyoruz.
ini_set('session.cookie_lifetime', (string)$session_lifetime);

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

// PHP 7.3+ ile array parametreleri desteklenir.
// SameSite=Lax: çoğu login senaryosu için güvenli varsayılan.
session_set_cookie_params([
    'lifetime' => $session_lifetime,
    'path' => '/',
    'domain' => '',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kullanıcı aktif oldukça oturumun “sliding expiration” mantığıyla uzaması için
// son aktivite zamanını güncelliyoruz.
// (Sunucu tarafında gerçek silinme gc_maxlifetime ile kontrol edilir.)
$_SESSION['__last_activity'] = time();
