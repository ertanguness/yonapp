<?php
require_once "../../Database/require.php";
require_once "../../Model/Cases.php";
require_once "../../Model/CaseTransactions.php";
require_once "../../App/Helper/date.php";
require_once "../../App/Helper/helper.php";
require_once "../../App/Helper/financial.php";
require_once "../../Model/DefinesModel.php";
require_once "../../App/Helper/security.php";
require_once "../../Model/Auths.php";
//require_once "../../Model/ProjectIncomeExpense.php";
//require_once "../../Model/Bordro.php";
//require_once "../../Model/Persons.php";



use App\Helper\Helper;
use App\Helper\Date;
use App\Helper\Security;

$Auths = new Auths();
$cases = new Cases();
$ct = new CaseTransactions();
$financial = new Financial();
$define = new DefinesModel();
//$ProjectIncExp = new ProjectIncomeExpense();
$Bordro = new Bordro();
//$Person = new Persons();


$Auths->checkFirmReturn();

if ($_POST["action"] == "saveTransaction") {
    $id = $_POST["transaction_id"];

    //Kasa boş gelmesini engelle
    $financial::caseControl($_POST["gm_case_id"]);

    //Tutar boş gelmesini engelle
    $financial::amountControl($_POST["amount"]);

    //İşlem Türü boş gelmesini engelle
    $financial::typeControl($_POST["gm_incexp_type"]);


   //Kasa hareketi ekleme yetkisi var mı?
    $Auths->hasPermission("income_expense_add_update");

    $case_id = Security::decrypt($_POST["gm_case_id"]);
    $project_id = $_POST["gm_project_id"];
    $person_id = $_POST["gm_person_name"] != 0 ? Security::decrypt($_POST["gm_person_name"]) : 0 ;
    $company_id = $_POST["gm_company"] != 0 ? Security::decrypt($_POST["gm_company"]) : 0 ;
    
    $users_type_id = $_POST["gm_incexp_type"] ?? 0;

 
    try {
        $data = [
            "id" => 0,
            "date" => ($_POST["transaction_date"]),
            "type_id" => $_POST["transaction_type"],
            "project_id" => $project_id,
            "person_id" => $person_id ,
            "company_id" => $company_id ,
            "users_type_id" => $users_type_id ,
            "case_id" =>  $case_id,
            "amount" => Helper::formattedMoneyToNumber($_POST["amount"]),
            "amount_money" => $_POST["gm_amount_money"],
            "description" => Security::escape($_POST["description"]),
        ];



        $lastInsertId = $ct->saveWithAttr($data);
        $status = "success";
        if ($id == 0) {
            $message = "Kasa Hareketi başarıyla eklendi";
        } else {

            $message = "Kasa Hareketi başarıyla güncellendi";
        }

        //     //Eklenen kaydın bilgilerini getir
        //      $transaction = $ct->find(Security::decrypt($lastInsertId));

        //    // Kayıt alanlarında düzenleme
        //     foreach ($transaction as $key => $value) {
        //         if ($key == "id") {
        //             $transaction->id = Security::encrypt($value);
        //         } else if ($key == "date") {
        //             $transaction->$key = Date::dmY($value);
        //         } elseif ($key == "type_id") {
        //             $transaction->$key = $value == 1 ? "Gelir" : "Gider";
        //         } elseif ($key == "case_id") {
        //             $transaction->$key = $cases->find($value)->case_name;
        //         } elseif ($key == "amount") {
        //             $transaction->$key = Helper::formattedMoney($value, $transaction->amount_money ?? 1);
        //         }
        //     }
    } catch (PDOException $ex) {
        $status = "error";
        $message = $ex->getMessage();
    }

    $res = [
        "status" => $status,
        "message" => $message
    ];
    echo json_encode($res);
}

if ($_POST["action"] == "deleteTransaction") {

    //Kasa hareketi silme yetkisi var mı?
    $Auths->hasPermissionReturn("delete_income_expense");


    $id = $_POST["id"];
    $type = $_GET["type"];
    try {

        if (in_array($type, [5, 6, 10, 11, 12])) {
            $ProjectIncExp->delete($id);
        } else {
            $ct->delete($id);
        }
        $status = "success";
        $message = "Kasa hareketi başarıyla silindi.";
    } catch (PDOException $ex) {
        $status = "error";
        $message = "Kasa hareketi bir hata oluştu.";
    }
    $res = [
        "status" => $status,
        "message" => $message,
    ];
    echo json_encode($res);
}

if ($_POST["action"] == "getSubTypes") {
    $type = $_POST["type"];
    $subTypes = $define->getIncExpTypesByFirmandType($type);
    $res = [

        "subTypes" => $subTypes
    ];
    echo json_encode($res);
}


