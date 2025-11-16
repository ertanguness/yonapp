<?php

use App\Router;

// ROUTER BAŞLAT
$router = new Router();

/* ----------------------------------------------------
|  AİDAT & BORÇLANDIRMA (DUES / DEBIT)
---------------------------------------------------- */
$router->get('aidat-turu-tanimlama', fn() => require 'pages/dues/dues-defines/manage.php');
$router->get('aidat-turu-duzenle/{id}', fn($id) => require 'pages/dues/dues-defines/manage.php');
$router->get('aidat-turu-listesi', fn() => require 'pages/dues/dues-defines/list.php');

$router->get('borclandirma', fn() => require 'pages/dues/debit/list.php');
$router->get('borclandirma-yap', fn() => require 'pages/dues/debit/manage.php');
$router->get('borclandirma-duzenle/{id}', fn($id) => require 'pages/dues/debit/manage.php');
$router->get('borclandirma-detayi/{id}', fn($id) => require 'pages/dues/debit/detail.php');

$router->get('borclandirma-kisi-ekle/{id}', fn($id) => require 'pages/dues/debit/single-manage.php');
$router->get('borclandirma-kisi-duzenle/{id}/{detay_id}', fn($id,$detay_id) => require 'pages/dues/debit/single-manage.php');

$router->get('borclarim', fn() => require 'pages/dues/user-payment/list.php');
$router->get('borclandirma-excelden-yukle/{id}', fn($id) => require 'pages/dues/debit/upload-from-xls.php');

/* ----------------------------------------------------
|  TAHSİLAT & ÖDEME (PAYMENTS & COLLECTIONS)
---------------------------------------------------- */
$router->get('yonetici-aidat-odeme', fn() => require 'pages/dues/payment/list.php');
$router->get('tahsilatlar', fn() => require 'pages/dues/collections/list.php');
$router->get('tahsilat-detayi/{id}', fn($id) => require 'pages/dues/collections/detail.php');

$router->get('onay-bekleyen-tahsilatlar', fn() => require 'pages/dues/payment/tahsilat-onay.php');
$router->get('eslesmeyen-odemeler', fn() => require 'pages/dues/payment/tahsilat-eslesmeyen.php');

$router->get('excelden-odeme-yukle', fn() => require 'pages/dues/payment/upload-from-xls.php');

$router->get('borclandirma-ozet', fn() => require 'pages/dues/payment/export/tum_sakinler_ozet_liste.php');

/* ----------------------------------------------------
|  ONLINE ÖDEME & BANKA
---------------------------------------------------- */
$router->get('online-aidat-takip', fn() => require 'pages/dues/online-dues/sorgula.php');
$router->get('banka-hesap-sorgula', fn() => require 'pages/dues/online-payment/sorgula.php');
$router->get('banka-hesap-hareketleri', fn() => require 'pages/dues/online-payment/list.php');

/* ----------------------------------------------------
|  SİTE YÖNETİMİ
---------------------------------------------------- */
$router->get('site-ekle', fn() => require 'pages/management/sites/manage.php');
$router->get('site-duzenle/{id}', fn($id) => require 'pages/management/sites/manage.php');
$router->get('siteler', fn() => require 'pages/management/sites/list.php');

/* ----------------------------------------------------
|  İŞLETME PROJESİ
---------------------------------------------------- */
$router->get('isletme-projesi', fn() => require 'pages/isletme-projesi/list.php');
$router->get('isletme-projesi-ekle', fn() => require 'pages/isletme-projesi/manage.php');
$router->get('isletme-projesi-duzenle/{id}', fn($id) => require 'pages/isletme-projesi/manage.php');
$router->get('isletme-projesi-detay/{id}', fn($id) => require 'pages/isletme-projesi/detail.php');
$router->get('isletme-projesi-pdf/{id}', fn($id) => require 'pages/isletme-projesi/pdf.php');

/* ----------------------------------------------------
|  BLOKLAR
---------------------------------------------------- */
$router->get('site-bloklari', fn() => require 'pages/management/blocks/list.php');
$router->get('blok-ekle', fn() => require 'pages/management/blocks/manage.php');
$router->get('blok-duzenle/{id}', fn($id) => require 'pages/management/blocks/manage.php');

