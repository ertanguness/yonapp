<?php

use App\Helper\Security;

require_once dirname(__DIR__, 4) . '/configs/bootstrap.php';

use Model\UserModel;
use Database\Db;


$UserModel = new UserModel();

$action = $_POST['action'] ?? null;
$post = $_POST;
$id = Security::decrypt($post['id'] ?? 0);
$db = Db::getInstance();


switch ($action) {
    case 'save_user':
        try {

            $db->beginTransaction();

            /** Kullanıcı var mı kontrol et */
            $user = $UserModel->find($id);

            /** Yoksa hata döndür */
            if (!$user && $id != 0) {
                response("error", "Kullanıcı bulunamadı");
                exit;
            }

            /** Aynı email adresi ve aynı rol tipi ile kayda izin verme */
            $isUserExists = $UserModel->isUserExists($post['email_adresi'], Security::decrypt($post['user_roles']));

            if ($isUserExists) {
                response("error", "Bu email adresi ve rol tipi ile kayıtlı kullanıcı bulunduğundan kayıt yapılamaz");
                exit;
            }

            $data = [
                "id" => $id,
                "user_type" => 2,
                "full_name" => $post['adi_soyadi'] ?? '',
                "email" => $post['email_adresi'] ?? '',
                "password" => password_hash($post['password'] ?? '', PASSWORD_DEFAULT),
                "roles" => Security::decrypt($post['user_roles'] ?? ''),
                "status" => 1,
            ];
            $lastId = $UserModel->saveWithAttr($data);

            $status = "success";
            $message = "Kullanıcı başarıyla kaydedildi";
            $data = $lastId;
            $db->commit();

        } catch (Exception $ex) {
            $status = "error";
            // $message = $ex->getMessage();
            $message = "İşlem sırasında bir hata oluştu. Lütfen sistem yöneticisi ile iletişime geçin.";
            $db->rollBack();
        }
        response($status, $message, $data);

        break;

    case 'delete':

        try {
            $UserModel = new UserModel();
            $db->beginTransaction();

            /**Kullanıcı var mı kontrol et */
            $user = $UserModel->find($id);

            if (!$user) {
                response("error", "Kullanıcı bulunamadı");
                exit;
            }

            $UserModel->softDelete($id);

            /**Kullanıcı durumunu pasif(2) yap */
            $data = [
                "id" => $id,
                "status" => 0,
            ];
            $UserModel->saveWithAttr($data);

            $status = "success";
            $message = "Kullanıcı başarıyla silindi";
            $data = $id;

            $db->commit();

        } catch (Exception $ex) {
            $db->rollBack();
            $status = "error";
            $message = "İşlem sırasında bir hata oluştu. Lütfen sistem yöneticisi ile iletişime geçin." ;
            $data = null;
        }

        response($status, $message, $data);




        break;

    case "durum-degistir":

        try {
            $UserModel = new UserModel();
            $db->beginTransaction();

        

            /**Kullanıcı var mı kontrol et */
            $user = $UserModel->find($id);

            if (!$user) {
                response("error", "Kullanıcı bulunamadı");
                exit;
            }

            $data = [
                "id" => $id,
                "status" => $post['status'] == 1 ? 2: 1,
            ];

            $UserModel->saveWithAttr($data);

            $status = "success";
            $message = "Kullanıcı durumu başarıyla değiştirildi";
            $data = $id;

            $db->commit();

        } catch (Exception $ex) {
            $db->rollBack();
            $status = "error";
            $message = "İşlem sırasında bir hata oluştu. Lütfen sistem yöneticisi ile iletişime geçin." ;
            $data = null;
        }

        response($status, $message, $data);



    case null:
        response("error", "Geçersiz işlem");

        break;

    default:
        response("error", "Geçersiz işlem");

        break;
}


function response($status, $message, $data = null)
{
    header('Content-Type: application/json');
    echo json_encode([
        "status" => $status,
        "message" => $message,
        "data" => $data,
    ]);
    exit;
}