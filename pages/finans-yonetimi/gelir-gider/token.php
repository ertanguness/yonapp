<?php
require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';

use App\Helper\Security;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

try {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) { $data = []; }

    // Sadece beklenen anahtarları alalım (beyaz liste)
    $allowed = [
        'start','end','type',
        'startDate','endDate','incExpType',
        'q_date','q_islem','q_daire','q_hesap','q_tutar','q_bakiye','q_kategori','q_makbuz','q_aciklama'
    ];
    $filtered = [];
    foreach ($allowed as $k) {
        if (array_key_exists($k, $data) && $data[$k] !== '' && $data[$k] !== null) {
            $filtered[$k] = $data[$k];
        }
    }

    $json = json_encode($filtered, JSON_UNESCAPED_UNICODE);
    $token = Security::encrypt($json);

    echo json_encode(['ok' => true, 'token' => $token]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
