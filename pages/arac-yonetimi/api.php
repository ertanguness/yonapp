<?php
require_once dirname(__DIR__, 2) . '/configs/bootstrap.php';

use App\Helper\Security;
use Model\AraclarModel;
use Model\BloklarModel;
use Model\DairelerModel;
use Model\KisilerModel;
use Database\Db;

header('Content-Type: application/json; charset=utf-8');

Security::checkLogin();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$pdo = getDbConnection();
$model = new AraclarModel();
$table = 'araclar';
$db = Db::getInstance();

function respond($status, $message, $extra = []) {
    $res = array_merge(['status' => $status, 'message' => $message], $extra);
    echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function bodyJson() {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

if ($method !== 'POST') {
    respond('error', 'Method desteklenmiyor');
}

if ($method === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'arac-kaydet') {
        $idRaw = $_POST['id'] ?? null;
        $id = $idRaw ? (is_numeric($idRaw) ? (int)$idRaw : (int)Security::decrypt($idRaw)) : 0;
        $kisiId = (int)($_POST['kisi_id'] ?? 0);
        $plaka = strtoupper(trim($_POST['plaka'] ?? ''));
        $marka = trim($_POST['marka_model'] ?? '');
        try {
            if ($kisiId <= 0 || $plaka === '') {
                throw new \Exception('Zorunlu alanlar eksik');
            }
            if ($id === 0 && $model->AracVarmi($plaka)) {
                throw new \Exception('Plaka mevcut');
            }
            $db->beginTransaction();
            if ($id > 0) {
                $model->updateSingle($id, [
                    'kisi_id' => $kisiId,
                    'plaka' => $plaka,
                    'marka_model' => $marka,
                ]);
                $lastId = $id;
                $msg = 'Güncelleme başarılı';
            } else {
                $encId = $model->saveWithAttr([
                    'kisi_id' => $kisiId,
                    'plaka' => $plaka,
                    'marka_model' => $marka,
                    'kayit_tarihi' => date('Y-m-d H:i:s'),
                ]);
                $lastId = (int)Security::decrypt($encId);
                $msg = 'Kayıt başarılı';
            }
            $db->commit();
            respond('success', $msg, ['id' => $lastId]);
        } catch (\Throwable $e) {
            try { $db->rollBack(); } catch (\Throwable $x) {}
            respond('error', $e->getMessage());
        }
    }

    if ($action === 'arac-sil') {
        $idRaw = $_POST['id'] ?? null;
        $id = $idRaw ? (is_numeric($idRaw) ? (int)$idRaw : (int)Security::decrypt($idRaw)) : 0;
        try {
            $db->beginTransaction();
                $model->softDelete($id);
            $db->commit();
            respond('success', 'Kayıt silindi');
        } catch (\Throwable $e) {
            try { $db->rollBack(); } catch (\Throwable $x) {}
            respond('error', $e->getMessage());
        }
    }

    if ($action === 'bloklar-getir') {
        $siteId = (int)($_SESSION['site_id'] ?? 0);
        $rows = (new BloklarModel())->SiteBloklari($siteId);
        respond('success', 'Kayıtlar', ['data' => $rows]);
    }

    if ($action === 'daireler-getir') {
        $blokId = (int)($_POST['blok_id'] ?? 0);
        if ($blokId <= 0) respond('error', 'blok_id gerekli');
        $rows = (new DairelerModel())->BlokDaireleri($blokId);
        respond('success', 'Kayıtlar', ['data' => $rows]);
    }

    if ($action === 'kisiler-getir') {
        $daireId = (int)($_POST['daire_id'] ?? 0);
        if ($daireId <= 0) respond('error', 'daire_id gerekli');
        $rows = (new KisilerModel())->getKisilerByDaireId($daireId);
        respond('success', 'Kayıtlar', ['data' => $rows]);
    }

    if ($action === 'liste') {
        $sql = "SELECT * FROM {$table} ORDER BY kayit_tarihi DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_OBJ);
        respond('success', 'Liste', ['data' => $rows]);
    }
}

respond('error', 'Action desteklenmiyor');