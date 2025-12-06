<?php
use Model\AnketModel;
use Model\AnketVoteModel;
use Model\AnketOyModel;
use Model\AnketApprovalModel;

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$userId = (int)($_SESSION['user']->id ?? 0);

function computeVoteStats(int $surveyId, array $options): array {
    $normalize = function($s){ return mb_strtolower(trim((string)$s)); };
    $Vote = new AnketVoteModel();
    $Legacy = new AnketOyModel();
    $countsNew = $Vote->getCountsByOption($surveyId);
    $countsLegacy = $Legacy->getResults($surveyId);
    $map = [];
    foreach ($countsNew as $c) { $map[$normalize($c['option_text'])] = ($map[$normalize($c['option_text'])] ?? 0) + (int)$c['c']; }
    foreach ($countsLegacy['rows'] as $r) { $map[$normalize($r['option_text'])] = ($map[$normalize($r['option_text'])] ?? 0) + (int)$r['votes']; }
    $total = 0; foreach ($map as $v) { $total += (int)$v; }
    $out = [];
    foreach ($options as $opt) {
        $cnt = (int)($map[$normalize($opt)] ?? 0);
        $pct = $total > 0 ? round($cnt * 100 / $total) : 0;
        $out[] = ['option' => $opt, 'count' => $cnt, 'percent' => $pct];
    }
    return $out;
}

try {
    if ($action === 'surveys_to_review') {
        $Anket = new AnketModel();
        $list = $Anket->findWhere([], 'created_at DESC', null);
        $Approval = new AnketApprovalModel();
        $rows = [];
        foreach ($list as $r) {
            $counts = $Approval->getCounts((int)$r->id);
            $rows[] = [
                'id' => (int)$r->id,
                'title' => (string)($r->title ?? ''),
                'status' => (string)($r->status ?? ''),
                'end_date' => (string)($r->end_date ?? ''),
                'approved' => (int)($counts['approved'] ?? 0),
                'rejected' => (int)($counts['rejected'] ?? 0),
            ];
        }
        echo json_encode(['status' => 'success', 'data' => $rows], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($action === 'detail') {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { echo json_encode(['status' => 'error','message'=>'Geçersiz id']); exit; }
        $Anket = new AnketModel();
        $row = $Anket->find($id);
        if (!$row) { echo json_encode(['status'=>'error','message'=>'Anket bulunamadı']); exit; }
        $options = json_decode($row->options_json ?? '[]', true) ?: [];
        $Vote = new AnketVoteModel();
        $userVote = ($userId > 0) ? ($Vote->getUserVote((int)$row->id, $userId) ?? null) : null;
        $data = [
            'id' => (int)$row->id,
            'title' => (string)($row->title ?? ''),
            'description' => (string)($row->description ?? ''),
            'status' => (string)($row->status ?? ''),
            'end_date' => (string)($row->end_date ?? ''),
            'options' => $options,
            'user_vote' => $userVote,
        ];
        echo json_encode(['status'=>'success','data'=>$data], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($action === 'vote') {
        $id = (int)($_POST['id'] ?? 0);
        $selected = trim((string)($_POST['selected_option'] ?? ''));
        if ($id <= 0 || $selected === '') { echo json_encode(['status'=>'error','message'=>'Eksik parametre']); exit; }
        if ($userId <= 0) { echo json_encode(['status'=>'error','message'=>'Kullanıcı oturumu gerekli']); exit; }
        $Anket = new AnketModel();
        $row = $Anket->find($id);
        if (!$row) { echo json_encode(['status'=>'error','message'=>'Anket bulunamadı']); exit; }
        $options = json_decode($row->options_json ?? '[]', true) ?: [];
        $Vote = new AnketVoteModel();
        $ok = $Vote->upsertVote($id, $userId, $selected);
        if (!$ok) { echo json_encode(['status'=>'error','message'=>'Oy kaydedilemedi']); exit; }
        $stats = computeVoteStats($id, $options);
        echo json_encode(['status'=>'success','message'=>'Oyunuz kaydedildi','vote_stats'=>$stats], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode(['status'=>'error','message'=>'Geçersiz işlem']);
} catch (\Throwable $e) {
    echo json_encode(['status'=>'error','message'=>'Hata: '.$e->getMessage()]);
}

