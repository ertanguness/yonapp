<?php 

require_once dirname(__DIR__, levels: 3) . '/configs/bootstrap.php';

use App\Helper\Security;
use App\Helper\Date;
use App\Services\Gate;
use App\Helper\Helper;
use App\Helper\Alert;
use Model\KasaModel;
use Model\KasaHareketModel;

use Database\Db;

$db = Db::getInstance();
$logger = \getlogger();


$KasaModel = new KasaModel();
$KasaHareketModel = new KasaHareketModel();

//Kasa Ekleme 
if($_POST['action'] == 'kasa_kaydet'){
    $id = Security::decrypt($_POST['id'] ?? 0);
    $site_id = $_SESSION['site_id'];
    $lastInsertId = 0;

    // echo json_encode($_POST);
    // exit;

    //Gate::can('kasa_ekle_guncelle');

    try {

        $db->beginTransaction();

        /** Eğer ilk kasa kaydı ise varsayılan kasa yap */
        $ilkKasami = $KasaModel->countWhere('site_id', $site_id);
        if($ilkKasami == 0){
            $_POST['varsayilan_mi'] = 1;
        }
        $data = [
            "id" => $id,
            "site_id" => $site_id,
            "kasa_adi" => $_POST['kasa_adi'],
            "iban" => $_POST['iban'],
            "varsayilan_mi" => $_POST['varsayilan_mi'] ?? 0,
            "aciklama" => $_POST['aciklama'],
           
        ];

        $KasaModel = new KasaModel();
        $lastInsertId = $KasaModel->saveWithAttr($data);


        $db->commit();
        $status = "success";
        $message = $id > 0 ? "Güncelleme başarılı" : "Kayıt başarılı";
    } catch (PDOException $ex) {
        $db->rollBack();
        $status = "error";
        $message = $ex->getMessage();
    }
    $res = [
        "status" => $status,
        "message" => $message,
        "data" => $data,
        "row" => $lastInsertId
    ];
    echo json_encode($res);
}

/* Kasa Silme*/
if($_POST["action"] == "kasa_sil"){

    $id = Security::decrypt($_POST['kasa_id'] ?? 0);

    //Gate::can('kasa_sil');

    try {

        $db->beginTransaction();

        $KasaModel = new KasaModel();
        $kasa = $KasaModel->find($id);

        if(!$kasa){Alert::error("Kasa bulunamadı." . $id);}

        //Kasa hareket kontrolü
        $KasaHareketModel = new KasaHareketModel();
        $kasaHareket = $KasaHareketModel->findWhere(['kasa_id' => $id]);
        if(count($kasaHareket) > 0){
          Alert::error("Bu kasa üzerinde hareket bulunduğu için silinemez.");
        }

        $KasaModel->delete($_POST["kasa_id"]);

        $db->commit();
        Alert::success("Kasa silme işlemi başarılı");
    } catch (PDOException $ex) {
        $db->rollBack();
        Alert::error($ex->getMessage());
 
    }

    exit;
}


/** VVarsayılan Kasa Yapma */

if($_POST["action"] == "varsayilan_kasa_yap"){

    $id = Security::decrypt($_POST['kasa_id'] ?? 0);

    //Gate::can('kasa_varsayilan_yap');

    try {

        $db->beginTransaction();

        $KasaModel = new KasaModel();
        $kasa = $KasaModel->find($id);

        if(!$kasa){Alert::error("Kasa bulunamadı." . $id);}

        //Varsayılan kasa ayarlama
        $KasaModel->varsayilanKasaYap($id);

        $db->commit();
        Alert::success("Kasa varsayılan olarak ayarlandı");
    } catch (PDOException $ex) {
        $db->rollBack();
        Alert::error($ex->getMessage());

    }

    exit;
}