//Projeden Ödeme Al
if ($_POST["action"] == "getPaymentFromProject") {
    $id = $_POST["id"] != 0 ? Security::decrypt($_POST["id"]) : 0;
    $project_id = Security::decrypt($_POST["fp_project_name"]);

    $amount = Helper::formattedMoneyToNumber($_POST["fp_amount"]);
    $date = Date::ymd($_POST["fp_action_date"]);
    $description = Security::escape($_POST["fp_description"]);

    //Kasa hareketi ekleme yetkisi var mı?
    //$Auths->hasPermission("income_expense_add_update");

    $data = [
        "id" => $id,
        "date" => $date,
        "type_id" => 1,
        "sub_type" => 5,
        "project_id" => $project_id,
        "case_id" => Security::decrypt($_POST["fp_cases"]),
        "amount" => $amount,
        "amount_money" => 1,
        "description" => $description,
    ];

    try {
        $lastInsertId = $ct->saveWithAttr($data);
        $status = "success";
        $message = $id == 0 ? "Ödeme başarıyla yapıldı" : "Ödeme başarıyla güncellendi";

    } catch (PDOException $ex) {
        $status = "error";
        $message = $ex->getMessage();
    }

    $res = [
        "status" => $status,
        "message" => $message,

    ];
    echo json_encode($res);
}

//Personel Ödemesi Yap
if ($_POST["action"] == "payToPerson") {
    $id = $_POST["tp_id"] != 0 ? Security::decrypt($_POST["id"]) : 0;
    $person_id = $_POST["tp_person_name"];
    $amount = Helper::formattedMoneyToNumber($_POST["tp_amount"]);
    $date = Date::ymd($_POST["tp_action_date"]);
    $description = Security::escape($_POST["tp_description"]);

    //Kasa hareketi ekleme yetkisi var mı?
    //$Auths->hasPermission("income_expense_add_update");

    $data = [
        "id" => $id,
        "date" => $date,
        "type_id" => 2, //Gider
        "sub_type" => 7, //Personel Ödemesi
        "person_id" => Security::decrypt($_POST["tp_person_name"]),
        "case_id" => Security::decrypt($_POST["tp_cases"]),
        "amount" => $amount,
        "amount_money" => 1,
        "description" => $description,
    ];

    try {
        $lastInsertId = $ct->saveWithAttr($data);
        $status = "success";
        $message = $id == 0 ? "Ödeme başarıyla yapıldı" : "Ödeme başarıyla güncellendi";



    } catch (PDOException $ex) {
        $status = "error";
        $message = $ex->getMessage();
    }

    $res = [
        "status" => $status,
        "message" => $message,

    ];
    echo json_encode($res);
    exit();
}

//Personellere Ödeme Yap
if ($_POST["action"] == "payToPersons") {

    //gelen değeri virgülden ayırarak diziye çevir
    $person_ids = explode(",", $_POST["person_ids"]);
    $case = Security::decrypt($_POST["tps_cases"]);
    $date = Date::ymd($_POST["tps_action_date"]);
    $description = Security::escape($_POST["tps_amount_description"]);
    $amounts = explode(",", $_POST["amounts"]);

    //Kasa hareketi ekleme yetkisi var mı?
    //$Auths->hasPermission("income_expense_add_update");

    try {
        $i = 0;
        foreach ($person_ids as $person) {
            $full_name = $Person->getPersonName($person)->full_name;
            $data = [
                "id" => 0,
                "date" => $date,
                "type_id" => 2, //Gider
                "sub_type" => 7, //Personel Ödemesi
                "person_id" => $person,
                "case_id" => $case,
                "amount" => Helper::formattedMoneyToNumber($amounts[$i]),
                "amount_money" => 1,
                "description" => $full_name . " " . $description,
            ];
            $i++;
            $ct->saveWithAttr($data);
        }

        $status = "success";
        $message = "Ödeme başarıyla yapıldı";

    } catch (PDOException $ex) {
        $status = "error";
        $message = $ex->getMessage();
    }



    $res = [
        "status" => $status,
        "message" => $message,

    ];
    echo json_encode($res);
}


//Firma Ödemesi Yap
if ($_POST["action"] == "payToCompany") {
    $id = $_POST["id"] != 0 ? Security::decrypt($_POST["id"]) : 0;
    $company_id = $_POST["tc_company_name"];
    $amount = Helper::formattedMoneyToNumber($_POST["tc_amount"]);
    $date = Date::ymd($_POST["tc_action_date"]);
    $description = Security::escape($_POST["tc_description"]);

    //Kasa hareketi ekleme yetkisi var mı?
    //$Auths->hasPermission("income_expense_add_update");

    $data = [
        "id" => $id,
        "date" => $date,
        "type_id" => 2, //Gider
        "sub_type" => 8, //Firma Ödemesi
        "case_id" => Security::decrypt($_POST["tc_cases"]),
        "company_id" => Security::decrypt($_POST["tc_company_name"]),
        "amount" => $amount,
        "amount_money" => 1,
        "description" => $description,
    ];

    try {
        $lastInsertId = $ct->saveWithAttr($data);
        $status = "success";
        $message = $id == 0 ? "Ödeme başarıyla yapıldı" : "Ödeme başarıyla güncellendi";


    } catch (PDOException $ex) {
        $status = "error";
        $message = $ex->getMessage();
    }

    $res = [
        "status" => $status,
        "message" => $message,

    ];
    echo json_encode($res);
}

