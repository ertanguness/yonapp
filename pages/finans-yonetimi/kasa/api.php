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

        $data = [
            "id" => $id,
            "site_id" => $site_id,
            "kasa_adi" => $_POST['kasa_adi'],
            "iban" => $_POST['iban'],
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