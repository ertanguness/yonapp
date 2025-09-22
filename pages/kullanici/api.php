<?php
require_once dirname(__DIR__ ,levels: 2). '/configs/bootstrap.php';


use App\Helper\Helper;
use App\Helper\Security;
use Model\UserModel;

$User = new UserModel();

// if(!Helper::isAdmin()) {
//     echo json_encode([
//         'status' => 'error',
//         'message' => 'Unauthorized access.'
//     ]);
//     exit;
// };

if ($_POST["action"] == "kullanici-kaydet") {
    $id = Security::decrypt($_POST['user_id']) ?? 0;

    $lastInsertedId = 0; // Son eklenen ID başlangıç değeri
    $rowData = ''; // Satır verisi başlangıç değeri

    //Email adresi ile kayıt var mı kontrol et
    $existingUser = $User->getUserByEmail($_POST['email_adresi'], $id);
    if ($existingUser && $id == 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Bu e-posta adresi zaten kayıtlı.'
        ]);
        exit;
    }

      
    
    try {
        $data = [
            'id' => $id , // Eğer id varsa deşifre et
            'full_name' => $_POST['adi_soyadi'],
            'email' => $_POST['email_adresi'],
            'phone' => $_POST['phone'],
            'owner_id' => $_SESSION["owner_id"],
            "status" => 1,
            "is_main_user" => 0,
            'roles' => Security::decrypt($_POST['user_roles']),
        ];
        if (!empty($_POST['password'])) {
            $data['password'] = password_hash($_POST['password'], PASSWORD_BCRYPT);
        }

        $lastInsertedId = $User->saveWithAttr($data) ?? $_POST['user_id'];


        $status = "success";
        $message = "Kullanıcı başarıyla kaydedildi.";
    } catch (PDOException $ex) {

        if( $ex->getCode() == 23000) { // 23000 hata kodu, genellikle benzersiz kısıtlama ihlali anlamına gelir
            $message = "Bu kullanıcı adı veya e-posta zaten kayıtlı.";
        } else {
            $message = $ex->getMessage();
        }
        $status = "error";
    }
    $res = [
        'status' => $status,
        'message' => $message,
        'lastInsertedId' => $lastInsertedId,
        'rowData' => $rowData
    ];
    echo json_encode($res);
}


//Kullanıcı silme işlemi
if ($_POST["action"] == "kullanici-sil") {
    $id = $_POST['id'] ;

    try {
        $User->delete($id);
        $status = "success";
        $message = "Kullanıcı başarıyla silindi.";
    } catch (PDOException $ex) {
        $status = "error";
        $message = $ex->getMessage();
    }
    echo json_encode([
        'status' => $status,
        'message' => $message
    ]);
}
