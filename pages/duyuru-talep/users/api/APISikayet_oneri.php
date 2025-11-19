<?php
require_once dirname(__DIR__, 4) . '/configs/bootstrap.php';

use Model\SikayetOneriModel;
use App\Helper\Security;
use App\Controllers\AuthController;

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? $_GET['action'] ?? null;
$id = Security::decrypt($_POST['id'] ?? 0) ?? 0;

if (!$action) { echo json_encode(['status'=>'error','message'=>'Geçersiz işlem']); exit; }

$user = AuthController::user();
if (!$user) { echo json_encode(['status'=>'error','message'=>'Oturum gerekli']); exit; }

$siteId = $_SESSION['site_id'] ?? null;
$model = new SikayetOneriModel();

switch ($action) {
     case 'CreateOrUpdate':
        $type = trim($_POST['inpType'] ?? 'Şikayet');
        $title = trim($_POST['inpTitle'] ?? '');
        $content = trim($_POST['inpContent'] ?? '');
        if ($title === '' || $content === '') {
            echo json_encode(['status'=>'error','message'=>'Başlık ve içerik zorunludur']);
            break;
        }
        try {
            $data = [
                'id' => $id,
                'kisi_id' => (int)$user->id,
                'site_id' => $siteId ? (int)$siteId : null,
                'type' => $type,
                'title' => $title,
                'message' => $content,
            ];
            $encId = $model->saveWithAttr($data);
            echo json_encode(['status'=>'success','message'=>'Gönderildi','id'=>$encId]);
        } catch (\Throwable $e) {
            echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
        }
        break;


    case 'delete':
        $rawId = $_POST['id'] ?? $_GET['id'] ?? 0;
        $id = is_numeric($rawId) ? intval($rawId) : intval(Security::decrypt($rawId) ?? 0);
        if (!$id) { echo json_encode(['status'=>'error','message'=>'Geçersiz ID']); break; }
        $record = $model->find($id);
        if (!$record || intval($record->kisi_id) !== intval($user->id)) {
            echo json_encode(['status'=>'error','message'=>'Yetkisiz işlem']);
            break;
        }
        try {
            $model->deleteByColumn('id', $id);
            echo json_encode(['status'=>'success','message'=>'Silindi']);
        } catch (\Throwable $e) {
            echo json_encode(['status'=>'error','message'=>'Silme başarısız']);
        }
        break;

    default:
        echo json_encode(['status'=>'error','message'=>'Tanınmayan işlem']);
}

exit;