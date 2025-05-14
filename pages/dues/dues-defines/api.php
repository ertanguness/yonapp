<?php 

require_once '../../../vendor/autoload.php';

use App\Helper\Security;
use Model\DueModel;

$Due = new DueModel();

if($_POST["action"] == "save_dues"){
    $id = Security::decrypt($_POST["id"]) ;

    $data = [
        "id" => $id,
        "due_name" => $_POST["due_name"],
    
    ];

    $lastInsertId = $Due->saveWithAttr($data);

    $res = [
        "status" => "success",
        "message" => "Başarılı" 
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