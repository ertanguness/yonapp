<?php
session_start();
require_once '../../../../vendor/autoload.php';
$site_id = $_SESSION["site_id"];

use App\Helper\Security;
use Model\DairelerModel;
use Model\KisilerModel;
use App\Helper\Date;

$Daireler = new DairelerModel();
$Kisiler = new KisilerModel();



if (isset($_POST["action"]) && $_POST["action"] == "save_peoples") {
    $id = Security::decrypt($_POST["id"]);
    $kimlikNo = $_POST["tcPassportNo"] ?? null;

    $dogumTarihi     = Date::Ymd($_POST["birthDate"] ?? null);
    $satinAlmaTarihi = Date::Ymd($_POST["buyDate"] ?? null);
    $girisTarihi     = Date::Ymd($_POST["entryDate"] ?? null);
    $cikisTarihi     = Date::Ymd($_POST["exitDate"] ?? null);

    // Sadece yeni kayıt eklenirken kimlik numarası kontrolü yap
    if (empty($id) || $id == 0) {
        if ($Kisiler->KisiVarmi($kimlikNo)) {
            $kayitli_kisi = $kimlikNo;
        }
        if (!empty($kayitli_kisi)) {
            echo json_encode([
                "status" => "error",
                "message" => $kayitli_kisi . " kimlik numarası ile kayıt önceden yapılmıştır. Lütfen farklı bir kimlik numarası giriniz."
            ]);
            exit;
        }
    }
    // Tarih kontrolleri
    if (!empty($cikisTarihi)) {
        if (!empty($girisTarihi) && $cikisTarihi < $girisTarihi) {
            echo json_encode([
                "status" => "error",
                "message" => "Çıkış tarihi, giriş tarihinden önce olamaz."
            ]);
            exit;
        }
        if (!empty($satinAlmaTarihi) && $cikisTarihi < $satinAlmaTarihi) {
            echo json_encode([
                "status" => "error",
                "message" => "Çıkış tarihi, satın alma tarihinden önce olamaz."
            ]);
            exit;
        }
    }

    if (!empty($girisTarihi) && !empty($satinAlmaTarihi) && $girisTarihi < $satinAlmaTarihi) {
        echo json_encode([
            "status" => "error",
            "message" => "Giriş tarihi, satın alma tarihinden önce olamaz."
        ]);
        exit;
    }

    $data = [
        "id"               => $id,
        "blok_id"          => $_POST["blok_id"],
        "daire_id"         => $_POST["daire_id"],
        "kimlik_no"        => $kimlikNo,
        "adi_soyadi"       => $_POST["fullName"],
        "dogum_tarihi"     => $dogumTarihi,
        "cinsiyet"         => $_POST["gender"],
        "uyelik_tipi"      => $_POST["residentType"],
        "telefon"          => $_POST["phoneNumber"],
        "eposta"           => $_POST["email"],
        "satin_alma_tarihi" => $satinAlmaTarihi,
        "giris_tarihi"     => $girisTarihi,
        "cikis_tarihi"     => $cikisTarihi
    ];

    $lastInsertId = $Kisiler->saveWithAttr($data);

    $res = [
        "status" => "success",
        "message" => "Başarılı"
    ];
    echo json_encode($res);
}

if (isset($_POST["action"]) && $_POST["action"] == "delete_peoples") {
    $Kisiler->delete($_POST["id"]);

    $res = [
        "status" => "success",
        "message" => "Başarılı"
    ];
    echo json_encode($res);
}
