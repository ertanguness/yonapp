<?php

namespace App\Helper;

class Security
{
    public static function escape($data)
    {
        return htmlentities ($data, ENT_QUOTES, 'UTF-8');
    }

    // CSRF Token
    public static function csrf()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $token = bin2hex(random_bytes(48));
            $_SESSION['csrf_token'] = $token;
        } else {
            $token = $_SESSION['csrf_token'];
        }
        return $token;
    }

    public static function checkCsrfToken()
    {
        //kullaNıcının session_token alanı ile Session'daki csrf_token alanını karşılaştırır
        $token = $_SESSION['user']->session_token ?? null;
        return hash_equals($_SESSION['csrf_token'], $token);

   
    }

    /*
    *Login Kontrolü yapılır,api sayfalarına erişim kontrolü için kullanılır
    */
    public static function checkLogin()
    {
        if (!isset($_SESSION['user'])) {
            echo "Unauthorized access. Please log in.";
            exit;
        }
    }


    public static function generatePassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function passwordControl($password, $hash)
    {
        return password_verify($password, $hash);
    }

    //encrypt//encrypt
//encrypt
public static function encrypt($data)
{
    if (empty($data)) {
        return '';
    }

    $method = "AES-256-GCM";
    $key = hash('sha256', 'mysecretkey', true);
    $iv = openssl_random_pseudo_bytes(12); // 12 byte IV
    $tag = null;

    $encrypted_data = openssl_encrypt($data, $method, $key, OPENSSL_RAW_DATA, $iv, $tag);

    // IV + TAG + ENCRYPTED sıralaması önemli
    $combined = $iv . $tag . $encrypted_data;

    // base64 encode + URL encode
    $encoded = base64_encode($combined);
    $urlSafe = strtr($encoded, ['+' => '-', '/' => '_', '=' => '*']);

    return rawurlencode($urlSafe);
}


public static function decrypt($data)
{
    if (empty($data) || $data === '0') return 0;

    $method = "AES-256-GCM";
    $key = hash('sha256', 'mysecretkey', true);

    // URL decode + tersine base64 düzeltmeleri
    $decoded = rawurldecode($data);
    $base64 = strtr($decoded, ['-' => '+', '_' => '/', '*' => '=']);

    $combined = base64_decode($base64);

    if (strlen($combined) < 28) {
        return null; // veri bozuk
    }

    $iv = substr($combined, 0, 12);
    $tag = substr($combined, 12, 16);
    $encrypted_data = substr($combined, 28);

    return openssl_decrypt($encrypted_data, $method, $key, OPENSSL_RAW_DATA, $iv, $tag);
}

}