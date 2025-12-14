<?php 

require_once dirname(__DIR__, 4) . '/configs/bootstrap.php';

use App\Services\Gate;
use Model\DuyuruModel;

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if (!Gate::allows('sakin_duyuru_goruntule')) {
  http_response_code(403);
  echo json_encode(['status' => 'error', 'message' => 'Yetkisiz erişim']);
  exit;
}

if($method == "GET") {
    
    
}