/* ----------------------------------------------------
|  DAİRELER
---------------------------------------------------- */
$router->get('site-daireleri', fn() => require 'pages/management/apartment/list.php');
$router->get('daire-ekle', fn() => require 'pages/management/apartment/manage.php');
$router->get('daire-duzenle/{id}', fn($id) => require 'pages/management/apartment/manage.php');
$router->get('daireleri-excelden-yukle', fn() => require 'pages/management/apartment/upload-from-xls.php');

/* ----------------------------------------------------
|  DAİRE TİPLERİ
---------------------------------------------------- */
$router->get('daire-tipi-listesi', fn() => require 'pages/defines/apartment-type/list.php');
$router->get('daire-tipi-ekle', fn() => require 'pages/defines/apartment-type/manage.php');
$router->get('daire-tipi-duzenle/{id}', fn($id) => require 'pages/defines/apartment-type/manage.php');

/* ----------------------------------------------------
|  SİTE SAKİNLERİ
---------------------------------------------------- */
$router->get('site-sakinleri', fn() => require 'pages/management/peoples/list.php');
$router->get('site-sakini-ekle', fn() => require 'pages/management/peoples/manage.php');
$router->get('site-sakini-duzenle/{id}', fn($id) => require 'pages/management/peoples/manage.php');
$router->get('excelden-site-sakini-yukle', fn() => require 'pages/management/peoples/upload-from-xls.php');

$router->get('acil-durum-kisileri', fn() => require 'pages/acil-durum-kisileri/list.php');




/* ----------------------------------------------------
|  ARAÇ YÖNETİMİ
---------------------------------------------------- */
$router->get('arac-yonetimi', fn() => require 'pages/arac-yonetimi/list.php');
$router->get('arac-ekle', fn() => require 'pages/arac-yonetimi/manage.php');
$router->get('arac-duzenle/{id}', fn($id) => require 'pages/arac-yonetimi/manage.php');
$router->get('site-araclari-excel', fn() => require 'pages/arac-yonetimi/export.php');

/* ----------------------------------------------------
|  DUYURU & TALEP
---------------------------------------------------- */
$router->get('sikayet-oneri-listesi', fn() => require 'pages/notice/admin/complaints-list.php');
$router->get('sikayet-oneri--duzenle', fn() => require 'pages/duyuru-talep/admin/announcements-manage.php');

$router->get('notice/peoples/announcements-list', fn() => require 'pages/duyuru-talep/peoples/announcements-list.php');

$router->get('notice/admin/complaints-list', fn() => require 'pages/notice/admin/complaints-list.php');
$router->get('notice/peoples/complaints-list', fn() => require 'pages/duyuru-talep/peoples/complaints-list.php');
$router->get('notice/peoples/complaints-manage', fn() => require 'pages/duyuru-talep/peoples/complaints-manage.php');

$router->get('notice/admin/survey-list', fn() => require 'pages/duyuru-talep/admin/survey-list.php');
$router->get('notice/admin/survey-manage', fn() => require 'pages/duyuru-talep/admin/survey-manage.php');
$router->get('notice/admin/survey-result/{id}', fn($id) => require 'pages/duyuru-talep/admin/survey-result.php');
$router->get('notice/peoples/survey-list', fn() => require 'pages/duyuru-talep/peoples/survey-list.php');

// Yeni slug'lar
$router->get('duyuru-talep/admin/announcements-list', fn() => require 'pages/duyuru-talep/admin/announcements-list.php');
$router->get('duyuru-talep/admin/announcements-manage', fn() => require 'pages/duyuru-talep/admin/announcements-manage.php');
$router->get('duyuru-talep/peoples/announcements-list', fn() => require 'pages/duyuru-talep/peoples/announcements-list.php');
$router->get('duyuru-talep/admin/complaints-list', fn() => require 'pages/notice/admin/complaints-list.php');
$router->get('duyuru-talep/peoples/complaints-list', fn() => require 'pages/duyuru-talep/peoples/complaints-list.php');
$router->get('duyuru-talep/peoples/complaints-manage', fn() => require 'pages/duyuru-talep/peoples/complaints-manage.php');
$router->get('duyuru-talep/admin/survey-list', fn() => require 'pages/duyuru-talep/admin/survey-list.php');
$router->get('duyuru-talep/admin/survey-manage', fn() => require 'pages/duyuru-talep/admin/survey-manage.php');
$router->get('duyuru-talep/admin/survey-result/{id}', fn($id) => require 'pages/duyuru-talep/admin/survey-result.php');
$router->get('duyuru-talep/peoples/survey-list', fn() => require 'pages/duyuru-talep/peoples/survey-list.php');
/* ----------------------------------------------------
|  BAKIM – ARIZA – PERİYODİK BAKIM
---------------------------------------------------- */
$router->get('bakim-ariza-takip', fn() => require 'pages/repair/list.php');
$router->get('bakim-ariza-ekle', fn() => require 'pages/repair/manage.php');
$router->get('bakim-ariza-duzenle/{id}', fn($id) => require 'pages/repair/manage.php');

