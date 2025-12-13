<?php
// Session yapılandırması
// Süreyi değiştirmek için buradaki değeri güncelleyin (saniye cinsinden)
$session_lifetime = 3600; // 1 gün

ini_set('session.gc_maxlifetime', $session_lifetime);
ini_set('session.cookie_lifetime', $session_lifetime);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
