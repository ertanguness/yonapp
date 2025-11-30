<?php

use App\Helper\Date;
use App\Helper\Helper;
use App\Helper\Security;

// Kişiye mesaj göndermek için modal hazırla

// Makbuz bildirimi bayrağı: ?makbuz_bildirim=1 veya context=makbuz gibi
$makbuz_bildirim = $_GET['makbuz_bildirim'] == "true" ? true : false;


if ($kisi && !empty($kisi->telefon)) {

    // Güncel bakiye bilgisi
    $kisi_bakiye = $FinansalRaporModel->KisiFinansalDurum($kisi_id);
    $bakiyeRaw   = (float)($kisi_bakiye->bakiye ?? 0);
    $bakiyeAbs   = abs($bakiyeRaw);
    $bakiyeTxt   = Helper::formattedMoney($bakiyeAbs);
    $bakiyeState = $bakiyeRaw < 0 ? 'borç' : ($bakiyeRaw > 0 ? 'alacak' : '0');

    // Varsayılan ek metin (normal mesajda kullanılacak)
    if ($bakiyeState === 'borç') {
        $metin_ek = "{$bakiyeTxt} tutarında borç bakiyeniz bulunmaktadır. Kalan tutarın en kısa sürede ödenmesini rica ederiz.";
    } elseif ($bakiyeState === 'alacak') {
        $metin_ek = "{$bakiyeTxt} tutarında alacak bakiyeniz bulunmaktadır.";
    } else {
        $metin_ek = "Güncel bakiyeniz bulunmamaktadır.";
    }

    if ($makbuz_bildirim) {


        $tahsilat_id = Security::decrypt($_GET['id'] ?? 0);

        $makbuz = $TahsilatModel->find($tahsilat_id);
        // echo "<!-- Makbuz verisi: "; var_dump($makbuz); echo " -->";

        // Makbuz modunda gösterilecek alanlar (varsa)
        // Bu değerleri çağıran yerden GET/POST ile gönderebilirsiniz:
        // ?makbuz_tutar=100.50&makbuz_tarih=2025-10-17 14:35:00&makbuz_no=MBZ-1234
        $makbuzTutarRaw = ($makbuz->tutar ?? null);
        $makbuzTutarTxt = Helper::formattedMoney((float)$makbuzTutarRaw);

        $makbuzTarihRaw = $makbuz->islem_tarihi ?? null;
        $makbuzTarihTxt = $makbuzTarihRaw ? Date::dmY($makbuzTarihRaw, 'd.m.Y H:i') : Date::dmY(date('Y-m-d H:i'), 'd.m.Y H:i');

        $makbuzNo      = $_REQUEST['makbuz_no'] ?? ($makbuz->makbuz_no ?? '');

        $mesaj_metni = "Sayın {$kisi->adi_soyadi},

{$makbuzTarihTxt} tarihli {$makbuzTutarTxt} tutarındaki ödemeniz alınmıştır." . ($makbuzNo ? " Makbuz No: {$makbuzNo}." : "") . "

Güncel durum: {$bakiyeTxt} " . ($bakiyeState === 'borç' ? "borcunuz bulunmaktadır." : ($bakiyeState === 'alacak' ? "alacağınız bulunmaktadır." : "bakiyeniz yoktur.")) . "

{$site->site_adi} YÖNETİMİ";
    } else {
        // Normal bilgilendirme/hatırlatma mesajı
        $mesaj_metni = "Sayın {$kisi->adi_soyadi},
{$metin_ek}


{$site->site_adi} YÖNETİMİ";
    }
} else {
    // Kişi/telefon yoksa yine de bir şablon hazırla
    $mesaj_metni = "Sayın Müşterimiz,
(mesajınızı buraya yazabilirsiniz)

{$site->site_adi} YÖNETİMİ";
}

// Telefonu sadece rakamlar olacak şekilde temizle
$telefonNumarasi = preg_replace('/[^0-9]/', '', $kisi->telefon ?? '');
// 0 ile başlıyorsa kaldır (5xxxxxxxxx formatına çevir)
if (substr($telefonNumarasi, 0, 1) === '0') {
    $telefonNumarasi = substr($telefonNumarasi, 1);
}
// Geçerli bir numara mı kontrol et (10-15 karakter arası)
if (strlen($telefonNumarasi) < 10 || strlen($telefonNumarasi) > 15) {
    $telefonNumarasi = '';
}
?>


