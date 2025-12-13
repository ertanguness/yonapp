<?php
require_once __DIR__ . '/../../../../configs/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');

use App\Helper\Date;
use Model\AnketModel;
use App\Services\Gate;
use Model\AnketOyModel;
use App\Helper\Security;

try {
    $skipAuth = defined('UNIT_TEST') && UNIT_TEST === true;
    if (!$skipAuth) { Security::checkLogin(); }
    // if (!$skipAuth && !Gate::can('admin_anket_sayfa')) {
    //     http_response_code(403);
    //     echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim']);
    //     exit;
    // }

    $method = $_SERVER['REQUEST_METHOD'];
    $exit = function(){ if (!(defined('UNIT_TEST') && UNIT_TEST === true)) { exit; } };
    $action = $_GET['action'] ?? $_POST['action'] ?? null;
    $model = new AnketModel();
    $votes = new AnketOyModel();

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
        $end_date = Date::Ymd($_POST['end_date'] ?? null);
        $description = $_POST['description'] ?? null;
        $start_date = Date::Ymd($_POST['start_date'] ?? null);
        $status = $_POST['status'] ?? 'Aktif';

        if ($title === '' || count(array_filter($options, fn($o)=>trim($o) !== '')) < 2) {
            http_response_code(422);
            echo json_encode(['status'=>'error','message'=>'Başlık ve en az iki seçenek giriniz']);
            $exit();
        }

        $options = array_values(array_unique(array_map(fn($o)=>trim($o), $options)));

        $idEnc = $model->saveWithAttr([
            'title' => $title,
            'description' => $description,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'status' => $status,
            'options_json' => json_encode($options, JSON_UNESCAPED_UNICODE),
        ]);

        echo json_encode(['status'=>'success','message'=>'Anket oluşturuldu','id'=>$idEnc]);
        $exit();
    }

    if ($method === 'POST' && in_array($action, ['update'])) {
        $idRaw = $_POST['id'] ?? null; // Normal veya şifreli ID kabul edilir
        if (!$idRaw) { http_response_code(400); echo json_encode(['status'=>'error','message'=>'ID gerekli']); $exit(); }
        $id = ctype_digit((string)$idRaw) ? (int)$idRaw : Security::decrypt($idRaw);
        if (!$id) { http_response_code(400); echo json_encode(['status'=>'error','message'=>'Geçersiz ID']); $exit(); }

        $payload = [];
        foreach (['title','description','status'] as $f) {
            if (isset($_POST[$f])) { $payload[$f] = $_POST[$f]; }
        }
        if (isset($_POST['start_date'])) { $payload['start_date'] = Date::Ymd($_POST['start_date']); }
        if (isset($_POST['end_date'])) { $payload['end_date'] = Date::Ymd($_POST['end_date']); }
        if (isset($_POST['options'])) { $payload['options_json'] = json_encode($_POST['options'], JSON_UNESCAPED_UNICODE); }

        $model->saveWithAttr(array_merge(['id' => $id], $payload));
        echo json_encode(['status'=>'success','message'=>'Anket güncellendi']);
        $exit();
    }

    if ($method === 'POST' && $action === 'change_status') {
        $idRaw = $_POST['id'] ?? null; $status = $_POST['status'] ?? null;
        if (!$idRaw || !$status) { http_response_code(400); echo json_encode(['status'=>'error','message'=>'ID ve durum gerekli']); $exit(); }
        $id = ctype_digit((string)$idRaw) ? (int)$idRaw : Security::decrypt($idRaw);
        if (!$id) { http_response_code(400); echo json_encode(['status'=>'error','message'=>'Geçersiz ID']); $exit(); }
        $model->saveWithAttr(['id' => $id, 'status' => $status]);
        echo json_encode(['status'=>'success','message'=>'Durum güncellendi']);
        $exit();
    }

    if (in_array($action, ['delete'])) {
        $idRaw = $_POST['id'] ?? $_GET['id'] ?? null;
        if (!$idRaw) { http_response_code(400); echo json_encode(['status'=>'error','message'=>'ID gerekli']); $exit(); }
        if (ctype_digit((string)$idRaw)) {
            $ok = $model->deleteByColumn('id', (int)$idRaw);
            if ($ok !== true) { http_response_code(404); echo json_encode(['status'=>'error','message'=>'Kayıt bulunamadı']); $exit(); }
        } else {
            $model->delete($idRaw);
        }
        echo json_encode(['status'=>'success','message'=>'Anket silindi']);
        $exit();
    }

    http_response_code(404);
    echo json_encode(['status'=>'error','message'=>'Geçersiz istek']);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Sunucu hatası','error'=> $e->getMessage()]);
}
    if ($action === 'vote') {
        if (!$skipAuth) { Security::checkLogin(); }
        $surveyId = intval($_POST['survey_id'] ?? $_GET['survey_id'] ?? 0);
        $option = trim($_POST['option'] ?? $_GET['option'] ?? '');
        if (!$surveyId || $option === '') { http_response_code(400); echo json_encode(['status'=>'error','message'=>'Geçersiz parametre']); $exit(); }
        $row = $model->find($surveyId);
        if (!$row) { http_response_code(404); echo json_encode(['status'=>'error','message'=>'Anket bulunamadı']); $exit(); }
        $opts = json_decode($row->options_json ?: '[]', true);
        if (!in_array($option, $opts, true)) { http_response_code(422); echo json_encode(['status'=>'error','message'=>'Geçersiz seçenek']); $exit(); }
        $userId = $_SESSION['user']->id ?? null;
        $votes->addVote($surveyId, $option, $userId);
        $model->updateWhere('id', $surveyId, ['total_votes' => ($row->total_votes ?? 0) + 1]);
        echo json_encode(['status'=>'success','message'=>'Oy kaydedildi']);
        $exit();
    }

    if ($method === 'GET' && $action === 'results') {
        $surveyId = intval($_GET['survey_id'] ?? 0);
        if (!$surveyId) { http_response_code(400); echo json_encode(['status'=>'error','message'=>'ID gerekli']); $exit(); }
        $row = $model->find($surveyId);
        if (!$row) { http_response_code(404); echo json_encode(['status'=>'error','message'=>'Kayıt bulunamadı']); $exit(); }
        $res = $votes->getResults($surveyId);
        $opts = json_decode($row->options_json ?: '[]', true);
        $total = $res['total'];
        $byOpt = [];
        foreach ($opts as $o) { $byOpt[$o] = 0; }
        foreach ($res['rows'] as $r) { $byOpt[$r['option_text']] = (int)$r['votes']; }
        $options = [];
        foreach ($byOpt as $text => $count) {
            $percent = $total > 0 ? round(($count * 100.0) / $total) : 0;
            $options[] = ['option_text'=>$text, 'votes'=>$count, 'percent'=>$percent];
        }
        echo json_encode(['status'=>'success','total'=>$total,'options'=>$options]);
        $exit();
    }
