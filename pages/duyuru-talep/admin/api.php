<?php
require_once dirname(__DIR__, levels: 3) . '/configs/bootstrap.php';

use Model\DuyuruTalepModel;
use Model\SurveysModel;
use Model\SurveyOptionsModel;
use App\Services\Gate;

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? $_GET['action'] ?? null;

if (!$action) {
    echo json_encode(['status' => 'error', 'message' => 'Geçersiz işlem']);
    exit;
}

switch ($action) {
    case 'announcements_datatable':
        Gate::can('announcements_admin_page');
        $request = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
        $columns = [
            ['db' => 'id', 'dt' => 0],
            ['db' => 'title', 'dt' => 1],
            ['db' => 'content', 'dt' => 2],
            ['db' => 'start_date', 'dt' => 3],
            ['db' => 'end_date', 'dt' => 4],
            ['db' => 'target_type', 'dt' => 5],
            ['db' => 'status', 'dt' => 6],
        ];
        try {
            $model = new DuyuruTalepModel();
            echo $model->serverProcessing($request, $columns, null, null, true, null, 'id');
        } catch (\Throwable $e) {
            $draw = intval($request['draw'] ?? 0);
            echo json_encode([
                'draw' => $draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'announcements verisi getirilemedi: ' . $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
        break;

    case 'announcement_save':
        Gate::can('announcements_admin_page');
        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'content' => trim($_POST['content'] ?? ''),
            'target_type' => $_POST['target_type'] ?? 'all',
            'block_id' => !empty($_POST['block_id']) ? intval($_POST['block_id']) : null,
            'start_date' => $_POST['start_date'] ?? null,
            'end_date' => $_POST['end_date'] ?? null,
            'status' => $_POST['status'] ?? 'draft',
            'created_by' => $_SESSION['user']->id ?? null,
        ];
        if (!$data['title'] || !$data['content']) {
            echo json_encode(['status' => 'error', 'message' => 'Başlık ve içerik zorunludur']);
            exit;
        }
        try {
            $model = new DuyuruTalepModel();
            $encId = $model->saveWithAttr($data);
            getLogger()->info('Announcement saved', ['title' => $data['title'], 'user' => $_SESSION['user']->id ?? null]);
            echo json_encode(['status' => 'success', 'message' => 'Duyuru kaydedildi', 'id' => $encId]);
        } catch (\Throwable $e) {
            getLogger()->error('Announcement save failed', ['error' => $e->getMessage()]);
            echo json_encode(['status' => 'error', 'message' => 'Duyuru kaydedilemedi']);
        }
        break;

    case 'announcement_delete':
        Gate::can('announcements_admin_page');
        $id = $_POST['id'] ?? null;
        try {
            $model = new DuyuruTalepModel();
            $model->delete($id);
            getLogger()->info('Announcement deleted', ['id' => $id, 'user' => $_SESSION['user']->id ?? null]);
            echo json_encode(['status' => 'success', 'message' => 'Duyuru silindi']);
        } catch (\Throwable $e) {
            getLogger()->error('Announcement delete failed', ['error' => $e->getMessage()]);
            echo json_encode(['status' => 'error', 'message' => 'Duyuru silinemedi']);
        }
        break;

    case 'survey_save':
        Gate::can('survey_admin_page');
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $end_date = $_POST['end_date'] ?? null;
        $options = $_POST['options'] ?? [];
        if (!$title || empty($options)) {
            echo json_encode(['status' => 'error', 'message' => 'Başlık ve en az bir seçenek gereklidir']);
            exit;
        }
        $db = \Database\Db::getInstance();
        $pdo = $db->connect();
        try {
            $pdo->beginTransaction();
            $surveyModel = new SurveysModel();
            $surveyIdEnc = $surveyModel->saveWithAttr([
                'title' => $title,
                'description' => $description,
                'end_date' => $end_date,
                'status' => 'Aktif',
                'created_by' => $_SESSION['user']->id ?? null,
            ]);
            $surveyId = \App\Helper\Security::decrypt($surveyIdEnc);
            $optModel = new SurveyOptionsModel();
            foreach ($options as $opt) {
                $optText = trim($opt);
                if ($optText) {
                    $optModel->saveWithAttr([
                        'survey_id' => $surveyId,
                        'option_text' => $optText,
                    ]);
                }
            }
            $pdo->commit();
            getLogger()->info('Survey created', ['title' => $title]);
            echo json_encode(['status' => 'success', 'message' => 'Anket oluşturuldu']);
        } catch (\Throwable $e) {
            $pdo->rollBack();
            getLogger()->error('Survey create failed', ['error' => $e->getMessage()]);
            echo json_encode(['status' => 'error', 'message' => 'Anket oluşturulamadı']);
        }
        break;

    case 'surveys_list':
        Gate::can('survey_admin_page');
        $db = \getDbConnection();
        $stmt = $db->query("SELECT id, title, end_date, status FROM surveys ORDER BY id DESC");
        echo json_encode($stmt->fetchAll(\PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
        break;

    case 'survey_results':
        Gate::can('survey_admin_page');
        $surveyId = intval($_GET['survey_id'] ?? 0);
        if (!$surveyId) { echo json_encode(['status'=>'error','message'=>'Geçersiz anket']); exit; }
        $db = \getDbConnection();
        $totalStmt = $db->prepare('SELECT COUNT(*) as total FROM survey_votes WHERE survey_id = ?');
        $totalStmt->execute([$surveyId]);
        $total = (int)($totalStmt->fetchColumn() ?: 0);
        $optStmt = $db->prepare('SELECT o.id, o.option_text, (
            SELECT COUNT(*) FROM survey_votes v WHERE v.survey_id = o.survey_id AND v.option_id = o.id
        ) as votes FROM survey_options o WHERE o.survey_id = ?');
        $optStmt->execute([$surveyId]);
        $rows = $optStmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['percent'] = $total ? round(($r['votes'] / $total) * 100) : 0;
        }
        echo json_encode(['total'=>$total, 'options'=>$rows], JSON_UNESCAPED_UNICODE);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Tanınmayan işlem']);
}

exit;