//Alınan Proje Masrafı Ekle
if ($_POST["action"] == "addExpenseReceivedProject") {
    $id = $_POST["id"] != 0 ? Security::decrypt($_POST["id"]) : 0;
    $project_id = $_POST["rp_project_name"];
    $amount = Helper::formattedMoneyToNumber($_POST["rp_amount"]);
    $date = Date::ymd($_POST["rp_action_date"]);
    $description = Security::escape($_POST["rp_description"]);

    //Kasa hareketi ekleme yetkisi var mı?
    //$Auths->hasPermission("income_expense_add_update");

    $data = [
        "id" => $id,
        "date" => $date,
        "type_id" => 2, //Gider
        "sub_type" => 9, //Alınan Proje Masrafı
        "case_id" => Security::decrypt($_POST["rp_cases"]),
        "project_id" => Security::decrypt($_POST["rp_project_name"]),
        "amount" => $amount,
        "amount_money" => 1,
        "description" => $description,
    ];

    try {
        $lastInsertId = $ct->saveWithAttr($data);
        $status = "success";
        $message = $id == 0 ? "Masraf başarıyla eklendi" : "Masraf başarıyla güncellendi";


    } catch (PDOException $ex) {
        $status = "error";
        $message = $ex->getMessage();
    }

    $res = [
        "status" => $status,
        "message" => $message,

    ];
    echo json_encode($res);
}


//Güncelleme için verileri getir
if ($_POST["action"] == "getTransaction") {
    $id = $_POST["id"];
    $projects = $_POST["projects"];
    $persons = $_POST["persons"];
    $companies = $_POST["companies"];
    $cases = $_POST["cases"];


    $transaction = $ct->find(Security::decrypt($id));
    //tarihi formatla
    $transaction->date = Date::dmY($transaction->date);

    //kasa id geriye normal döner ancak select2'deki değerler şifreli olduğu için 
    //select2 elemanın tüm değerleri karşılaştırarak eşleşen değer atanır
    $cases = explode(",", ($cases));
    //sondaki virgülü sil
    array_pop($cases);
    foreach ($cases as $case) {
        //şifreyi çözerek bir değişkene ata
        if ($transaction->case_id == Security::decrypt($case)) {
            $transaction->case_id = $case;
        }
    }

    //proje id geriye normal döner ancak select2'deki değerler şifreli olduğu için
    //select2 elemanın tüm değerleri karşılaştırarak eşleşen değer atanır
    $projects = explode(",", ($projects));
    //sondaki virgülü sil
    array_pop($projects);
    foreach ($projects as $project) {
        //şifreyi çözerek bir değişkene ata
        if ($transaction->project_id == Security::decrypt($project)) {
            $transaction->project_id = $project;
        }
    }

    //personel id geriye normal döner ancak select2'deki değerler şifreli olduğu için
    //select2 elemanın tüm değerleri karşılaştırarak eşleşen değer atanır
    $persons = explode(",", ($persons));
    //sondaki virgülü sil
    array_pop($persons);
    foreach ($persons as $person) {
        //şifreyi çözerek bir değişkene ata
        if ($transaction->person_id == Security::decrypt($person)) {
            $transaction->person_id = $person;
        }
    }

    //firma id geriye normal döner ancak select2'deki değerler şifreli olduğu için
    //select2 elemanın tüm değerleri karşılaştırarak eşleşen değer atanır
    $companies = explode(",", ($companies));
    //sondaki virgülü sil
    array_pop($companies);
    foreach ($companies as $company) {
        //şifreyi çözerek bir değişkene ata
        if ($transaction->company_id == Security::decrypt($company)) {
            $transaction->company_id = $company;
        }
    }

    $res = [
        "status" => "success",
        "transaction" => $transaction,

    ];
    echo json_encode($res);
}

//Kasalar arası virman yapmak için transfer yapılacak kasaları getir
if ($_POST["action"] == "getCaseTransfer") {
    //Kasalararası virman yetkisi var mı kontrol et
    //$Auths->hasPermissionReturn("intercash_transfer");
    try {
        $id = Security::decrypt($_POST["from_case_id"]);
        $cases = $cases->getCasesExceptId($id);
        $status = "success";
    } catch (PDOException $ex) {
        $status = "error";
        $message = $ex->getMessage();
    }

    $res = [
        "status" => $status,
        "message" => $message ?? "",
        "cases" => $cases
    ];

    echo json_encode($res);

}