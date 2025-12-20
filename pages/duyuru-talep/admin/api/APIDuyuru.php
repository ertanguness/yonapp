<?php

use App\Helper\Security;
require_once dirname(__DIR__, 4) . '/configs/bootstrap.php';
//header('Content-Type: application/json; charset=utf-8');

use App\Services\Gate;
use Model\DuyuruModel;
use Model\KisilerModel;
use Model\BloklarModel;
use App\Helper\Date;

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if (!Gate::allows('announcements_admin_page')) {
  http_response_code(403);
  echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim']);
  exit;
}

function respond($s, $m, $e = [])
{
  echo json_encode(array_merge(['status' => $s, 'message' => $m], $e));
  exit;
}

function readInput()
{
  $raw = file_get_contents('php://input');
  $ct = $_SERVER['CONTENT_TYPE'] ?? '';
  if (stripos($ct, 'application/json') !== false) {
    $d = json_decode($raw, true);
    return is_array($d) ? $d : [];
  }
  parse_str($raw, $d);
  return $d;
}

$model = new DuyuruModel();

if ($method === 'GET') {
  if (isset($_GET['list'])) {
    $list = $_GET['list'];
    if ($list === 'blocks') {
      $siteId = $_SESSION['site_id'] ?? null;
      $m = new BloklarModel();
      $rows = $siteId ? $m->SiteBloklari($siteId) : [];
      $data = array_map(function ($r) {
        return ['id' => (int) $r->id, 'text' => ($r->blok_adi ?? 'Blok') . ' (#' . (int) $r->id . ')'];
      }, $rows);
      respond('success', 'Bloklar', ['data' => $data]);
    }
    if ($list === 'persons') {
      $siteId = $_SESSION['site_id'] ?? null;
      $q = trim($_GET['q'] ?? '');
      $m = new KisilerModel();
      $rows = $siteId ? $m->getAktifKisilerBySite($siteId) : [];
      if ($q !== '') {
        $rows = array_values(array_filter($rows, function ($r) use ($q) {
          $t = ($r->adi_soyadi ?? '') . ' ' . ($r->daire_kodu ?? '') . ' ' . ($r->blok_adi ?? '');
          return stripos($t, $q) !== false;
        }));
      }
      $data = array_map(function ($r) {
        $lbl = ($r->adi_soyadi ?? 'Kişi') . ' - ' . ($r->blok_adi ?? '') . ' / ' . ($r->daire_kodu ?? '');
        return ['id' => (int) $r->id, 'text' => $lbl];
      }, $rows);
      respond('success', 'Kişiler', ['data' => $data]);
    }
  }
  if (isset($_GET['datatables'])) {
    $columns = [
      ['db' => 'id', 'dt' => 0],
      ['db' => 'baslik', 'dt' => 1],
      ['db' => 'icerik', 'dt' => 2],
      ['db' => 'baslangic_tarihi', 'dt' => 3],
      ['db' => 'bitis_tarihi', 'dt' => 4],
      ['db' => 'durum', 'dt' => 5],
      ['db' => 'id', 'dt' => 6],
    ];
    echo $model->serverProcessing($_GET, $columns);
    exit;
  }
  if (isset($_GET['id'])) {
    $id = intval(Security::decrypt($_GET['id']));
    $row = $model->find($id);
    respond($row ? 'success' : 'error', $row ? 'Kayıt bulundu' : 'Kayıt yok', ['data' => $row]);
  } else {
    $rows = $model->all();
    respond('success', 'Liste', ['data' => $rows]);
  }
}

// POST veya PUT için kaydetme işlemi (saveWithAttr ile)
if ($method === 'POST' || $method === 'PUT') {
  $d = $_POST;
  if (empty($d)) {
    $d = readInput();
  }

  // ID'yi al ve decrypt et
  $id = 0;
  if (isset($d['id']) && !empty($d['id'])) {
    $decryptedId = Security::decrypt($d['id']);
    $id = intval($decryptedId);
  }

  $title          = trim($d['title'] ?? $d['baslik'] ?? '');
  $content        = trim($d['content'] ?? $d['icerik'] ?? '');
  $start          = Date::Ymd($d['start_date'] ?? $d['baslangic_tarihi'] ?? null);
  $end            = Date::Ymd($d['end_date'] ?? $d['bitis_tarihi'] ?? null);
  $status         = $d['status'] ?? $d['durum'] ?? 'draft';
  $targetType     = $d['target_type'] ?? null;
  $blockId        = $d['block_id'] ?? null;
  $kisiIds        = $d['kisi_ids'] ?? $d['kisi_ids[]'] ?? null;

  if ($title === '')
    respond('error', 'Başlık zorunlu');
  if ($content === '')
    respond('error', 'İçerik zorunlu');
  if ($start && $end && strtotime($end) < strtotime($start))
    respond('error', 'Bitiş tarihi başlangıçtan küçük olamaz');

  $targetIds = null;
  if ($targetType === 'block' && $blockId) {
    $targetIds = json_encode([(int) $blockId]);
  }


  if ($targetType === 'kisi') {
    $decryptedKisiIds = [];
    if (is_string($kisiIds)) {
      $kisiIds = array_filter(explode(',', $kisiIds));
    }
    if (is_array($kisiIds)) {
      foreach ($kisiIds as $kisiId) {
        $decryptedKisiIds[] = intval(Security::decrypt($kisiId));
      }
      $targetIds = json_encode($decryptedKisiIds);
    }
  }

  // Model ile kaydet
  $data = [
    'id'                => $id,
    'site_id'           => $_SESSION['site_id'] ?? 0,
    'baslik'            => $title,
    'icerik'            => $content,
    'baslangic_tarihi'  => $start ?: null,
    'bitis_tarihi'      => $end ?: null,
    'durum'             => $status,
    'target_type'       => $targetType,
    'target_ids'        => $targetIds
  ];

  try {
    $result = $model->saveWithAttr($data);
    if ($id > 0) {
      respond('success', 'Güncellendi');
    } else {
      respond('success', 'Kaydedildi', ['id' => $result]);
    }
  } catch (\Exception $e) {
    respond('error', 'İşlem başarısız: ' . $e->getMessage());
  }
}

if ($method === 'DELETE') {
  $d = readInput();
  $encryptedId = $d['id'] ?? $_GET['id'] ?? null;

  if (!$encryptedId) {
    respond('error', 'Geçersiz ID');
  }

  try {
    $result = $model->delete($encryptedId);
    if ($result === true) {
      respond('success', 'Silindi');
    } else {
      respond('error', 'Silinemedi');
    }
  } catch (\Exception $e) {
    respond('error', 'Silme hatası: ' . $e->getMessage());
  }
}

respond('error', 'Method desteklenmiyor');