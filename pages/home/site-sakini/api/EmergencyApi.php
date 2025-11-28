<?php
require_once dirname(__DIR__, 4) . '/configs/bootstrap.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

use App\Helper\Security;
use Model\AcilDurumKisileriModel;

$Model = new AcilDurumKisileriModel();
$db = getDbConnection();

function columnExists($db, $table, $column){
    $stmt = $db->prepare("SHOW COLUMNS FROM {$table} LIKE ?");
    $stmt->execute([$column]);
    return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
}

function ensureEmergencyColumns($db){
    try { if(!columnExists($db,'acil_durum_kisileri','notlar')){ $db->exec("ALTER TABLE acil_durum_kisileri ADD COLUMN notlar TEXT NULL"); } } catch(Throwable $e){}
}

$action = $_POST['action'] ?? '';

function normalizeId($raw)
{
    if (!$raw) return 0;
    if (is_numeric($raw)) return (int)$raw;
    $dec = Security::decrypt($raw);
    return (int)$dec;
}

if($action === 'save_em'){
    ensureEmergencyColumns($db);

    $id = normalizeId($_POST['id'] ?? 0);
    $isUpdate = $id > 0;
    $telefon = trim((string)($_POST['telefon'] ?? ''));
    if(!$telefon){ echo json_encode([ 'status' => 'error', 'message' => 'Telefon zorunludur' ]); exit; }
    if(!$isUpdate && $Model->AcilDurumKisiVarmi($telefon)){
        echo json_encode([ 'status' => 'error', 'message' => $telefon . ' numarası zaten kayıtlı' ]);
        exit;
    }

    $data = [
        'id' => $id,
        'kisi_id' => (int)($_POST['kisi_id'] ?? 0),
        'adi_soyadi' => trim((string)($_POST['adi_soyadi'] ?? '')),
        'telefon' => $telefon,
        'yakinlik' => trim((string)($_POST['yakinlik'] ?? '')),
        'notlar' => trim((string)($_POST['notlar'] ?? '')),
    ];

    $lastInsertId = $Model->saveWithAttr($data);
    if(!$lastInsertId && $isUpdate){ $lastInsertId = $id; }
    if(!$lastInsertId){ echo json_encode([ 'status' => 'error', 'message' => 'Kayıt başarısız' ]); exit; }

    echo json_encode([ 'status' => 'success', 'id' => $isUpdate ? $id : normalizeId($lastInsertId) ]);
    exit;
}

if($action === 'delete_em'){
    $id = normalizeId($_POST['id'] ?? 0);
    if($id <= 0){ echo json_encode(['status'=>'error','message'=>'Geçersiz ID']); exit; }
    $Model->delete(\App\Helper\Security::encrypt($id));
    echo json_encode([ 'status' => 'success' ]);
    exit;
}

if($action === 'get_em'){
    $id = normalizeId($_POST['id'] ?? 0);
    if($id <= 0){ echo json_encode(['status'=>'error','message'=>'Geçersiz ID']); exit; }
    $em = $Model->AcilDurumKisiBilgileri($id);
    echo json_encode(['status'=>'success','data'=>$em]);
    exit;
}

echo json_encode([ 'status' => 'error', 'message' => 'Geçersiz istek' ]);