// Kasa Transfer
if($_POST['action'] == 'kasa_transfer'){
    Gate::can('kasalar_arasi_transfer');

    $csrf = $_POST['csrf_token'] ?? '';
    if (!$csrf || $csrf !== Security::csrf()){
        echo json_encode(["status"=>"error","message"=>"Geçersiz CSRF token."], JSON_UNESCAPED_UNICODE); exit;
    }

    $sourceEnc = $_POST['source_kasa_id'] ?? '';
    $targetEnc = $_POST['target_kasa_id'] ?? '';
    $amountIn = Helper::formattedMoneyToNumber($_POST['transfer_tutar'] ?? '0');
    $dateIn   = $_POST['transfer_tarih'] ?? date('Y-m-d');
    $desc     = trim((string)($_POST['transfer_aciklama'] ?? ''));

    $sourceId = Security::decrypt($sourceEnc);
    $targetId = Security::decrypt($targetEnc);

    if (!$sourceId || !$targetId){ echo json_encode(["status"=>"error","message"=>"Kasa seçimi geçersiz."], JSON_UNESCAPED_UNICODE); exit; }
    if ($sourceId == $targetId){ echo json_encode(["status"=>"error","message"=>"Aynı kasa seçilemez."], JSON_UNESCAPED_UNICODE); exit; }
    if (!($amountIn > 0)){ echo json_encode(["status"=>"error","message"=>"Transfer tutarı sıfırdan büyük olmalı."], JSON_UNESCAPED_UNICODE); exit; }
    if (mb_strlen($desc) < 10){ echo json_encode(["status"=>"error","message"=>"Açıklama en az 10 karakter olmalı."], JSON_UNESCAPED_UNICODE); exit; }

    $sd = $KasaModel->KasaFinansalDurum($sourceId);
    $sourceBalance = (float)($sd->bakiye ?? 0);
    if ($sourceBalance < $amountIn){ echo json_encode(["status"=>"error","message"=>"Kaynak kasa bakiyesi yetersiz."], JSON_UNESCAPED_UNICODE); exit; }

    $site_id = $_SESSION['site_id'] ?? 0;
    $ref = 'TRF-' . date('YmdHis') . '-' . substr(bin2hex(random_bytes(3)),0,6);

    try{
        $db->beginTransaction();

        $dataOut = [
            "id" => 0,
            "site_id" => $site_id,
            "kasa_id" => $sourceId,
            "islem_tarihi" => $dateIn . ' 00:00:00',
            "islem_tipi" => 'Gider',
            "kategori" => 'Kasa Transferi',
            "makbuz_no" => $ref,
            "tutar" => -abs($amountIn),
            "aciklama" => $desc,
            "guncellenebilir" => 1
        ];
        $outId = $KasaHareketModel->saveWithAttr($dataOut);

        $dataInArr = [
            "id" => 0,
            "site_id" => $site_id,
            "kasa_id" => $targetId,
            "islem_tarihi" => $dateIn . ' 00:00:00',
            "islem_tipi" => 'Gelir',
            "kategori" => 'Kasa Transferi',
            "makbuz_no" => $ref,
            "tutar" => abs($amountIn),
            "aciklama" => $desc,
            "guncellenebilir" => 1
        ];
        $inId = $KasaHareketModel->saveWithAttr($dataInArr);

        $db->commit();

        $sb2 = $KasaModel->KasaFinansalDurum($sourceId);
        $tb2 = $KasaModel->KasaFinansalDurum($targetId);

        echo json_encode([
            "status"=>"success",
            "message"=>"Transfer başarılı.",
            "data"=>[
                "ref"=>$ref,
                "source_new_balance" => Helper::formattedMoney($sb2->bakiye ?? 0),
                "target_new_balance" => Helper::formattedMoney($tb2->bakiye ?? 0),
                "out_id"=>$outId,
                "in_id"=>$inId
            ]
        ], JSON_UNESCAPED_UNICODE);
    }catch(\Throwable $ex){
        $db->rollBack();
        echo json_encode(["status"=>"error","message"=>$ex->getMessage()], JSON_UNESCAPED_UNICODE);
    }
    exit;
}