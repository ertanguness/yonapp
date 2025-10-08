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

//Borçlarım
$router->get('borclarim', function () {
    require 'pages/dues/user-payment/list.php';
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


                        // SİTE YÖNETİMİ
//site ekle
$router->get('site-ekle', function () {
    require 'pages/management/sites/manage.php';
});
//site-duzenle
$router->get('site-duzenle/{id}', function ($id) {
    require 'pages/management/sites/manage.php';
});

//siteler listesi
$router->get('siteler', function () {
    require 'pages/management/sites/list.php';
});

//Bloklar
$router->get('site-bloklari', function () {
    require 'pages/management/blocks/list.php';
});

//Blok Ekle
$router->get('blok-ekle', function () {
    require 'pages/management/blocks/manage.php';
});
//Blok Düzenle
$router->get('blok-duzenle/{id}', function ($id) {
    require 'pages/management/blocks/manage.php';
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
//Site sakini ekle
$router->get('site-sakini-ekle', function () {
    require 'pages/management/peoples/manage.php';
});
//Site sakini düzenle
$router->get('site-sakini-duzenle/{id}', function ($id) {
    require 'pages/management/peoples/manage.php';
});
// Araç Yönetimi
$router->get('arac-yonetimi', function () {
    $_GET['tab'] = 'car';
    require 'pages/management/peoples/manage.php';
});

// Acil Durum Yönetimi
$router->get('acil-durum-yonetimi', function () {
    $_GET['tab'] = 'emergency';
    require 'pages/management/peoples/manage.php';
});



$router->get('excelden-site-sakini-yukle', function () {
    require 'pages/management/peoples/upload-from-xls.php';
});

                        // REPAİR(BAKIM ONARIM)
//Bakım ve arıza yönetimi
$router->get('bakim-ariza-takip', function () {
    require 'pages/repair/list.php';
});
$router->get('bakim-ariza-ekle', function () {
    require 'pages/repair/manage.php';
});
$router->get('bakim-ariza-duzenle/{id}', function ($id) {
    require 'pages/repair/manage.php';
});
//periyodik bakım
$router->get('periyodik-bakim', function () {
    require 'pages/repair/care/list.php';
});
$router->get('periyodik-bakim-ekle', function () {
    require 'pages/repair/care/manage.php';
});
$router->get('periyodik-bakim-duzenle/{id}', function ($id) {
    require 'pages/repair/care/manage.php';
});
//maliyet ve faturalandırma
$router->get('maliyet-faturalandirma', function () {
    require 'pages/repair/cost/list.php';
});
$router->get('maliyet-fatura-ekle', function () {
    require 'pages/repair/cost/manage.php';
});
$router->get('maliyet-fatura-duzenle/{id}', function ($id) {
    require 'pages/repair/cost/manage.php';
});

                                //GÜVENLİK VE ZİYARETÇİ 
//Görev Yeri listesi
$router->get('guvenlik-gorev-yerleri', function () {
    require 'pages/ziyaretci/guvenlik/GorevYeri/list.php';
});   
//Görev Yeri ekle
$router->get('guvenlik-gorev-yeri-ekle', function () {
    require 'pages/ziyaretci/guvenlik/GorevYeri/manage.php';
}); 
//Görev Yeri duzenle
$router->get('guvenlik-gorev-yeri-duzenle/{id}', function ($id) {
    require 'pages/ziyaretci/guvenlik/GorevYeri/manage.php';
});  
//Güvenlik Yönetimi
$router->get('guvenlik', function () {
    require 'pages/ziyaretci/guvenlik/list.php';
});                             
                         
// Güvenlik Görev Yeri Ekle 
$router->get('guvenlik-yeni-gorev-ekle', function () {
    require 'pages/ziyaretci/guvenlik/manage.php';
}); 
// Güvenlik Görev Yeri Duzenle 
$router->get('guvenlik-gorev-duzenle/{id}', function ($id) {
    require 'pages/ziyaretci/guvenlik/manage.php';
}); 
// Ziyaretciler
$router->get('ziyaretci-listesi', function () {
    require 'pages/ziyaretci/list.php';
});
// ziyaretci ekle
$router->get('ziyaretci-ekle', function () {
    require 'pages/ziyaretci/manage.php';
});
// ziyaretci duzenle
$router->get('ziyaretci-duzenle/{id}', function ($id) {
    require 'pages/ziyaretci/manage.php';
});
// Personel Listesi
$router->get('personel-listesi', function () {
    require 'pages/ziyaretci/guvenlik/Personel/list.php';
});
// Personel Ekle
$router->get('personel-ekle', function () {
    require 'pages/ziyaretci/guvenlik/Personel/manage.php';
});
// Personel Düzenle
$router->get('personel-duzenle/{id}', function ($id) {
    require 'pages/ziyaretci/guvenlik/Personel/manage.php';
});
// Vardiya listesi
$router->get('vardiya-listesi', function () {
    require 'pages/ziyaretci/guvenlik/Vardiya/list.php';
});  
$router->get('vardiya-ekle', function () {
    require 'pages/ziyaretci/guvenlik/Vardiya/manage.php';
});  
$router->get('vardiya-duzenle/{id}', function ($id) {
    require 'pages/ziyaretci/guvenlik/Vardiya/manage.php';
});  
                                    //KULLANICILAR 
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


//Gelir gider işlemleri
$router->get('gelir-gider-islemleri', function () {
    require 'pages/finans-yonetimi/gelir-gider/list.php';
});





//----------------İCRA İŞLEMLERİ BAŞLANGIÇ----------------
//Sakin İcraları listesi
$router->get('icralarim', function () {
    require 'pages/icra/sakinler/list.php';
});

//Sakin İcra detayı
$router->get('icra-sakin-detay/{id}', function ($id) {
    require 'pages/icra/sakinler/manage.php';
});

//İcra Listesi
$router->get('icra-takibi', function () {
    require 'pages/icra/list.php';
});
//İcra Detay 
$router->get('icra-detay/{id}', function ($id) {
    require 'pages/icra/detay/manage.php';
});

//İcra Ekle
$router->get('icra-ekle', function () {
    require 'pages/icra/manage.php';
});
//İcra Düzenle
$router->get('icra-duzenle/{id}', function ($id) {
    require 'pages/icra/manage.php';
});

                                //TANIMLAMALAR
//Daire Tipi Tanımlama
$router->get('daire-turu-listesi', function () {
    require 'pages/defines/apartment-type/list.php';
});
//Daire Tipi Ekle
$router->get('daire-turu-ekle', function () {
    require 'pages/defines/apartment-type/manage.php';
});
//Daire Tipi Düzenle
$router->get('daire-turu-duzenle/{id}', function ($id) {
    require 'pages/defines/apartment-type/manage.php';
});

                            //AYARLAR
//ayarlar
$router->get('ayarlar', function () { 
    require 'pages/ayarlar/manage.php';
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
