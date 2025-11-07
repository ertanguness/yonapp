<?php
require_once dirname(__DIR__, levels: 4) . '/configs/bootstrap.php';

$site_id = $_SESSION["site_id"];

use App\Helper\Security;
use Model\DairelerModel;
use Model\KisilerModel;
use App\Helper\Date;
use Database\DB;

$Daireler = new DairelerModel();
$Kisiler = new KisilerModel();


$db = \getDbConnection();


if (isset($_POST["action"]) && $_POST["action"] == "save_peoples") {
    $id = Security::decrypt($_POST["id"]);
    $kimlikNo = $_POST["tcPassportNo"] ?? null;

    $dogumTarihi     = Date::Ymd($_POST["birthDate"]);
    $satinAlmaTarihi = isset($_POST["buyDate"]) ? Date::Ymd($_POST["buyDate"]) : null;
    $girisTarihi     = Date::Ymd($_POST["entryDate"]);
    $cikisTarihi     = Date::Ymd($_POST["exitDate"]) ?? null;
        $db->beginTransaction();

    try {


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

        //Email adresi  kontrolü
        if (!empty($_POST["email"])) {
            if ($Kisiler->isExistsEmail($_POST["email"]) && (empty($id) || $id == 0)) {
                $kayitli_email = $_POST["email"];
            }
            if (!empty($kayitli_email)) {
                echo json_encode([
                    "status" => "error",
                    "message" => $kayitli_email . " e-posta adresi ile kayıt önceden yapılmıştır. Lütfen farklı bir e-posta adresi giriniz."
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
            "site_id"          => $site_id,
            "blok_id"          => $_POST["blok_id"],
            "daire_id"         => $_POST["daire_id"],
            "kimlik_no"        => $kimlikNo,
            "adi_soyadi"       => $_POST["fullName"],
            "dogum_tarihi"     => $dogumTarihi,
            "cinsiyet"         => $_POST["gender"],
            "uyelik_tipi"      => $_POST["residentType"],
            "telefon"          => $_POST["phoneNumber"],
            "satin_alma_tarihi" => $satinAlmaTarihi,
            "giris_tarihi"     => $girisTarihi,
            "cikis_tarihi"     => $cikisTarihi,
            "aktif_mi" => 1,
            "kullanim_durumu" => isset($_POST["kullanim_durumu"]) ? 1 : 0

        ];
        if (!empty($_POST["email"])) {
            $data["eposta"] = $_POST["email"];
        }
        $lastInsertId = $Kisiler->saveWithAttr($data);
        $db->commit();
        $status = "success";
        $message = "Başarılı! Kişi başarıyla kaydedildi.";

    } catch (PDOException $ex) {
        $db->rollBack();
        $status = "error";
        $message = $ex->getMessage();
    }
    $res = [
        "status" => $status,
        "message" => $message,
    ];
    echo json_encode($res);
    exit;
}

if (isset($_POST["action"]) && $_POST["action"] == "delete_peoples") {
    $Kisiler->backupDelete($_POST["id"], 'kisiler');

    $res = [
        "status" => "success",
        "message" => "Başarılıyla silindi.",
    ];
    echo json_encode($res);
}
