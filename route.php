<?php



use App\Router;

// Router başlat
$router = new Router();


$router->get('aidat-turu-tanimlama', function () {
    require 'pages/dues/dues-defines/manage.php';
});

//Aidat türü güncelleme
$router->get('aidat-turu-duzenle/{id}', function ($id) {
    require 'pages/dues/dues-defines/manage.php';
});


$router->get('aidat-turu-listesi', function () {
    require 'pages/dues/dues-defines/list.php';
});


$router->get('borclandirma', function () {
    require 'pages/dues/debit/list.php';
});
$router->get('borclandirma-yap', function () {
    require 'pages/dues/debit/manage.php';
});

//Borçlandırma Düzenle
$router->get('borclandirma-duzenle/{id}', function ($id) {
    require 'pages/dues/debit/manage.php';
});

//Borçlandirma Detayi 
$router->get('borclandirma-detayi/{id}', function ($id) {
    require 'pages/dues/debit/detail.php';
});



//Yönetici aidat ödeme sayfası
$router->get('yonetici-aidat-odeme', function () {
    require 'pages/dues/payment/list.php';
});

//Yapılan tahsilatlar sayfası
$router->get('tahsilatlar', function () {
    require 'pages/dues/collections/list.php';
});

//Tahsilat detayı
$router->get('tahsilat-detayi/{id}', function ($id) {
    require 'pages/dues/collections/detail.php';
});




//Ana Sayfa
$router->get('ana-sayfa', function () {
    require 'pages/home.php';
});



//Site Yönetimi
$router->get('site-ekle', function () {
    require 'pages/management/sites/manage.php';
});
$router->get('site-duzenle/{id}', function ($id) {
    require 'pages/management/sites/manage.php';
});


//Bloklar
$router->get('site-bloklari', function () {
    require 'pages/management/blocks/list.php';
});

//Daireler
$router->get('site-daireleri', function () {
    require 'pages/management/apartment/list.php';
});

//Daire Ekle
$router->get('daire-ekle', function () {
    require 'pages/management/apartment/manage.php';
});
//Daire düzenle
$router->get('daire-duzenle/{id}', function ($id) {
    require 'pages/management/apartment/manage.php';
});

//Site Sakinleri
$router->get('site-sakinleri', function () {
    require 'pages/management/peoples/list.php';
});
//Site sakini düzenle
$router->get('site-sakini-duzenle/{id}', function ($id) {
    require 'pages/management/peoples/manage.php';
});


$router->get('siteler', function () {
    require 'pages/management/sites/list.php';
});

//Kullanıcı Ekle
$router->get('kullanici-ekle' , function () {
    require 'pages/kullanici/duzenle.php';
});

//Kullanıcı Düzenle
$router->get('kullanici-duzenle', function () {
    require 'pages/kullanici/duzenle.php';
});

//Kullanıcı Listesi
$router->get('kullanici-listesi', function () {
    require 'pages/kullanici/list.php';
});
$router->get('kullanici-listesi/{id}', function ($type) {
    require 'pages/kullanici/list.php';
});

//Kullanıcı grupları
$router->get('kullanici-gruplari', function () {
    require 'pages/kullanici-gruplari/list.php';
});


//Kullanıcı grbu ekle
$router->get('kullanici-grubu-ekle', function () {
    require 'pages/kullanici-gruplari/duzenle.php';
});


//Kullanıcı grubu düzenle
$router->get('kullanici-grubu-duzenle', function () {
    require 'pages/kullanici-gruplari/duzenle.php';
});


//Yetki Yönetimi
$router->get('yetki-yonetimi/{id}', function ($role_id) {
    require 'pages/kullanici-gruplari/yetkiler.php';
});


//Kasa Listesi
$router->get('kasa-listesi', function () {
    require 'pages/finans-yonetimi/kasa/list.php';
});

//Kasa Hareketleri
$router->get('kasa-hareketleri/{id}', function ($id) {
   
    require 'pages/finans-yonetimi/kasa/hareketler.php';
});



// Ziyaretciler
$router->get('ziyaretci-listesi', function () {
    require 'pages/ziyaretci/list.php';
});


//İcra İşlemleri
$router->get('icralarim', function () {
    require 'pages/levy/people/list.php';
});

//İcra Detay
$router->get('icra-detay/{id}', function ($id) {
    require 'pages/levy/people/manage.php';
});




// ROUTES tanımla
$router->get('index', function () {
    require 'index.php';
});




//Onay Bekleyen Tahsilatlar
$router->get('onay-bekleyen-tahsilatlar', function () {
    require 'pages/dues/payment/tahsilat-onay.php';
});



$router->get('profile', function () {
    require 'profile.php';
});

$router->get('forgot-password', function () {
    require 'forgot_password.php';
});

//Reset Password
$router->get('reset-password', function () {
    require 'reset_password.php';
});



// Giriş yap
$router->get('sign-in', function () {
    require 'sign-in.php';
});

// Kayıt ol
$router->get('sign-up', function () {
    require 'sign-up.php';
});


//Çıkış yap
$router->get('logout', function () {
    require 'logout.php';
});

// Yetkiniz yok sayfası
$router->get('unauthorize', function () {
    
     require 'pages/authorize.php';
});





// Parametreli örnek: /rapor/2025-08-17
$router->get('rapor/{tarih}', function ($tarih) {
    // $tarih değişkeni dinamik geliyor
    echo "Rapor tarihi: " . htmlspecialchars($tarih);
});

// // Çalıştır
// $url = $_GET['p'] ?? '';
// $url = rtrim($url, '/');
// $router->dispatch($url);

//echo "İstenen URL: " . htmlspecialchars($url) . "<br>"; // Debug için
// $url = rtrim($url, '/');
// $router->dispatch($url);