$router->get('periyodik-bakim', fn() => require 'pages/repair/care/list.php');
$router->get('periyodik-bakim-ekle', fn() => require 'pages/repair/care/manage.php');
$router->get('periyodik-bakim-duzenle/{id}', fn($id) => require 'pages/repair/care/manage.php');

$router->get('maliyet-faturalandirma', fn() => require 'pages/repair/cost/list.php');
$router->get('maliyet-fatura-ekle', fn() => require 'pages/repair/cost/manage.php');
$router->get('maliyet-fatura-duzenle/{id}', fn($id) => require 'pages/repair/cost/manage.php');

/* ----------------------------------------------------
|  GÜVENLİK & ZİYARETÇİ
---------------------------------------------------- */
$router->get('guvenlik', fn() => require 'pages/ziyaretci/guvenlik/list.php');

$router->get('guvenlik-yeni-gorev-ekle', fn() => require 'pages/ziyaretci/guvenlik/manage.php');
$router->get('guvenlik-gorev-duzenle/{id}', fn($id) => require 'pages/ziyaretci/guvenlik/manage.php');

$router->get('guvenlik-gorev-yerleri', fn() => require 'pages/ziyaretci/guvenlik/GorevYeri/list.php');
$router->get('guvenlik-gorev-yeri-ekle', fn() => require 'pages/ziyaretci/guvenlik/GorevYeri/manage.php');
$router->get('guvenlik-gorev-yeri-duzenle/{id}', fn($id) => require 'pages/ziyaretci/guvenlik/GorevYeri/manage.php');

$router->get('ziyaretci-listesi', fn() => require 'pages/ziyaretci/list.php');
$router->get('ziyaretci-ekle', fn() => require 'pages/ziyaretci/manage.php');
$router->get('ziyaretci-duzenle/{id}', fn($id) => require 'pages/ziyaretci/manage.php');

$router->get('vardiya-listesi', fn() => require 'pages/ziyaretci/guvenlik/Vardiya/list.php');
$router->get('vardiya-ekle', fn() => require 'pages/ziyaretci/guvenlik/Vardiya/manage.php');
$router->get('vardiya-duzenle/{id}', fn($id) => require 'pages/ziyaretci/guvenlik/Vardiya/manage.php');

$router->get('guvenlik-personel-listesi', fn() => require 'pages/ziyaretci/guvenlik/Personel/list.php');
$router->get('guvenlik-personel-ekle', fn() => require 'pages/ziyaretci/guvenlik/Personel/manage.php');
$router->get('guvenlik-personel-duzenle/{id}', fn($id) => require 'pages/ziyaretci/guvenlik/Personel/manage.php');

/* ----------------------------------------------------
|  PERSONEL
---------------------------------------------------- */
$router->get('personel-listesi', fn() => require 'pages/persons/list.php');
$router->get('personel-ekle', fn() => require 'pages/persons/manage.php');
$router->get('personel-duzenle/{id}', fn($id) => require 'pages/persons/manage.php');

/* ----------------------------------------------------
|  KULLANICI VE YETKİLER
---------------------------------------------------- */
$router->get('kullanici-ekle', fn() => require 'pages/kullanici/duzenle.php');
$router->get('kullanici-duzenle', fn() => require 'pages/kullanici/duzenle.php');

