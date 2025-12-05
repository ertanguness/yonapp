<?php
require_once dirname(__DIR__ ,levels: 2). '/configs/bootstrap.php';

use App\Services\Gate;
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
    $email = $_POST['email_adresi'];
    $roles = Security::decrypt($_POST['user_roles'] ?? '') ?? 0;
    $kisi_id = isset($_POST['kisi_id']) ? (int) (Security::decrypt($_POST['kisi_id']) ?? 0) : 0;

   // Gate::can('kullanici-kaydet');

    $lastInsertedId = 0; // Son eklenen ID başlangıç değeri
    $rowData = ''; // Satır verisi başlangıç değeri

    // Email, kisi_id ve rol üçlüsü aynı ise mükerrer kayıt engellenir
    $existingUser = $User->getUserByEmailWithID($email, $kisi_id, $roles);
    if ($existingUser && $id == 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Aynı e-posta, kişi ve rol ile kullanıcı zaten mevcut.'
        ]);
        exit;
    }

    //** Roles boş ise kayıt yapma */
    if(empty($_POST['user_roles'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Kullanıcı Rolü boş olamaz.'
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
            'roles' => $roles,
            'kisi_id' => $kisi_id,
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
