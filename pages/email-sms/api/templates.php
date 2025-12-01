<?php
require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';

use App\Helper\Security;
use App\Services\Gate;
use Model\MessageTemplateModel;

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$siteId = $_SESSION['site_id'] ?? null;

// CSRF zorunluluğu: GET haricinde
function requireCsrf(): void {
    $token = $_GET['csrf_token'] ?? ($_POST['csrf_token'] ?? null);
    if (!$token) {
        $raw = file_get_contents('php://input');
        if ($raw) {
            $json = json_decode($raw, true);
            $token = $json['csrf_token'] ?? null;
        }
    }
    if (!$token || !hash_equals((string)$token, (string)Security::csrf())) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz CSRF token']);
        exit;
    }
}

// Yetki kontrolü
if (!Gate::allows('email_sms_gonder')) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Bu işlem için yetkiniz yok.']);
    exit;
}

$tplModel = new MessageTemplateModel();

try {
    if ($method === 'GET') {
        $type = isset($_GET['type']) && in_array($_GET['type'], ['sms','email'], true) ? $_GET['type'] : 'sms';
        $items = $tplModel->listTemplates($siteId ? (int)$siteId : null, $type);
        echo json_encode(['status' => 'success', 'items' => $items], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($method === 'POST') {
        requireCsrf();
        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        $type = isset($data['type']) && in_array($data['type'], ['sms','email'], true) ? $data['type'] : 'sms';
        $name = trim((string)($data['name'] ?? ''));
        $subject = isset($data['subject']) ? trim((string)$data['subject']) : null;
        $body = (string)($data['body'] ?? '');
        $variables = isset($data['variables']) ? (is_array($data['variables']) ? json_encode($data['variables'], JSON_UNESCAPED_UNICODE) : (string)$data['variables']) : null;
        if ($name === '' || $body === '') {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'İsim ve içerik zorunludur.']);
            exit;
        }
        $id = $tplModel->createTemplate([
            'site_id' => $siteId,
            'type' => $type,
            'name' => $name,
            'subject' => $subject,
            'body' => $body,
            'variables' => $variables,
        ]);
        echo json_encode(['status' => 'success', 'message' => 'Şablon kaydedildi', 'id' => $id], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($method === 'PUT') {
        requireCsrf();
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?: [];
        $id = (int)($data['id'] ?? 0);
        if (!$id) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz şablon ID']);
            exit;
        }
        $update = [];
        if (isset($data['name'])) { $update['name'] = trim((string)$data['name']); }
        if (array_key_exists('subject', $data)) { $update['subject'] = $data['subject'] !== null ? trim((string)$data['subject']) : null; }
        if (isset($data['body'])) { $update['body'] = (string)$data['body']; }
        if (isset($data['variables'])) { $update['variables'] = is_array($data['variables']) ? json_encode($data['variables'], JSON_UNESCAPED_UNICODE) : (string)$data['variables']; }
        if (!$update) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Güncellenecek alan yok']);
            exit;
        }
        $update['updated_at'] = date('Y-m-d H:i:s');
        $tplModel->updateSingle($id, $update);
        echo json_encode(['status' => 'success', 'message' => 'Şablon güncellendi']);
        exit;
    }

    if ($method === 'DELETE') {
        requireCsrf();
        $id = null;
        if (isset($_GET['id'])) { $id = (int)$_GET['id']; }
        if (!$id) {
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true) ?: [];
            $id = (int)($data['id'] ?? 0);
        }
        if (!$id) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz şablon ID']);
            exit;
        }
        $tplModel->delete(Security::encrypt($id));
        echo json_encode(['status' => 'success', 'message' => 'Şablon silindi']);
        exit;
    }

    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'İzin verilmeyen yöntem']);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

