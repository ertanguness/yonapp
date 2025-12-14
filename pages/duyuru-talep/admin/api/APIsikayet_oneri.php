<?php
require_once dirname(__DIR__, 4) . '/configs/bootstrap.php';

use Model\SikayetOneriModel;
use App\Services\Gate;
use App\Controllers\AuthController;

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? $_GET['action'] ?? null;

if (!$action) {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem']);
    exit;
}

switch ($action) {
    case 'list':
        Gate::can('announcements_admin_page');
        $siteId = $_SESSION['site_id'] ?? null;
        $model = new SikayetOneriModel();
        $rows = $model->findAllByUserName($siteId);
        echo json_encode(['status' => 'success', 'data' => $rows]);
        break;

    case 'complaint_update':
        Gate::can('announcements_admin_page');
        $id = intval($_POST['id'] ?? 0);
        $status = trim($_POST['status'] ?? '');
        $reply = trim($_POST['reply_message'] ?? '');
        if (!$id || !$status) { echo json_encode(['status'=>'error','message'=>'Geçersiz veri']); break; }
        $data = [
            'status' => $status,
            'reply_message' => $reply ?: null,
            'reply_at' => $reply ? date('Y-m-d H:i:s') : null,
        ];
        try {
            $model = new SikayetOneriModel();
            $model->updateSingle($id, $data);
            echo json_encode(['status'=>'success','message'=>'Güncellendi']);
        } catch (\Throwable $e) {
            echo json_encode(['status'=>'error','message'=>'Güncelleme başarısız']);
        }
        break;

    case 'delete':
        Gate::can('announcements_admin_page');
        $id = $_POST['id'] ?? $_GET['id'] ?? null;
        $id = $id ? (int)$id : 0;
        if (!$id) { echo json_encode(['status'=>'error','message'=>'Geçersiz ID']); break; }
        try {
            $model = new SikayetOneriModel();
            $model->deleteByColumn('id', $id);
            echo json_encode(['status'=>'success','message'=>'Silindi']);
        } catch (\Throwable $e) {
            echo json_encode(['status'=>'error','message'=>'Silme başarısız']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Tanınmayan işlem']);
}

exit;