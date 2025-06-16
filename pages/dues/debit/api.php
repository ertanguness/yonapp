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
use Model\DairelerModel;
use Model\KisilerModel;
use Model\DefinesModel;

$Borc = new BorclandirmaModel();
$BorcDetay = new BorclandirmaDetayModel();
$Due = new DueModel();
$Bloklar = new BloklarModel();
$Daire = new DairelerModel();
$Kisiler = new KisilerModel();
$Defines = new DefinesModel();



/**BORÇLANDIRMA YAP */
if ($_POST["action"] == "borclandir") {
    $site_id = $_SESSION['site_id'];
    $id = Security::decrypt($_POST["id"]);
    $user_id = $_SESSION["user"]->id;
    $borclandirma_turu = $_POST["hedef_tipi"];
    $gun_bazli = isset($_POST["day_based"]) ? true : false; // Gün bazlı mı kontrolü

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
        $kisiler = $Kisiler->SiteAktifKisileri($site_id);
        foreach ($kisiler as $kisi) {
            $data["aciklama"] = $kisi->giris_tarihi;
            $data["kisi_id"] = $kisi->kisi_id;
            $data["blok_id"] = $kisi->blok_id; // Blok ID'sini de ekliyoruz

            // Eğer gün bazlı borçlandırma ise, başlangıç ve bitiş tarihlerini gün bazlı olarak ayarlıyoruz
            if ($gun_bazli) {
                $data['tutar'] = Helper::calculateDayBased(
                    Date::Ymd($_POST["baslangic_tarihi"]), 
                    Date::Ymd($_POST["bitis_tarihi"]),
                    $kisi->giris_tarihi,
                    Helper::formattedMoneyToNumber($_POST["tutar"])
                ); // Günlük tutar hesaplanıyor
            }
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
    } elseif ($borclandirma_turu == "person") {
        //Kişilere borçlandırma yapılıyor
        $person_ids = $_POST["hedef_kisi"];
        foreach ($person_ids as $person_id) {
            $data["kisi_id"] = Security::decrypt($person_id);
            $BorcDetay->saveWithAttr($data);
        }
    }else if($borclandirma_turu == 'dairetipi'){
        //Daire tipine göre borçlandırma yapılıyor
        $daire_tipleri = $_POST["apartment_type"];
       
        //Daire Tipi id'lerinde döngü yap
        foreach($daire_tipleri as $daire_tipi_id){
            $daire_tipi_id = Security::decrypt($daire_tipi_id);
           
            //Daireler tablosundan bu daire tipine sahip daireleri getir
            $daireler = $Daire->DaireTipineGoreDaireler($daire_tipi_id);

            foreach ($daireler as $daire) {
                $data["kisi_id"] = $Kisiler->AktifKisiByDaire($daire->id)->id; // Daireye ait aktif kişinin ID'sini alıyoruz
                $data["blok_id"] = $daire->blok_id; // Daireye ait blok ID'sini alıyoruz
                $data["daire_id"] = $daire->id; // Daire ID'sini ekliyoruz
                $BorcDetay->saveWithAttr($data);
            }
        }

        echo json_encode([
            "status" => "success",
            "message" => "Daire Tipine göre borçlandırma tamamlandı!",
            "data" => $data
        ]);
        exit;
    }

    $res = [
        "status" => "success",
        "message" => "İşlem Başarı ile tamamlandı! " . $gun_bazli, 
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

//Sitenin Aktif kişilerini getir
if ($_POST["action"] == "get_people_by_site") {
    $site_id = $_SESSION["site_id"]; // Kullanıcının site_id'sini alıyoruz

    $data = $Kisiler->SiteAktifKisileri($site_id);

    //id'yi şifreli hale getiriyoruz
    foreach ($data as $key => $value) {
        $data[$key]->id = Security::encrypt($value->kisi_id);
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

//Daire Tiplerini getir
if ($_POST["action"] == "get_apartment_types") {
    $data = $Defines->getAllByApartmentType(3);

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

