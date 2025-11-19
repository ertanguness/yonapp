<?php
require_once dirname(__DIR__, 4) . '/configs/bootstrap.php';

use Model\SikayetOneriModel;
use App\Controllers\AuthController;

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? $_GET['action'] ?? null;

if (!$action) { echo json_encode(['status'=>'error','message'=>'Geçersiz işlem']); exit; }

$user = AuthController::user();
if (!$user) { echo json_encode(['status'=>'error','message'=>'Oturum gerekli']); exit; }

$siteId = $_SESSION['site_id'] ?? null;
$model = new SikayetOneriModel();

switch ($action) {
    case 'list':
        $rows = $model->listByUser((int)$user->id, $siteId ? (int)$siteId : null);
        echo json_encode(['status'=>'success','data'=>$rows]);
        break;

    case 'create':
        $type = trim($_POST['type'] ?? 'Şikayet');
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        if ($title === '' || $content === '') {
            echo json_encode(['status'=>'error','message'=>'Başlık ve içerik zorunludur']);
            break;
        }
        try {
            $encId = $model->createForUser((int)$user->id, $siteId ? (int)$siteId : null, [
                'type' => $type,
                'title' => $title,
                'content' => $content,
            ]);
            echo json_encode(['status'=>'success','message'=>'Gönderildi','id'=>$encId]);
        } catch (\Throwable $e) {
            echo json_encode(['status'=>'error','message'=>'Kayıt başarısız']);
        }
        break;

    default:
        echo json_encode(['status'=>'error','message'=>'Tanınmayan işlem']);
}

exit;