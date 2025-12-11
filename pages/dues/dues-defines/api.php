<?php
require_once dirname(__DIR__, levels: 3) . '/configs/bootstrap.php';


use App\Helper\Security;
use App\Helper\Date;
use App\Helper\Helper;
use Model\DueModel;
use Model\DueDetailModel;
use App\Modules\Onboarding\Events\OnboardingEvents;

$Due = new DueModel();
$DueDetail = new DueDetailModel();


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

       
        $lastInsertId = $Due->saveWithAttr($data) ?? $_POST["id"];


        //Borçlandırılacak Kişileri json decode yap
        $duePendingList = json_decode($_POST["due_pending_list"], true);
        if (!empty($duePendingList)) {

            //Önce eskilerini sil
            /**Eğer kayıt varsa */
            $existingRecords = $DueDetail->findWhere(["due_id" => Security::decrypt($lastInsertId)]);
            if (!empty($existingRecords)) {
                $DueDetail->softDeleteByColumn("due_id",Security::decrypt($lastInsertId));
            }

            //Sonra yenilerini ekle
            foreach ($duePendingList as $pending) {


                $hedef_tipi = $pending["borclandirma_tipi"];

              //  $logger->info('Due pending kisi_ids: ' . $pending["kisi_ids"]);

                if ($hedef_tipi == "person") {
                    $kisiIdsRaw = $pending["kisi_ids"];
                    $kisiIds = [];
                    // Eğer dizi olarak geldiyse
                    if (is_array($kisiIdsRaw)) {
                        foreach ($kisiIdsRaw as $encId) {
                            $kisiIds[] = Security::decrypt($encId);
                        }
                    } else {
                        // String olarak geldiyse (virgülle ayrılmış veya tek id)
                        $ids = explode(",", $kisiIdsRaw);
                        foreach ($ids as $encId) {
                            $encId = trim($encId);
                            if ($encId !== '') {
                                $kisiIds[] = Security::decrypt($encId);
                            }
                        }
                    }
                    // Kaydederken json_encode ile dizi olarak kaydet
                    $pending["kisi_ids"] = json_encode($kisiIds);
                }elseif ($hedef_tipi == "dairetipi") {
                    $apartmentTypeIdsRaw = $pending["daire_tipi_ids"];
                    $apartmentTypeIds = [];
                    // Eğer dizi olarak geldiyse
                    if (is_array($apartmentTypeIdsRaw)) {
                        foreach ($apartmentTypeIdsRaw as $encId) {
                            $apartmentTypeIds[] = Security::decrypt($encId);
                        }
                    } else {
                        // String olarak geldiyse (virgülle ayrılmış veya tek id)
                        $ids = explode(",", $apartmentTypeIdsRaw);
                        foreach ($ids as $encId) {
                            $encId = trim($encId);
                            if ($encId !== '') {
                                $apartmentTypeIds[] = Security::decrypt($encId);
                            }
                        }
                    }
                    // Kaydederken json_encode ile dizi olarak kaydet
                    $pending["daire_tipi_ids"] = json_encode($apartmentTypeIds);
                }elseif ($hedef_tipi == "block") {
                    $blockIdsRaw = $pending["blok_ids"];
                    $blockIds = [];
                    // Eğer dizi olarak geldiyse
                    if (is_array($blockIdsRaw)) {
                        foreach ($blockIdsRaw as $encId) {
                            $blockIds[] = Security::decrypt($encId);
                        }
                    } else {
                        // String olarak geldiyse (virgülle ayrılmış veya tek id)
                        $ids = explode(",", $blockIdsRaw);
                        foreach ($ids as $encId) {
                            $encId = trim($encId);
                            if ($encId !== '') {
                                $blockIds[] = Security::decrypt($encId);
                            }
                        }
                    }
                    // Kaydederken json_encode ile dizi olarak kaydet
                    $pending["blok_ids"] = json_encode($blockIds);
                }



                $duePendingData = [
                    "borclandirma_tipi" => $hedef_tipi,
                    "due_id" => Security::decrypt($lastInsertId),
                    "kisi_ids" => $pending["kisi_ids"],
                    "daire_ids" => $pending["daire_id"] ?? 0,
                    "blok_ids" => $pending["blok_ids"] ?? '',
                    "daire_tipi_ids" => $pending["daire_tipi_ids"] ?? '',
                    "tutar" => Helper::formattedMoneyToNumber($pending["tutar"]),
                ];
                $DueDetail->saveWithAttr($duePendingData);
            }
        }



        $res = [
            "status" => "success",
            "message" => "<strong>" . $_POST["due_name"] . "</strong><br><br> başarıyla kaydedildi.",
        ];
        $db->commit();
        try { OnboardingEvents::complete('add_dues_types', $_SESSION['site_id'] ?? null); } catch (\Throwable $e) {}
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