$router->get('kullanici-listesi', fn() => require 'pages/kullanici/list.php');
$router->get('kullanici-listesi/{id}', fn($type) => require 'pages/kullanici/list.php');

$router->get('kullanici-gruplari', fn() => require 'pages/kullanici-gruplari/list.php');
$router->get('kullanici-grubu-ekle', fn() => require 'pages/kullanici-gruplari/duzenle.php');
$router->get('kullanici-grubu-duzenle', fn() => require 'pages/kullanici-gruplari/duzenle.php');

$router->get('yetki-yonetimi/{id}', fn($role_id) => require 'pages/kullanici-gruplari/yetkiler.php');

/* ----------------------------------------------------
|  KASA VE FİNANS
---------------------------------------------------- */
$router->get('kasa-listesi', fn() => require 'pages/finans-yonetimi/kasa/list.php');
$router->get('kasa-hareketleri/{id}', fn($id) => require 'pages/finans-yonetimi/kasa/hareketler.php');
$router->get('kasa-ekle', fn() => require 'pages/finans-yonetimi/kasa/duzenle.php');

$router->get('gelir-gider-islemleri', fn() => require 'pages/finans-yonetimi/gelir-gider/list.php');
$router->get('gelir-gider-islemleri/{id}', fn($id) => require 'pages/finans-yonetimi/gelir-gider/list.php');

$router->get('excelden-gelir-gider-yukle', fn() => require 'pages/finans-yonetimi/gelir-gider/upload/upload-from-xls.php');

$router->get('gelir-gider-tipi-listesi', fn() => require 'pages/defines/gelir-gider-tipi/list.php');
$router->get('gelir-gider-tipi-ekle', fn() => require 'pages/defines/gelir-gider-tipi/manage.php');
$router->get('gelir-gider-tipi-duzenle/{id}', fn($id) => require 'pages/defines/gelir-gider-tipi/manage.php');

/* ----------------------------------------------------
|  İCRA TAKİP
---------------------------------------------------- */
$router->get('icralarim', fn() => require 'pages/icra/sakinler/list.php');
$router->get('icra-sakin-detay/{id}', fn($id) => require 'pages/icra/sakinler/manage.php');

$router->get('icra-takibi', fn() => require 'pages/icra/list.php');
$router->get('icra-detay/{id}', fn($id) => require 'pages/icra/detay/manage.php');

$router->get('icra-ekle', fn() => require 'pages/icra/manage.php');
$router->get('icra-duzenle/{id}', fn($id) => require 'pages/icra/manage.php');

/* ----------------------------------------------------
|  RAPORLAR
---------------------------------------------------- */
$router->get('raporlar', fn() => require 'pages/raporlar/main.php');
$router->get('tarihler-arasi-boc-alacak-raporu', fn() => require 'pages/raporlar/tarihler-arasi-borc-alacak.php');
$router->get('hazirun-listesi', fn() => require 'pages/raporlar/export/hazirun-listesi.php');

$router->get('rapor/{tarih}', function ($tarih) {
    echo "Rapor tarihi: " . htmlspecialchars($tarih);
});

/* ----------------------------------------------------
|  AYARLAR
---------------------------------------------------- */
$router->get('ayarlar', fn() => require 'pages/settings/manage.php');

/* ----------------------------------------------------
|  AUTH – LOGIN – PROFILE
---------------------------------------------------- */
$router->get('sign-in', fn() => require 'sign-in.php');
$router->get('kayit-ol', fn() => require 'register.php');
$router->get('kayit-basarili', fn() => require 'register-success.php');
$router->get('logout', fn() => require 'logout.php');
$router->get('forgot-password', fn() => require 'forgot_password.php');
$router->get('reset-password', fn() => require 'reset_password.php');
$router->get('profile', fn() => require 'profile.php');
$router->get('unauthorize', fn() => require 'pages/authorize.php');

/* ----------------------------------------------------
|  ANA SAYFA & GENEL
---------------------------------------------------- */
$router->get('ana-sayfa', fn() => require 'pages/home/home.php');
$router->get('index', fn() => require 'index.php');

$router->get('ssp-test', fn() => require 'pages/server_processing.php');

// Email ve SMS Bildirimleri
$router->get('bildirimler', fn() => require 'pages/email-sms/list.php');

