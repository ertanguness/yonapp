<?php
session_start();
require_once '../../../vendor/autoload.php';


use App\Helper\Security;
use App\Helper\Date;
use App\Helper\Helper;
use App\Helper\Error;


use Model\BorclandirmaModel;
use Model\BorclandirmaDetayModel;
use Model\DueModel;
use Model\BloklarModel;
use Model\KisilerModel;

$Borc = new BorclandirmaModel();
$BorcDetay = new BorclandirmaDetayModel();
$Due = new DueModel();
$Bloklar = new BloklarModel();
$Kisiler = new KisilerModel();



/**BORÇLANDIRMA YAP */
if ($_POST["action"] == "borclandir") {
    $site_id = $_SESSION['site_id'];
    $id = Security::decrypt($_POST["id"]);
    $user_id = $_SESSION["user"]->id;
    $borclandirma_turu = $_POST["hedef_tipi"];

    $data = [
        "id" => $id,
        "site_id" => $site_id,
        "borc_tipi_id" => Security::decrypt($_POST["borc_baslik"]),
        "tutar" => Helper::formattedMoneyToNumber($_POST["tutar"]),
        "baslangic_tarihi" => Date::Ymd($_POST["baslangic_tarihi"]),
        "bitis_tarihi" => Date::Ymd($_POST["bitis_tarihi"]),
        "ceza_orani" => $_POST["ceza_orani"],
        "aciklama" => $_POST["aciklama"],
        "hedef_tipi" => $borclandirma_turu,
    ];

    $lastInsertId = $Borc->saveWithAttr($data) ?? $id;;

    $data = [];

    $data = [
        "id" => $id,
        "borclandirma_id" =>  Security::decrypt($lastInsertId),
        "borc_adi" => $_POST["borc_adi"],
        "tutar" => Helper::formattedMoneyToNumber($_POST["tutar"]),
        "baslangic_tarihi" => Date::Ymd($_POST["baslangic_tarihi"]),
        "bitis_tarihi" => Date::Ymd($_POST["bitis_tarihi"]),
        "ceza_orani" => $_POST["ceza_orani"],
        "aciklama" => $_POST["aciklama"],
        "hedef_tipi" => $borclandirma_turu,
    ];

    //Borçlandırma tipi kontrol ediliyor
    if ($borclandirma_turu == "all") {
        //Tüm siteye borçlandırma yapılıyor
        //Sitenin tüm aktif kişilerini getir
        $kisiler = $Kisiler->SiteKisileri($site_id);
        foreach ($kisiler as $kisi) {
            $data["kisi_id"] = $kisi->id;
            $data["blok_id"] = $kisi->blok_id; // Blok ID'sini de ekliyoruz
            $BorcDetay->saveWithAttr($data);
        }
    } elseif ($borclandirma_turu == "block") {
        //Bloklara borçlandırma yapılıyor
        //Blogun aktif kişilerini getir
        $kisiler = $Kisiler->BlokKisileri(Security::decrypt($_POST["block_id"]));
        foreach ($kisiler as $kisi) {
            $data["kisi_id"] = $kisi->id;
            $data["blok_id"] = Security::decrypt($_POST["block_id"]);
            $BorcDetay->saveWithAttr($data);
        }
    } elseif ($borclandirma_turu == "kisi") {
        //Kişilere borçlandırma yapılıyor
        $person_ids = $_POST["person_ids"];
        foreach ($person_ids as $person_id) {
            $data["person_id"] = Security::decrypt($person_id);
            $BorcDetay->saveWithAttr($data);
        }
    }

    $res = [
        "status" => "success",
        "message" => "İşlem Başarı ile tamamlandı! son eklenen id . " . $borclandirma_turu 
    ];
    echo json_encode($res);
}

if ($_POST["action"] == "delete_debit") {
    try {
        $Borc->delete($_POST["id"]);

        $res = [
            "status" => "success",
            "message" => "Borçlandırma başarı ile kaydedildi!"
        ];
    } catch (Exception $e) {
        $res = Error::handlePDOException($e);
    }
    echo json_encode($res);
}


if ($_POST["action"] == "get_due_info") {
    $id = Security::decrypt($_POST["id"]);

    $data = $Due->find($id);

    $res = [
        "status" => "success",
        "data" => $data
    ];

    echo json_encode($res);
}

//Sitenin bloklarını listele
if ($_POST["action"] == "get_blocks") {
    //$id = Security::decrypt($_POST["id"]) ;
    $site_id = $_SESSION["site_id"]; // Kullanıcının site_id'sini alıyoruz

    $data = $Bloklar->SiteBloklari($site_id);

    //id'yi şifreli hale getiriyoruz
    foreach ($data as $key => $value) {
        $data[$key]->id = Security::encrypt($value->id);
    }

    $res = [
        "status" => "success",
        "data" => $data


    ];

    echo json_encode($res);
}

//Bloğun kişilerini getir
if ($_POST["action"] == "get_peoples_by_block") {
    $id = Security::decrypt($_POST["block_id"]);

    $data = $Kisiler->BlokKisileri($id);

    //id'yi şifreli hale getiriyoruz
    foreach ($data as $key => $value) {
        $data[$key]->id = Security::encrypt($value->id);
    }

    $res = [
        "status" => "success",
        "data" => $data
    ];

    echo json_encode($res);
}

