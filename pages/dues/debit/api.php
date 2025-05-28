<?php 
session_start();
require_once '../../../vendor/autoload.php';


use App\Helper\Security;
use App\Helper\Date;
use App\Helper\Helper;
use App\Helper\Error;


use Model\BorclandirmaModel ;
use Model\BorclandirmaDetayModel;
use Model\DueModel;
use Model\BloklarModel;
use Model\KisilerModel;

$Borc = new BorclandirmaModel ();
$BorcDetay = new BorclandirmaDetayModel();
$Due = new DueModel();
$Bloklar = new BloklarModel();
$Kisiler = new KisilerModel();

//BORÇLANDIRMA YAP
if($_POST["action"] == "borclandirma_kaydet"){

    $site_id =$_SESSION['site_id'] ;
    $id = Security::decrypt($_POST["id"]) ;
    $user_id = $_SESSION["user"]->id;
    $borclandirma_turu = $_POST["hedef_tipi"];

    $data = [
        "id" => $id,
        "borc_tipi_id" => Security::decrypt($_POST["borc_baslik"]),
        "tutar" => Helper::formattedMoneyToNumber($_POST["tutar"]),
        "baslangic_tarihi" => Date::Ymd($_POST["baslangic_tarihi"] ),
        "bitis_tarihi" => Date::Ymd($_POST["bitis_tarihi"]),
        "ceza_orani" => $_POST["ceza_orani"],
        "aciklama" => $_POST["aciklama"],
        "hedef_tipi" => $_POST["hedef_tipi"],
        
        // // Yeni eklenen alanlar
        // "para_birimi" => $_POST["para_birimi"] ?? 'TRY',
        // "tekrarlama_sikligi" => $_POST["tekrarlama_sikligi"] ?? 'aylik',
        // "son_odeme_tarihi" => Date::Ymd($_POST["son_odeme_tarihi"] ?? null),
        // "durum" => $_POST["durum"] ?? 'aktif',
        // "referans_kodu" => $_POST["referans_kodu"] ?? null,
        
        // // Koşullu alanlar
        // "blok_id" => ($_POST["hedef_tipi"] == 'blok') ? $_POST["blok_id"] : null,
        // "hedef_kisi_id" => ($_POST["hedef_tipi"] == 'kisi') ? $_POST["kisi_id"] : null
    ];

    $lastInsertId = $Borc->saveWithAttr($data) ?? $id; ;

    //Tüm siteye borçlandırma yapılıyor
    if($borclandirma_turu== "all"){
           
        $data = [];

        //sitenin tüm kişilerini alıyoruz
        $kisiler = $Kisiler->SiteKisileri($site_id);
        foreach ($kisiler as $kisi) {
            $data = [
                "id" => 0,
                "borc_id" => Security::decrypt($lastInsertId),
                "kisi_id" => $kisi->id,
                "tutar" => Helper::formattedMoneyToNumber($_POST["tutar"]),
                //"baslangic_tarihi" => Date::Ymd($_POST["baslangic_tarihi"]),    
                "bitis_tarihi" => Date::Ymd($_POST["bitis_tarihi"]),
                "ceza_orani" => $_POST["ceza_orani"],
                "aciklama" => $_POST["aciklama"],
                "borc_adi" => $_POST["borc_adi"],
            ];
            $BorcDetay->saveWithAttr($data);
        }


    }

    //Burada, gelen borçlandırmaya göre, tüm siteye veya kişilere borçlandırma yapılacak.


    $res = [
        "status" => "success",
        "message" => "İşlem Başarı ile tamamlandı! "
    ];
    echo json_encode($res);
}

if($_POST["action"] == "delete_debit"){
    try {
        $Borc->delete($_POST["id"]);

        $res = [
            "status" => "success",
            "message" => "Başarılı"
        ];
    } catch (Exception $e) {
        $res = Error::handlePDOException($e);
    }
    echo json_encode($res);
}


if($_POST["action"] == "get_due_info"){
    $id = Security::decrypt($_POST["id"]) ;

    $data = $Due->find($id);

    $res = [
        "status" => "success",
        "data" => $data
    ];

    echo json_encode($res);
  
}

//Sitenin bloklarını listele
if($_POST["action"] == "get_blocks"){
    //$id = Security::decrypt($_POST["id"]) ;
    $site_id = $_SESSION["site_id"]; // Kullanıcının site_id'sini alıyoruz

    $data = $Bloklar->SiteBloklari($site_id);
   
    //id'yi şifreli hale getiriyoruz
    foreach ($data as $key => $value) {
        $data[$key]->id = Security::encrypt($value->id);
    }

    $res = [
        "status" => "success" ,
        "data" => $data


    ];

    echo json_encode($res);
  
}

//Bloğun kişilerini getir
if($_POST["action"] == "get_peoples_by_block"){
    $id = Security::decrypt($_POST["block_id"]) ;

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


/**BORÇLANDIRMA YAP */
if($_POST["action"] == "borclandir"){
    $id = Security::decrypt($_POST["id"]) ;
    $user_id = $_SESSION["user"]->id;

    $data = [
        "id" => $id,
        "due_id" => Security::decrypt($_POST["due_title"]),
        "amount" => Helper::formattedMoneyToNumber($_POST["amount"]),
        "end_date" => Date::Ymd($_POST["end_date"]),
        "penalty_rate" => $_POST["penalty_rate"],
        "description" => $_POST["description"],
        "target_type" => $_POST["target_type"],
    ];

    //Borçlandırma tipi kontrol ediliyor
    if($_POST["target_type"] == "tum"){
        //Tüm siteye borçlandırma yapılıyor
        $Debit->saveWithAttr($data);
    }elseif($_POST["target_type"] == "blok"){
        //Bloklara borçlandırma yapılıyor
        $block_ids = $_POST["block_ids"];
        foreach ($block_ids as $block_id) {
            $data["block_id"] = Security::decrypt($block_id);
            $Debit->saveWithAttr($data);
        }
    }elseif($_POST["target_type"] == "kisi"){
        //Kişilere borçlandırma yapılıyor
        $person_ids = $_POST["person_ids"];
        foreach ($person_ids as $person_id) {
            $data["person_id"] = Security::decrypt($person_id);
            $Debit->saveWithAttr($data);
        }
    }

    $res = [
        "status" => "success",
        "message" => "İşlem Başarı ile tamamlandı! id:" 
    ];
    echo json_encode($res);
}