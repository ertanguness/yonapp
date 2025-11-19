<?php

require_once dirname(__DIR__, 2) . '/configs/bootstrap.php';

use App\Helper\Security;
use App\Services\Gate;
use Model\AcilDurumKisileriModel;
use Database\Db;
use Model\BloklarModel;
use Model\DairelerModel;
use Model\KisilerModel;

header('Content-Type: application/json; charset=utf-8');

Security::checkLogin();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$pdo = getDbConnection();
$model = new AcilDurumKisileriModel();
$table = 'acil_durum_kisileri';
$db = Db::getInstance();

function json($data, int $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function bodyJson() {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function respond($status, $message, $extra = []) {
    $res = array_merge(['status' => $status, 'message' => $message], $extra);
    echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Yalnızca action tabanlı POST isteklerini kabul et
if ($method !== 'POST') {
    respond('error', 'Method desteklenmiyor');
}

if ($method === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'acil-kisi-kaydet') {
        Gate::can('acil_durum_kisi_ekle');
        $idRaw = $_POST['id'] ?? null;
        $id = $idRaw ? (is_numeric($idRaw) ? (int)$idRaw : (int)Security::decrypt($idRaw)) : 0;
        $kisiId = (int)($_POST['kisi_id'] ?? 0);
        $adiSoyadi = trim($_POST['adi_soyadi'] ?? '');
        $telefon = preg_replace('/\D+/', '', (string)($_POST['telefon'] ?? ''));
        $yakinlik = trim($_POST['yakinlik'] ?? '');
        try {
            if ($kisiId <= 0 || $adiSoyadi === '' || strlen($telefon) < 10 || $yakinlik === '') {
                throw new \Exception('Zorunlu alanlar eksik');
            }
            if ($id === 0 && $model->AcilDurumKisiVarmi($telefon)) {
                throw new \Exception('Telefon mevcut');
            }
            $db->beginTransaction();
            if ($id > 0) {
                $model->updateSingle($id, [
                    'kisi_id' => $kisiId,
                    'adi_soyadi' => $adiSoyadi,
                    'telefon' => $telefon,
                    'yakinlik' => $yakinlik,
                ]);
                $lastId = $id;
                $msg = 'Güncelleme başarılı';
            } else {
                $encId = $model->saveWithAttr([
                    'kisi_id' => $kisiId,
                    'adi_soyadi' => $adiSoyadi,
                    'telefon' => $telefon,
                    'yakinlik' => $yakinlik,
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

    if ($action === 'acil-kisi-getir') {
        $idRaw = $_POST['id'] ?? null;
        $id = $idRaw ? (is_numeric($idRaw) ? (int)$idRaw : (int)Security::decrypt($idRaw)) : 0;
        try {
            $row = $model->find($id);
            if (!$row) throw new \Exception('Kayıt bulunamadı');
            respond('success', 'Kayıt bulundu', ['data' => $row]);
        } catch (\Throwable $e) {
            respond('error', $e->getMessage());
        }
    }

    if ($action === 'acil-kisi-sil') {
        Gate::can('acil_durum_kisi_sil');
        $idRaw = $_POST['id'] ?? null;
        $id = $idRaw ? (is_numeric($idRaw) ? (int)$idRaw : (int)Security::decrypt($idRaw)) : 0;
        try {
            $db->beginTransaction();
            if ($model->hasColumn('silinme_tarihi')) {
                $model->softDelete($id);
            } else {
                $model->delete(Security::encrypt($id));
            }
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

    if ($action === 'kisi-detay-getir') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) respond('error', 'id gerekli');
        $row = (new KisilerModel())->getPersonById($id);
        if (!$row) respond('error','Kayıt bulunamadı');
        respond('success', 'Kayıt', ['data' => $row]);
    }

    if ($action === 'liste') {
        $orderCol = $model->hasColumn('kayit_tarihi') ? 'kayit_tarihi' : 'id';
        $orderDir = 'DESC';
        $where = $model->hasColumn('silinme_tarihi') ? ' WHERE silinme_tarihi IS NULL' : '';
        $sql = "SELECT * FROM {$table}{$where} ORDER BY {$orderCol} {$orderDir}";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_OBJ);
        respond('success', 'Liste', ['data' => $rows]);
    }
}

respond('error', 'Action desteklenmiyor');

if ($method === 'GET') {
    $model->ensureIndexes();
    $type = $_GET['type'] ?? null;
    if ($type === 'list') {
        $orderCol = $model->hasColumn('kayit_tarihi') ? 'kayit_tarihi' : 'id';
        $orderDir = 'DESC';
        $where = $model->hasColumn('silinme_tarihi') ? ' WHERE silinme_tarihi IS NULL' : '';
        $sql = "SELECT * FROM {$table}{$where} ORDER BY {$orderCol} {$orderDir}";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_OBJ);
        if (ob_get_length()) { ob_end_clean(); }
        return json(['data' => $rows]);
    }
    if ($type === 'blocks') {
        $siteId = (int)($_GET['site_id'] ?? ($_SESSION['site_id'] ?? 0));
        $rows = (new BloklarModel())->SiteBloklari($siteId);
        return json(['status' => 'ok', 'data' => $rows]);
    }
    if ($type === 'apartments') {
        $blokId = (int)($_GET['blok_id'] ?? 0);
        if ($blokId <= 0) return json(['status' => 'error','message'=>'blok_id gerekli'],422);
        $rows = (new DairelerModel())->BlokDaireleri($blokId);
        return json(['status' => 'ok', 'data' => $rows]);
    }
    if ($type === 'people') {
        $daireId = (int)($_GET['daire_id'] ?? 0);
        if ($daireId <= 0) return json(['status' => 'error','message'=>'daire_id gerekli'],422);
        $rows = (new KisilerModel())->getKisilerByDaireId($daireId);
        return json(['status' => 'ok', 'data' => $rows]);
    }
    $id = $_GET['id'] ?? null;
    if ($id) {
        $decId = is_numeric($id) ? (int)$id : (int)Security::decrypt($id);
        $row = $model->find($decId);
        if (!$row) json(['status' => 'error', 'message' => 'Kayıt bulunamadı'], 404);
        $kisi = null;
        if (!empty($row->kisi_id)) {
            $kisi = (new KisilerModel())->getPersonById((int)$row->kisi_id);
        }
        json(['status' => 'ok', 'data' => $row, 'kisi' => $kisi]);
    }
    $start = isset($_GET['start']) ? (int)$_GET['start'] : 0;
    $length = isset($_GET['length']) ? (int)$_GET['length'] : 25;
    if ($length <= 0) $length = 25;
    $name = trim($_GET['name'] ?? '');
    $phone = trim($_GET['phone'] ?? '');
    $rel = trim($_GET['relation'] ?? '');
    $order = strtolower($_GET['order'] ?? 'desc');
    $orderDir = $order === 'asc' ? 'ASC' : 'DESC';
    $orderCol = $model->hasColumn('kayit_tarihi') ? 'kayit_tarihi' : 'id';
    $sql = "SELECT * FROM {$table}";
    if ($model->hasColumn('silinme_tarihi')) { $sql .= " WHERE silinme_tarihi IS NULL"; }
    $bind = [];
    if ($name !== '') { $sql .= " AND adi_soyadi LIKE ?"; $bind[] = "%$name%"; }
    if ($phone !== '') { $sql .= " AND telefon LIKE ?"; $bind[] = "%$phone%"; }
    if ($rel !== '') { $sql .= " AND yakinlik = ?"; $bind[] = $rel; }
    $countStmt = $pdo->prepare(str_replace('SELECT *', 'SELECT COUNT(*)', $sql));
    $countStmt->execute($bind);
    $total = (int)$countStmt->fetchColumn();
    $sql .= " ORDER BY $orderCol $orderDir LIMIT ?, ?";
    $bind[] = $start;
    $bind[] = $length;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($bind);
    $rows = $stmt->fetchAll(PDO::FETCH_OBJ);
    json(['status' => 'ok', 'data' => $rows, 'total' => $total, 'start' => $start, 'length' => $length]);
}

if ($method === 'POST') {
    Gate::can('acil_durum_kisi_ekle');
    //if (!Security::checkCsrfToken()) json(['status' => 'error', 'message' => 'CSRF doğrulaması başarısız'], 403);
    $in = $_POST;
    if (empty($in)) $in = bodyJson();
    $kisiId = (int)($in['kisi_id'] ?? 0);
    $adiSoyadi = trim($in['adi_soyadi'] ?? '');
    $telefon = preg_replace('/\D+/', '', (string)($in['telefon'] ?? ''));
    $yakinlik = trim($in['yakinlik'] ?? '');
    if ($kisiId <= 0 || $adiSoyadi === '' || strlen($telefon) < 10 || $yakinlik === '') {
        json(['status' => 'error', 'message' => 'Zorunlu alanlar eksik'], 422);
    }
    if ($model->AcilDurumKisiVarmi($telefon)) {
        json(['status' => 'error', 'message' => 'Telefon mevcut'], 409);
    }
    try {
        $pdo->beginTransaction();
        $encId = $model->saveWithAttr([
            'kisi_id' => $kisiId,
            'adi_soyadi' => $adiSoyadi,
            'telefon' => $telefon,
            'yakinlik' => $yakinlik,
            'kayit_tarihi' => date('Y-m-d H:i:s'),
        ]);
        $pdo->commit();
        json(['status' => 'ok', 'id' => $encId]);
    } catch (Throwable $e) {
        $pdo->rollBack();
        json(['status' => 'error', 'message' => 'İşlem başarısız'], 500);
    }
}

if ($method === 'PUT') {
    Gate::can('acil_durum_kisileri_ekle');
    //if (!Security::checkCsrfToken()) json(['status' => 'error', 'message' => 'CSRF doğrulaması başarısız'], 403);
    $in = bodyJson();
    $idRaw = $in['id'] ?? null;
    if (!$idRaw) json(['status' => 'error', 'message' => 'ID gerekli'], 422);
    $id = is_numeric($idRaw) ? (int)$idRaw : (int)Security::decrypt($idRaw);
    $data = [];
    if (isset($in['adi_soyadi'])) $data['adi_soyadi'] = trim($in['adi_soyadi']);
    if (isset($in['telefon'])) $data['telefon'] = preg_replace('/\D+/', '', (string)$in['telefon']);
    if (isset($in['yakinlik'])) $data['yakinlik'] = trim($in['yakinlik']);
    if (empty($data)) json(['status' => 'error', 'message' => 'Güncellenecek alan yok'], 422);
    try {
        $pdo->beginTransaction();
        $model->updateSingle($id, $data);
        $pdo->commit();
        json(['status' => 'ok']);
    } catch (Throwable $e) {
        $pdo->rollBack();
        json(['status' => 'error', 'message' => 'İşlem başarısız'], 500);
    }
}

if ($method === 'DELETE') {
    Gate::can('acil_durum_kisi_sil');
    if (!Security::checkCsrfToken()) json(['status' => 'error', 'message' => 'CSRF doğrulaması başarısız'], 403);
    $id = $_GET['id'] ?? null;
    if (!$id) {
        $in = bodyJson();
        $id = $in['id'] ?? null;
    }
    if (!$id) json(['status' => 'error', 'message' => 'ID gerekli'], 422);
    $decId = is_numeric($id) ? (int)$id : (int)Security::decrypt($id);
    try {
        $pdo->beginTransaction();
        if ($model->hasColumn('silinme_tarihi')) {
            $model->softDelete($decId);
        } else {
            $model->delete(\App\Helper\Security::encrypt($decId));
        }
        $pdo->commit();
        json(['status' => 'ok']);
    } catch (Throwable $e) {
        $pdo->rollBack();
        json(['status' => 'error', 'message' => 'İşlem başarısız'], 500);
    }
}

json(['status' => 'error', 'message' => 'Method desteklenmiyor'], 405);