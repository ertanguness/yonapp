<?php
require_once __DIR__ . '/../../../../configs/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');

use App\Helper\Security;
use App\Services\Gate;
use Model\AnketModel;

try {
    $skipAuth = defined('UNIT_TEST') && UNIT_TEST === true;
    if (!$skipAuth) { Security::checkLogin(); }
    if (!$skipAuth && !Gate::can('survey_admin_page')) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim']);
        exit;
    }

    $method = $_SERVER['REQUEST_METHOD'];
    $exit = function(){ if (!(defined('UNIT_TEST') && UNIT_TEST === true)) { exit; } };
    $action = $_GET['action'] ?? $_POST['action'] ?? null;
    $model = new AnketModel();

    if ($method === 'GET' && in_array($action, ['surveys_list','list'])) {
        $rows = $model->all();
        $data = array_map(function($r){
            return [
                'id' => $r->id,
                'id_enc' => Security::encrypt($r->id),
                'title' => $r->title,
                'created_at' => $r->created_at ?? null,
                'end_date' => $r->end_date,
                'status' => $r->status === 'Taslak' ? 'Taslak' : ($r->status ?? 'Aktif'),
                'total_votes' => $r->total_votes ?? 0,
            ];
        }, $rows);
        echo json_encode($data);
        $exit();
    }

    if ($method === 'GET' && $action === 'get' ) {
        $id = $_GET['id'] ?? null;
        if (!$id) { http_response_code(400); echo json_encode(['status'=>'error','message'=>'ID gerekli']); $exit(); }
        $row = $model->find($id);
        if (!$row) { http_response_code(404); echo json_encode(['status'=>'error','message'=>'Kayıt bulunamadı']); $exit(); }
        $row->id_enc = Security::encrypt($row->id);
        $row->options = json_decode($row->options_json ?: '[]', true);
        echo json_encode(['status'=>'success','data'=>$row]);
        $exit();
    }

    if ($method === 'POST' && in_array($action, ['survey_save','create'])) {
        $title = trim($_POST['title'] ?? '');
        $options = $_POST['options'] ?? [];
        $end_date = $_POST['end_date'] ?? null;
        $description = $_POST['description'] ?? null;
        $start_date = $_POST['start_date'] ?? null;
        $status = $_POST['status'] ?? 'Aktif';

        if ($title === '' || count(array_filter($options, fn($o)=>trim($o) !== '')) < 2) {
            http_response_code(422);
            echo json_encode(['status'=>'error','message'=>'Başlık ve en az iki seçenek giriniz']);
            $exit();
        }

        $options = array_values(array_unique(array_map(fn($o)=>trim($o), $options)));

        $idEnc = $model->create([
            'title' => $title,
            'description' => $description,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'status' => $status,
            'options' => $options,
        ]);

        echo json_encode(['status'=>'success','message'=>'Anket oluşturuldu','id'=>$idEnc]);
        $exit();
    }

    if ($method === 'POST' && in_array($action, ['update'])) {
        $id = $_POST['id'] ?? null; // Normal ID beklenir
        if (!$id) { http_response_code(400); echo json_encode(['status'=>'error','message'=>'ID gerekli']); $exit(); }

        $payload = [];
        foreach (['title','description','start_date','end_date','status'] as $f) {
            if (isset($_POST[$f])) { $payload[$f] = $_POST[$f]; }
        }
        if (isset($_POST['options'])) { $payload['options'] = $_POST['options']; }

        $model->updateById($id, $payload);
        echo json_encode(['status'=>'success','message'=>'Anket güncellendi']);
        $exit();
    }

    if (in_array($action, ['delete'])) {
        $idEnc = $_POST['id'] ?? $_GET['id'] ?? null; // Şifreli ID beklenir
        if (!$idEnc) { http_response_code(400); echo json_encode(['status'=>'error','message'=>'ID gerekli']); $exit(); }
        $model->delete($idEnc);
        echo json_encode(['status'=>'success','message'=>'Anket silindi']);
        $exit();
    }

    http_response_code(404);
    echo json_encode(['status'=>'error','message'=>'Geçersiz istek']);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Sunucu hatası','error'=> $e->getMessage()]);
}