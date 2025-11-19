<?php
require_once dirname(__DIR__, levels: 3) . '/configs/bootstrap.php';

use Model\ComplaintsModel;
use App\Services\Gate;

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? $_GET['action'] ?? null;

if (!$action) {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem']);
    exit;
}

switch ($action) {
    case 'complaint_save':
        Gate::can('complaints_peoples_page');
        $data = [
            'user_id' => $_SESSION['user']->id ?? null,
            'title' => trim($_POST['title'] ?? ''),
            'type' => $_POST['type'] ?? 'Şikayet',
            'message' => trim($_POST['message'] ?? ''),
            'status' => 'Yeni',
        ];
        if (!$data['user_id']) {
            echo json_encode(['status' => 'error', 'message' => 'Oturum bulunamadı']);
            exit;
        }
        if (!$data['title'] || !$data['message']) {
            echo json_encode(['status' => 'error', 'message' => 'Başlık ve açıklama zorunludur']);
            exit;
        }
        try {
            $model = new ComplaintsModel();
            $encId = $model->saveWithAttr($data);
            getLogger()->info('Complaint created', ['title' => $data['title'], 'user' => $data['user_id']]);
            echo json_encode(['status' => 'success', 'message' => 'Talebiniz alındı', 'id' => $encId]);
        } catch (\Throwable $e) {
            getLogger()->error('Complaint create failed', ['error' => $e->getMessage()]);
            echo json_encode(['status' => 'error', 'message' => 'Talep oluşturulamadı']);
        }
        break;

    case 'complaints_list':
        Gate::can('complaints_peoples_page');
        $userId = $_SESSION['user']->id ?? 0;
        $db = \getDbConnection();
        $stmt = $db->prepare("SELECT id, title, type, status, created_at FROM complaints WHERE user_id = ? ORDER BY id DESC");
        $stmt->execute([$userId]);
        echo json_encode($stmt->fetchAll(\PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Tanınmayan işlem']);
}

exit;