<?php 
session_start();

require_once '../../../vendor/autoload.php';

use App\Helper\Security;
use App\Helper\Date;
use App\Helper\Helper;
use Model\DueModel;

$Due = new DueModel();

if($_POST["action"] == "save_dues"){
    $id = Security::decrypt($_POST["id"]) ;
    $user_id = $_SESSION["user"]->id;

    $data = [
        "id" => $id,
        "user_id" => $user_id,
       // "block_id" => $_POST["block_id"],
       'site_id' => $_SESSION['site_id'],
        "due_name" => $_POST["due_name"],
        "start_date" => Date::Ymd($_POST["start_date"]),
        "amount" => Helper::formattedMoneyToNumber($_POST["amount"]),
        "period" => $_POST["period"],
        "auto_renew" => isset($_POST["auto_renew"]) ? 1 : 0,
        "day_based" => isset($_POST["day_based"]) ? 1 : 0,
        "penalty_rate" => $_POST["penalty_rate"],
        "state" => $_POST["state"],
        "description" => $_POST["description"],
    
    ];

    $lastInsertId = $Due->saveWithAttr($data);

    $res = [
        "status" => "success",
        "message" => "<strong>".$_POST["due_name"] . "</strong><br><br> başarıyla kaydedildi.", 
    ];
    echo json_encode($res);
}

if($_POST["action"] == "delete_dues"){
    $Due->delete($_POST["id"]);

    $res = [
        "status" => "success",
        "message" => "Başarılı" 
    ];
    echo json_encode($res);
}