<?php
require_once ROOT . "/Database/db.php";
require_once ROOT . "/Model/Cases.php";
require_once ROOT . "/App/Helper/security.php";

use Database\Db;
use App\Helper\Security;

class Financial extends Db
{


    const TYPE = [
        1 => "Gelir",
        2 => "Gider",
        3 => "Virman",
        4 => "Diğer",
        5 => "Proje(Alınan Ödeme)",
        6 => "Proje(Yapılan Ödeme)",
        7 => "Personel Ödemesi",
        8 => "Firma Ödemesi",
        9 => "Alınan Proje Masrafı",
        10 => "Hakediş",
        11 => "Proje Masrafı",
        12 => "Proje Kesinti",
        13 => "Masraf",
        14 => "Puantaj Çalışma",
        15 => "Kesinti",
        16 => "Maaş",
    ];
    protected $caseObj;


    public function __construct()
    {
        parent::__construct();
        $this->caseObj = new Cases();
    }


    //firma id'ye göre gelir veya gider türlerini getirir
    public function getIncExpTypeSelect($name = "inc_exp_type", $type_id = 1)
    {
        $firm_id = $_SESSION['firm_id'];
        $query = $this->db->prepare("SELECT * FROM defines WHERE firm_id = ? AND type_id = ?");
        $query->execute([$firm_id, $type_id]);
        $results = $query->fetchAll(PDO::FETCH_OBJ);

        $select = "<select name=\"{$name}\" class=\"form-select modal-select select2\" id=\"{$name}\" style=\"width:100%\">";
        $select .= '<option value="">Tür Seçiniz</option>';
        foreach ($results as $row) {
            $select .= "<option value=\"{$row->id}\">{$row->name}</option>";
        }
        $select .= '</select>';
        return $select;
    }

    //CaseSelect
    public function getCasesSelectByFirm($name = "case_id", $case_id = "")
    {

        //case_id boş ise firmanın varsayılan kasa id'sini al
        if (empty($case_id) && $case_id != 0) {
            $case_id = $this->caseObj->getDefaultCaseIdByFirm();
        }

        $cases = $this->caseObj->allCaseWithFirmId();
        $select = "<select name='" . $name . "' class=\"form-control select2\" id='" . $name . "' style='width:100%'>";
        $select .= "<option value='0'>Kasa Seçiniz</option>";

        foreach ($cases as $case) {
            $selectedAttr = $case_id == $case->id ? 'selected' : '';
            $select .= "<option value=\"" . Security::encrypt($case->id) . "\" {$selectedAttr}>{$case->case_name}-{$case->bank_name}/{$case->branch_name}</option>";
        }
        $select .= '</select>';
        return $select;
    }

    //Kullanıcıya göre kasaları getir
    public function getCasesSelectByUser($name = "case_id", $case_id = "")
    {
        $is_main_user = $_SESSION['user']->parent_id;
        if ($is_main_user == 0) {
            $cases = $this->caseObj->allCaseWithFirmId();

        } else {
            $cases = $this->caseObj->getCasesByUserIds();
        }

        $select = "<select name='" . $name . "' class=\"form-control select2\" id='" . $name . "' style='width:100%'>";
        $select .= "<option value='0'>Kasa Seçiniz</option>";
        foreach ($cases as $case) {
            $selectedAttr = $case_id == $case->id ? 'selected' : '';
            $select .= "<option value=\"" . Security::encrypt($case->id) . "\" {$selectedAttr}>{$case->case_name}-{$case->bank_name}/{$case->branch_name}</option>";
        }
        $select .= '</select>';
        return $select;
    }

    //Hareketin type bilgisini döndürür
    public static function getTransactionType($type_id)
    {
        if (!isset(self::TYPE[$type_id])) {
            return "";
        }
        return self::TYPE[$type_id];
    }

    //defines tablosundan id'ye gore sorgula
    public function getUsersTransactionType($id)
    {
        $query = $this->db->prepare("SELECT * FROM defines WHERE id = ?");
        $query->execute([$id]);
        return $query->fetch(PDO::FETCH_OBJ)->name;
    }

    //gelen case_id değerini kontrol etmek için
    public static function caseControl($case_id)
    {
       if (!isset($case_id) || $case_id == 0) {
            $res = [
                "status" => "error",
                "message" => "Kasa seçimi yapınız"
            ];
            echo json_encode($res);
            exit();

        }
    }

    //Gelen tutarın kontrolü
    public static function amountControl($amount)
    {
       if (!isset($amount) || $amount == 0 || $amount == '') {
            $res = [
                "status" => "error",
                "message" => "Geçerli bir tutar giriniz!"
            ];
            echo json_encode($res);
            exit();

        }
    }
    //Gelen işlem türünün kontrolü
    public static function typeControl($type)
    {
       if (!isset($type) || $type == 0 || $type == '') {
            $res = [
                "status" => "error",
                "message" => "İşlem Türünü seçiniz!"
            ];
            echo json_encode($res);
            exit();

        }
    }



}
