<?php
require_once __DIR__ . '/../../../../configs/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');

use App\Helper\Security;
use Model\AnketModel;
use Model\AnketApprovalModel;
use Model\AnketVoteModel;

try {
    $skipAuth = defined('UNIT_TEST') && UNIT_TEST === true;
    if (!$skipAuth) { Security::checkLogin(); }

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $action = $_GET['action'] ?? $_POST['action'] ?? null;
    $exit = function(){ if (!(defined('UNIT_TEST') && UNIT_TEST === true)) { exit; } };

    $surveys = new AnketModel();
    $approvals = new AnketApprovalModel();
    $votes = new AnketVoteModel();

    if ($method === 'GET' && in_array($action, ['list','surveys_to_review'])) {
        $rows = $surveys->all();
        $data = [];
        foreach ($rows as $r) {
            if (in_array($r->status, ['Onay Bekliyor','Aktif','Yayında'])) {
                $counts = $approvals->getCounts((int)$r->id);
                $data[] = [
                    'id' => (int)$r->id,
                    'title' => $r->title,
                    'description' => $r->description,
                    'status' => $r->status,
                    'end_date' => $r->end_date,
                    'approved' => $counts['approved'],
                    'rejected' => $counts['rejected'],
                ];
            }
        }
        echo json_encode(['status'=>'success','data'=>$data]);
        $exit();
    }

    if ($method === 'GET' && $action === 'detail') {
        $id = $_GET['id'] ?? null;
        if (!$id) { http_response_code(400); echo json_encode(['status'=>'error','message'=>'ID gerekli']); $exit(); }
        $row = $surveys->find((int)$id);
        if (!$row) { http_response_code(404); echo json_encode(['status'=>'error','message'=>'Kayıt bulunamadı']); $exit(); }
        $row->options = json_decode($row->options_json ?: '[]', true);
        $row->counts = (new AnketApprovalModel())->getCounts((int)$id);
        $userId = $_SESSION['user']->id ?? null;
        $row->user_vote = $userId ? $votes->getUserVote((int)$id, (int)$userId) : null;
        echo json_encode(['status'=>'success','data'=>$row]);
        $exit();
    }

    if ($method === 'POST' && in_array($action, ['approve','reject'])) {
        $id = $_POST['id'] ?? null;
        $comment = $_POST['comment'] ?? null;
        $selected = $_POST['selected_option'] ?? null;
        if (!$id) { http_response_code(400); echo json_encode(['status'=>'error','message'=>'ID gerekli']); $exit(); }
        $userId = $_SESSION['user']->id ?? 0;
        if (!$skipAuth && !$userId) { http_response_code(401); echo json_encode(['status'=>'error','message'=>'Giriş yapınız']); $exit(); }
        $decision = $action === 'approve' ? 'onay' : 'red';
        $ok = $approvals->upsertDecision((int)$id, (int)$userId, $decision, $comment);
        if ($ok && $decision === 'onay' && $selected) { $votes->upsertVote((int)$id, (int)$userId, $selected); }
        if ($ok) {
            $counts = $approvals->getCounts((int)$id);
            $optCounts = $votes->getCountsByOption((int)$id);
            $totalVotes = 0; foreach ($optCounts as $oc) { $totalVotes += (int)$oc['c']; }
            $voteStats = array_map(function($oc) use ($totalVotes){
                $pct = $totalVotes > 0 ? round(($oc['c']/$totalVotes)*100) : 0;
                return [ 'option' => $oc['option_text'], 'count' => (int)$oc['c'], 'percent' => $pct ];
            }, $optCounts);
            echo json_encode(['status'=>'success','message'=>'Değerlendirmeniz kaydedildi','counts'=>$counts,'vote_stats'=>$voteStats,'total_votes'=>$totalVotes]);
        } else {
            http_response_code(500);
            echo json_encode(['status'=>'error','message'=>'Kaydedilirken hata oluştu']);
        }
        $exit();
    }

    if ($method === 'POST' && $action === 'vote') {
        $id = $_POST['id'] ?? null; $selected = $_POST['selected_option'] ?? null;
        $userId = $_SESSION['user']->id ?? 0;
        if (!$id || !$selected || !$userId) { http_response_code(400); echo json_encode(['status'=>'error','message'=>'ID ve seçenek gerekli']); $exit(); }
        $ok = $votes->upsertVote((int)$id, (int)$userId, $selected);
        echo json_encode(['status' => $ok ? 'success' : 'error', 'message' => $ok ? 'Oy kaydedildi' : 'Oy kaydedilemedi']);
        $exit();
    }

    http_response_code(404);
    echo json_encode(['status'=>'error','message'=>'Geçersiz istek']);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Sunucu hatası','error'=> $e->getMessage()]);
}