<?php
require_once dirname(__DIR__, 4) . '/configs/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$token = null;
if (isset($_SERVER['HTTP_AUTHORIZATION'])) { $token = trim(str_ireplace('Bearer', '', $_SERVER['HTTP_AUTHORIZATION'])); }
if (!$token && function_exists('apache_request_headers')) { $h = apache_request_headers(); if (isset($h['Authorization'])) { $token = trim(str_ireplace('Bearer', '', $h['Authorization'])); } }
function b64d($d){ $r = strtr($d,'-_','+/'); $p = strlen($r)%4; if($p){ $r .= str_repeat('=',4-$p);} return base64_decode($r); }
function jwt_verify($t){ if(!$t) return true; $parts = explode('.', $t); if(count($parts)!==3) return false; list($h,$p,$s)=$parts; $alg = json_decode(b64d($h),true)['alg']??'HS256'; if($alg!=='HS256') return false; $payload = json_decode(b64d($p),true); if(!$payload) return false; $secret = getenv('JWT_SECRET') ?: ($_ENV['JWT_SECRET'] ?? getenv('APP_KEY') ?? ''); if(!$secret) return false; $sig = hash_hmac('sha256', $h.'.'.$p, $secret, true); $sigEnc = rtrim(strtr(base64_encode($sig),'+/','-_'),'='); if(!hash_equals($sigEnc,$s)) return false; if(isset($payload['exp']) && time() >= intval($payload['exp'])) return false; return true; }
$hasBearer = $token && strlen($token) > 0;
if (!jwt_verify($token)) { http_response_code(401); echo json_encode(['status'=>'error','message'=>'Yetkisiz']); exit; }
$pdo = getDbConnection();
function ensureTableExists($pdo){
  $sql = "CREATE TABLE IF NOT EXISTS duyurular (
    id INT AUTO_INCREMENT PRIMARY KEY,
    baslik VARCHAR(255) NOT NULL,
    icerik TEXT NOT NULL,
    baslangic_tarihi DATE NULL,
    bitis_tarihi DATE NULL,
    durum ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    target_type VARCHAR(16) NULL,
    target_ids TEXT NULL,
    olusturulma_tarihi DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    silinme_tarihi DATETIME NULL,
    silen_kullanici INT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
  $pdo->exec($sql);
}
function ensureColumns($pdo){
  $cols = [];
  $st = $pdo->query("SHOW COLUMNS FROM duyurular");
  foreach(($st?$st->fetchAll(PDO::FETCH_ASSOC):[]) as $c){ $cols[$c['Field']] = true; }
  if(!isset($cols['target_type'])){ $pdo->exec("ALTER TABLE duyurular ADD COLUMN target_type VARCHAR(16) NULL AFTER durum"); }
  if(!isset($cols['target_ids'])){ $pdo->exec("ALTER TABLE duyurular ADD COLUMN target_ids TEXT NULL AFTER target_type"); }
}
function respond($s,$m,$e=[]){ echo json_encode(array_merge(['status'=>$s,'message'=>$m],$e)); exit; }
function readInput(){ $raw = file_get_contents('php://input'); $ct = $_SERVER['CONTENT_TYPE'] ?? ''; if (stripos($ct,'application/json')!==false) { $d = json_decode($raw,true); return is_array($d)?$d:[]; } parse_str($raw,$d); return $d; }
ensureTableExists($pdo);
ensureColumns($pdo);
$model = new \Model\DuyuruModel();
if ($method==='GET') {
  if (isset($_GET['list'])) {
    $list = $_GET['list'];
    if ($list === 'blocks') {
      $siteId = $_SESSION['site_id'] ?? null;
      $m = new \Model\BloklarModel();
      $rows = $siteId ? $m->SiteBloklari($siteId) : [];
      $data = array_map(function($r){ return [ 'id'=> (int)$r->id, 'text'=> ($r->blok_adi ?? 'Blok').' (#'.(int)$r->id.')' ]; }, $rows);
      respond('success','Bloklar', ['data'=>$data]);
    }
    if ($list === 'persons') {
      $siteId = $_SESSION['site_id'] ?? null;
      $q = trim($_GET['q'] ?? '');
      $m = new \Model\KisilerModel();
      $rows = $siteId ? $m->getAktifKisilerBySite($siteId) : [];
      if ($q !== '') { $rows = array_values(array_filter($rows, function($r) use ($q){ $t = ($r->adi_soyadi ?? '').' '.($r->daire_kodu ?? '').' '.($r->blok_adi ?? ''); return stripos($t, $q) !== false; })); }
      $data = array_map(function($r){ $lbl = ($r->adi_soyadi ?? 'Kişi').' - '.($r->blok_adi ?? '').' / '.($r->daire_kodu ?? ''); return [ 'id'=> (int)$r->id, 'text'=> $lbl ]; }, $rows);
      respond('success','Kişiler', ['data'=>$data]);
    }
  }
  if (isset($_GET['datatables'])) {
    $columns = [
      [ 'db'=>'id', 'dt'=>0 ],
      [ 'db'=>'baslik', 'dt'=>1 ],
      [ 'db'=>'icerik', 'dt'=>2 ],
      [ 'db'=>'baslangic_tarihi', 'dt'=>3 ],
      [ 'db'=>'bitis_tarihi', 'dt'=>4 ],
      [ 'db'=>'durum', 'dt'=>5 ],
      [ 'db'=>'id', 'dt'=>6 ],
    ];
    echo $model->serverProcessing($_GET, $columns);
    exit;
  }
  if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare('SELECT * FROM duyurular WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    respond($row? 'success':'error', $row? 'Kayıt bulundu':'Kayıt yok', ['data'=>$row]);
  } else {
    $stmt = $pdo->query('SELECT id, baslik, icerik, baslangic_tarihi, bitis_tarihi, durum, olusturulma_tarihi FROM duyurular ORDER BY id DESC');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    respond('success','Liste', ['data'=>$rows]);
  }
}
if ($method==='POST') {
  if(!$hasBearer){ \App\Services\Gate::can('announcements_admin_page'); }
  $d = $_POST;
  if (empty($d)) { $d = readInput(); }
  $title = trim($d['title'] ?? $d['baslik'] ?? '');
  $content = trim($d['content'] ?? $d['icerik'] ?? '');
  $start = $d['start_date'] ?? $d['baslangic_tarihi'] ?? null;
  $end = $d['end_date'] ?? $d['bitis_tarihi'] ?? null;
  $status = $d['status'] ?? $d['durum'] ?? 'draft';
  $targetType = $d['target_type'] ?? null;
  $blockId = $d['block_id'] ?? null;
  $kisiIds = $d['kisi_ids'] ?? $d['kisi_ids[]'] ?? null;
  if ($title==='') respond('error','Başlık zorunlu');
  if ($content==='') respond('error','İçerik zorunlu');
  if ($start && $end && strtotime($end) < strtotime($start)) respond('error','Bitiş tarihi başlangıçtan küçük olamaz');
  $targetIds = null;
  if ($targetType === 'block' && $blockId) { $targetIds = json_encode([ (int)$blockId ]); }
  if ($targetType === 'kisi') {
    if (is_string($kisiIds)) { $kisiIds = array_filter(array_map('intval', explode(',', $kisiIds))); }
    if (is_array($kisiIds)) { $targetIds = json_encode(array_map('intval', $kisiIds)); }
  }
  $stmt = $pdo->prepare('INSERT INTO duyurular (baslik, icerik, baslangic_tarihi, bitis_tarihi, durum, target_type, target_ids) VALUES (?,?,?,?,?,?,?)');
  $ok = $stmt->execute([$title, $content, $start?:null, $end?:null, $status, $targetType, $targetIds]);
  if (!$ok) respond('error','Kaydedilemedi');
  $id = $pdo->lastInsertId();
  respond('success','Kaydedildi', ['id'=>$id]);
}
if ($method==='PUT') {
  if(!$hasBearer){ \App\Services\Gate::can('announcements_admin_page'); }
  $d = readInput();
  $id = intval($d['id'] ?? 0);
  if ($id<=0) respond('error','Geçersiz ID');
  $fields = [];
  $params = [];
  foreach(['title'=>'baslik','content'=>'icerik','start_date'=>'baslangic_tarihi','end_date'=>'bitis_tarihi','status'=>'durum','target_type'=>'target_type','target_ids'=>'target_ids'] as $k=>$col){ if(isset($d[$k])||isset($d[$col])){ $v = $d[$k]??$d[$col]; if($k==='target_ids' && is_array($v)) { $v = json_encode(array_map('intval',$v)); } $fields[] = "$col = ?"; $params[] = $v; } }
  if (!$fields) respond('error','Güncellenecek alan yok');
  if ((isset($d['start_date'])||isset($d['baslangic_tarihi'])) && (isset($d['end_date'])||isset($d['bitis_tarihi']))) {
    $sv = $d['start_date'] ?? $d['baslangic_tarihi'];
    $ev = $d['end_date'] ?? $d['bitis_tarihi'];
    if ($sv && $ev && strtotime($ev) < strtotime($sv)) respond('error','Bitiş tarihi başlangıçtan küçük olamaz');
  }
  $params[] = $id;
  $sql = 'UPDATE duyurular SET '.implode(', ',$fields).' WHERE id = ?';
  $stmt = $pdo->prepare($sql);
  $ok = $stmt->execute($params);
  respond($ok? 'success':'error', $ok? 'Güncellendi':'Güncellenemedi');
}
if ($method==='DELETE') {
  if(!$hasBearer){ \App\Services\Gate::can('announcements_admin_page'); }
  $d = readInput();
  $id = intval($d['id'] ?? ($_GET['id'] ?? 0));
  if ($id<=0) respond('error','Geçersiz ID');
  $stmt = $pdo->prepare('DELETE FROM duyurular WHERE id = ?');
  $ok = $stmt->execute([$id]);
  respond($ok? 'success':'error', $ok? 'Silindi':'Silinemedi');
}
respond('error','Method desteklenmiyor');
