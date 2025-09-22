<?php


require_once dirname(__DIR__ ,levels: 2). '/configs/bootstrap.php';



use App\Helper\Helper;
use Model\MenuModel;
use Model\UserModel;
use Model\UserRolesModel;
use Model\PermissionsModel;
use Model\UserRolePermissionsModel;

use App\Helper\Security;

$Menus = new MenuModel();
$User = new UserModel();
$UserRoles = new UserRolesModel();
$Permissions = new PermissionsModel();
$UserPermissions = new UserRolePermissionsModel();


/**
 * Yetkileri ve Yetki Gruplarını döndürür.
 * * @return json
 * 
 */
if ($_POST['action'] == 'getPermissions') {

    $id = Security::decrypt($_POST['id'] ?? 0 );

    // Tüm izinleri gruplanmış olarak al
    $permissionGroups = $Permissions->getGroupedPermissions();
    
    //Kullanıcı izinlerini al
    $userPermissions = $UserPermissions->getUserPermissions($id);
    // Sonucu bir API olarak döndürmek için:
    header('Content-Type: application/json; charset=utf-8');
    $res = [
        'status' => 'success',
        'id' => $id,
        'data' => [
            'permissions' => $permissionGroups,
            'user_permissions' => $userPermissions
        ]
    ];
    echo json_encode(
        $res,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    );
}


// Yetkileri Kaydet
if ($_POST['action'] == 'savePermissions') {
    
    // Gelen verileri al
    $roleID = Security::decrypt($_POST['id'] );
    $submittedPermissions = json_decode($_POST['permissions']) ?? [];

    // Gelen izinlerin bir dizi olduğundan emin ol
    if (!is_array($submittedPermissions)) {
        $submittedPermissions = [];
    }
    // Gelen değerlerin integer olduğundan emin ol
    $submittedPermissions = array_map('intval', $submittedPermissions);

    try {
        if ($roleID === 0) {
            throw new Exception("Geçersiz veya eksik Yetki grubu ID'si.");
        }
        
      
        // 1. Adım: Düzenlenen kullanıcının bilgilerini ve rolünü al
        $role =  $UserRoles->find($roleID);
        if (!$role) {
            throw new Exception("Yetki grubu bulunamadı (ID: {$roleID}).");
        }

        // 2. Adım (Güvenlik): Kullanıcının grubunun/rolünün izin verdiği yetkileri al
        $allowedGroupPermissions = $UserPermissions->getPermissionsForRole($roleID);
        
        // 3. Adım (Filtreleme): Gelen yetkileri, sadece kullanıcının grubunun izin verdikleriyle sınırla.
        // Bu, birinin formdan fazladan yetki göndermesini engeller.
        $validPermissionsToSync = array_intersect($submittedPermissions, $allowedGroupPermissions);

        // 4. Adım: Modeli çağırarak veritabanını senkronize et
        $UserPermissions->syncUserPermissions($roleID, $validPermissionsToSync);

        //Menu cache'yi temizle
       $Menus->clearMenuCacheForRole($roleID);
        
        $status = 'success';
        $message = 'Yetki Grubu izinleri başarıyla güncellendi.';

    } catch (Exception $e) {
        $status = "error";
        $message = 'Bir hata oluştu: ' . $e->getMessage();
    }

    $res = [
        'status' => $status,
        'message' => $message,
        'data' => [
            'role_id' => $roleID,
            'permissions' => $_POST['permissions']
        ]
    ];
    
    header('Content-Type: application/json');
    echo json_encode($res);
}


/**
 * Kullanıcı Grubu Ekleme ve Güncelleme
 * 
 */
if ($_POST['action'] == 'saveRole') {

    $roleID = Security::decrypt($_POST['id'] ?? 0);
    $roleName = $_POST['role_name'] ?? '';
    $description = $_POST['description'] ?? '';

    try {
      
            // Yeni rol ekleniyor
            $data = [
                "id" => $roleID,
                "owner_id" => $_SESSION['user']->id,
                'role_name' => $roleName,
                'description' => $description,
                'main_role' => 0
            ];

            $lastInsertId = $UserRoles->saveWithAttr($data);

            $status = 'success';
            $message = 'Yeni kullanıcı grubu başarıyla eklendi.';
       
    } catch (Exception $e) {
        $status = 'error';
        $message = 'Bir hata oluştu: ' . $e->getMessage();
    }

    $res = [
        'status' => $status,
        'message' => $message
    ];

    echo json_encode($res);
}


/**
 * Kullanıcı Grubu Silme
 */
if ($_POST['action'] == 'deleteRole') {

    $roleID = $_POST['id'] ?? 0;

    try {
        if ($roleID === 0) {
            throw new Exception("Geçersiz veya eksik Yetki grubu ID'si.");
        }

        // Silme işlemi
        $deleted = $UserRoles->delete($roleID);
        if (!$deleted) {
            throw new Exception("Yetki grubu silinemedi veya bulunamadı (ID: {$roleID}).");
        }

        // İlgili kullanıcı izinlerini de sil
        //$UserPermissions->deleteByRoleId($roleID);

        //Menu cache'yi temizle
        //$Menus->clearMenuCacheForRole($roleID);

        $status = 'success';
        $message = 'Yetki grubu başarıyla silindi.';
    } catch (Exception $e) {
        $status = 'error';
        $message = 'Bir hata oluştu: ' . $e->getMessage();
    }

    $res = [
        'status' => $status,
        'message' => $message
    ];

    echo json_encode($res);
}
