<?php
require_once dirname(__DIR__, 4) . '/configs/bootstrap.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use App\Helper\Security;
use Model\AraclarModel;
use Database\Db;

$Araclar = new AraclarModel();
$db = Db::getInstance();

$action = $_POST['action'] ?? '';

function normalizeId($raw)
{
    if (!$raw) return 0;
    if (is_numeric($raw)) return (int)$raw;
    $dec = Security::decrypt($raw);
    return (int)$dec;
}

if ($action === 'save_car') {

    $id = normalizeId($_POST['id'] ?? 0);
    $isUpdate = $id > 0;

    $plaka = trim((string)($_POST['plaka'] ?? ''));
    if (!$plaka) {
        echo json_encode(['status' => 'error', 'message' => 'Plaka zorunludur']);
        exit;
    }

    if (!$isUpdate && $Araclar->AracVarmi($plaka)) {
        echo json_encode(['status' => 'error', 'message' => $plaka . ' plakası zaten kayıtlı']);
        exit;
    }

    $data = [
        'id' => $id,
        'kisi_id' => (int)($_POST['kisi_id'] ?? 0),
        'plaka' => $plaka,
        'marka_model' => trim((string)($_POST['marka_model'] ?? '')),
        'renk' => trim((string)($_POST['renk'] ?? '')),
        'arac_tipi' => trim((string)($_POST['arac_tipi'] ?? '')),
    ];

    $lastInsertId = $Araclar->saveWithAttr($data);
    if (!$lastInsertId && $isUpdate) {
        $lastInsertId = $id;
    }
    if (!$lastInsertId) {
        echo json_encode(['status' => 'error', 'message' => 'Araç kaydedilemedi']);
        exit;
    }

    echo json_encode(['status' => 'success', 'id' => $isUpdate ? $id : normalizeId($lastInsertId)]);
    exit;
}

if ($action === 'delete_car') {
    $id = normalizeId($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz ID']);
        exit;
    }
    $Araclar->delete(\App\Helper\Security::encrypt($id));
    echo json_encode(['status' => 'success']);
    exit;
}

if ($action === 'get_car') {
    $id = normalizeId($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Geçersiz ID']);
        exit;
    }
    $car = $Araclar->AracBilgileri($id);
    echo json_encode(['status' => 'success', 'data' => $car]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Geçersiz istek']);
