<?php 
session_start();
require_once '../../../vendor/autoload.php';


use App\Helper\Security;
use App\Helper\Date;
use App\Helper\Helper;


use Model\DebitModel;
use Model\DebitDetailsModel;
use Model\DueModel;
use Model\BlockModel;
use Model\PeoplesModel;

$Debit = new DebitModel();
$DebitDetails = new DebitDetailsModel();
$Due = new DueModel();
$Block = new BlockModel();
$Peoples = new PeoplesModel();

//BORÇLANDIRMA YAP
if($_POST["action"] == "save_debit"){

    $site_id =$_SESSION['site_id'] ;
    $id = Security::decrypt($_POST["id"]) ;
    $user_id = $_SESSION["user"]->id;
    $borclandirma_turu = $_POST["target_type"];

    $data = [
        "id" => $id,
        "due_id" => Security::decrypt($_POST["due_title"]),
        "amount" => Helper::formattedMoneyToNumber($_POST["amount"]),
        "end_date" => Date::Ymd($_POST["end_date"]),
        "penalty_rate" => $_POST["penalty_rate"],
        "description" => $_POST["description"],
        "target_type" => $_POST["target_type"],
     
 
    
    ];

    $lastInsertId = $Debit->saveWithAttr($data) ;

    if($borclandirma_turu== "all"){
        //Tüm siteye borçlandırma yapılıyor
           
        $data = [];
        //sitenin tüm kişilerini alıyoruz
        $peoples = $Peoples->getPeopleBySite($site_id);
        foreach ($peoples as $person) {
            $data["kisi_id"] = $person->id;
            $DebitDetails->saveWithAttr($data);
        }


    }





    //Burada, gelen borçlandırmaya göre, tüm siteye veya kişilere borçlandırma yapılacak.


    $res = [
        "status" => "success",
        "message" => "İşlem Başarı ile tamamlandı! site_id  "    
    ];
    echo json_encode($res);
}

if($_POST["action"] == "delete_debit"){
    $Debit->delete($_POST["id"]);

    $res = [
        "status" => "success",
        "message" => "Başarılı" 
    ];
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

    $data = $Block->getBlocksBySite($site_id);
   
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
if($_POST["action"] == "get_peoples_by_block"){
    $id = Security::decrypt($_POST["block_id"]) ;

    $data = $Peoples->getPeopleByBlock($id);
   
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