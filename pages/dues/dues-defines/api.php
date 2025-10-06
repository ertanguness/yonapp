<?php
require_once dirname(__DIR__, levels: 3) . '/configs/bootstrap.php';


use App\Helper\Security;
use App\Helper\Date;
use App\Helper\Helper;
use Model\DueModel;

$Due = new DueModel();
$db = \getDbConnection();

if ($_POST["action"] == "save_dues") {
    $id = Security::decrypt($_POST["id"]);
    $hedef_tipi = $_POST["hedef_tipi"];
    //$user_id = $_SESSION["user"]->id;
    try {
        $db->beginTransaction();
        $data = [
            "id" => $id,
            'site_id' => $_SESSION['site_id'],
            "due_name" => $_POST["due_name"],
            "start_date" => Date::Ymd($_POST["start_date"]),
            "end_date" => !empty($_POST["end_date"]) ? Date::Ymd($_POST["end_date"]) : null,
            "amount" => Helper::formattedMoneyToNumber($_POST["amount"]),
            "period" => $_POST["period"],
            "auto_renew" => isset($_POST["auto_renew"]) ? 1 : 0,
            "day_of_period" => isset($_POST["day_of_period"]) ? $_POST["day_of_period"] : 0,
            "penalty_rate" => $_POST["penalty_rate"],
            "state" => $_POST["state"],
            "description" => $_POST["description"],
            "borclandirma_tipi" => $hedef_tipi,


        ];

        if ($hedef_tipi == "person") {
            $borclandirilacakKisiler = isset($_POST["hedef_kisi"]) ? $_POST["hedef_kisi"] : [];
            //Kişileri json formatında sakla
            $data["borclandirilacaklar"] = json_encode($borclandirilacakKisiler);
        } elseif ($hedef_tipi == "dairetipi") {
            $borclandirilacakDaireTipleri = isset($_POST["apartment_type"]) ? $_POST["apartment_type"] : [];
            
            $decode = [];   
            foreach ($borclandirilacakDaireTipleri as $enc_apartment_type) {
                $decode[] = Security::decrypt($enc_apartment_type);
            }
            
            //Daire tiplerini json formatında sakla
            $data["borclandirilacaklar"] = json_encode($decode);
        } elseif ($hedef_tipi == "block") {

            $borclandirilacakBloklar = isset($_POST["block_id"]) ? $_POST["block_id"] : [];
            $decode = [];
            foreach ($borclandirilacakBloklar as $enc_block_id) {
                $decode[] = Security::decrypt($enc_block_id);
            }
            $data["borclandirilacaklar"] = json_encode($decode);
        }
        $lastInsertId = $Due->saveWithAttr($data);

        $res = [
            "status" => "success",
            "message" => "<strong>" . $_POST["due_name"] . "</strong><br><br> başarıyla kaydedildi.",
        ];
        $db->commit();
    } catch (Exception $e) {
        $db->rollBack();
        $res = [
            "status" => "error",
            "message" => "Hata oluştu: " . $e->getMessage()
        ];
    }
    echo json_encode($res);
}

if ($_POST["action"] == "delete_dues") {
    $Due->delete($_POST["id"]);

    $res = [
        "status" => "success",
        "message" => "Başarılı"
    ];
    echo json_encode($res);
}
