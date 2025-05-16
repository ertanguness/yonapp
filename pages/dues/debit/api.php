<?php 
session_start();
require_once '../../../vendor/autoload.php';


use App\Helper\Security;
use App\Helper\Date;
use App\Helper\Helper;


use Model\DebitModel;
use Model\DueModel;
use Model\BlockModel;
use Model\PeoplesModel;

$Debit = new DebitModel();
$Due = new DueModel();
$Block = new BlockModel();
$Peoples = new PeoplesModel();


if($_POST["action"] == "save_debit"){
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

    $lastInsertId = $Debit->saveWithAttr($data) ;

    //Burada, gelen borçlandırmaya göre, tüm siteye veya kişilere borçlandırma yapılacak.

    $res = [
        "status" => "success",
        "message" => "İşlem Başarı ile tamamlandı! id:" 
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

    $data = $Block->getBlocksBySite();
   
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