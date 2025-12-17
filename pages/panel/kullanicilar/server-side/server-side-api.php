<?php
ob_start();
/**
 * DataTables server-side endpoint – Superadmin Kullanıcı Listesi
 *
 * DataTables’in standart POST parametrelerini (draw, start, length, search, order, columns)
 * alır ve JSON döndürür.
 */
require_once dirname(__DIR__, 4) . '/configs/bootstrap.php';

use App\Helper\Security;
use Model\UserModel;
use Database\Db;

header('Content-Type: application/json; charset=utf-8');

// DB bağlantısı (bootstrap içinde hazırlanıyor)
// Bu dosyada Model içindeki protected $db'ye erişemeyiz; global bağlantıyı kullanacağız.
$db = Db::getInstance()->connect();

if (!$db) {
    if (function_exists('ob_get_level')) {
        while (ob_get_level()) {
            ob_end_clean();
        }
    }
    http_response_code(500);
    echo json_encode(['error' => 'db_not_ready'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Yetki: sadece superadmin
if (!UserModel::isSuperAdmin()) {
    if (function_exists('ob_get_level')) {
        while (ob_get_level()) {
            ob_end_clean();
        }
    }
    http_response_code(403);
    echo json_encode(['error' => 'forbidden'], JSON_UNESCAPED_UNICODE);
    exit;
}

$User = new UserModel();

// DataTables standart parametreleri
$draw = intval($_POST['draw'] ?? 1);
$start = max(0, intval($_POST['start'] ?? 0));
$length = intval($_POST['length'] ?? 12);
if ($length <= 0) {
    $length = 12;
}
$searchValue = isset($_POST['search']['value']) ? trim((string) $_POST['search']['value']) : '';

$orderColIndex = intval($_POST['order'][0]['column'] ?? 0);
$orderDir = strtolower($_POST['order'][0]['dir'] ?? 'desc');
if (!in_array($orderDir, ['asc', 'desc'], true)) {
    $orderDir = 'desc';
}

// Liste sayfasındaki kolon sırasına göre güvenli kolon haritası
$map = [
    0 => 'u.id',
    1 => 'r.role_name',
    2 => 's.site_adi',
    3 => 'u.full_name',
    4 => 'u.email',
    5 => 'u.phone',
    6 => 'u.is_main_user',
    7 => 'u.status',
];
$orderColumn = $map[$orderColIndex] ?? 'u.id';

// Kolon bazlı filtreler (DataTables columns[n][search][value])
// Frontend `attachDtColumnSearch` JSON string yolluyor: {op, val, type}
$columnFilters = [];
if (isset($_POST['columns']) && is_array($_POST['columns'])) {
    $colMap = [
        0 => 'u.id',
        1 => 'r.role_name',
        2 => 's.site_adi',
        3 => 'u.full_name',
        4 => 'u.email',
        5 => 'u.phone',
        // 6: ana kullanıcı ikonu (aranabilir değil)
        // 7: durum (html döndürüyoruz, arama globalde var)
        // 8: işlem (aranabilir değil)
    ];

    foreach ($_POST['columns'] as $idx => $col) {
        if (!isset($colMap[$idx])) {
            continue;
        }
        $raw = isset($col['search']['value']) ? trim((string) $col['search']['value']) : '';
        if ($raw === '') {
            continue;
        }

        $op = 'contains';
        $val = $raw;
        $type = 'string';

        if (strlen($raw) > 1 && $raw[0] === '{') {
            $j = json_decode($raw, true);
            if (is_array($j)) {
                $op = isset($j['op']) ? (string) $j['op'] : $op;
                $val = isset($j['val']) ? (string) $j['val'] : '';
                $type = isset($j['type']) ? (string) $j['type'] : $type;
            }
        }

        $val = trim((string) $val);
        if ($val === '' || $op === 'none') {
            continue;
        }

        $columnFilters[] = [
            'col' => $colMap[$idx],
            'op' => $op,
            'val' => $val,
            'type' => $type,
        ];
    }
}

// Count total
$totalStmt = $db->prepare(
    "SELECT COUNT(*) AS c
     FROM users u
     LEFT JOIN user_roles r ON u.roles = r.id
     LEFT JOIN kisiler k ON u.kisi_id = k.id
     LEFT JOIN siteler s ON k.site_id = s.id
     WHERE u.silinme_tarihi IS NULL"
);
$totalStmt->execute();
$total = (int) (($totalStmt->fetch(PDO::FETCH_OBJ)->c) ?? 0);

$whereParts = [];
$params = [];

// Global arama
if ($searchValue !== '') {
    $whereParts[] = "(
        u.full_name LIKE :q
        OR u.email LIKE :q
        OR u.phone LIKE :q
        OR r.role_name LIKE :q
        OR s.site_adi LIKE :q
    )";
    $params['q'] = '%' . $searchValue . '%';
}

// Kolon bazlı arama
foreach ($columnFilters as $i => $f) {
    $p = 'c' . $i;
    $col = $f['col'];
    $op = $f['op'];
    $val = $f['val'];
    $type = $f['type'];

    // Basit güvenlik: sadece beklenen kolonlar kullanılıyor, op seti sınırlı
    if ($type === 'number') {
        $num = str_replace(['.', ' '], '', $val);
        $num = str_replace(',', '.', $num);
        $numVal = is_numeric($num) ? (float) $num : null;
        if ($numVal === null) {
            continue;
        }
        if ($op === 'gt') {
            $whereParts[] = "$col > :$p";
            $params[$p] = $numVal;
            continue;
        }
        if ($op === 'gte') {
            $whereParts[] = "$col >= :$p";
            $params[$p] = $numVal;
            continue;
        }
        if ($op === 'lt') {
            $whereParts[] = "$col < :$p";
            $params[$p] = $numVal;
            continue;
        }
        if ($op === 'lte') {
            $whereParts[] = "$col <= :$p";
            $params[$p] = $numVal;
            continue;
        }
        if ($op === 'not_equals') {
            $whereParts[] = "$col <> :$p";
            $params[$p] = $numVal;
            continue;
        }
        // equals / contains fallback
        if ($op === 'contains') {
            $whereParts[] = "$col LIKE :$p";
            $params[$p] = '%' . $numVal . '%';
        } else {
            $whereParts[] = "$col = :$p";
            $params[$p] = $numVal;
        }
        continue;
    }

    // string (default)
    $needle = $val;
    if ($op === 'starts') {
        $whereParts[] = "$col LIKE :$p";
        $params[$p] = $needle . '%';
        continue;
    }
    if ($op === 'ends') {
        $whereParts[] = "$col LIKE :$p";
        $params[$p] = '%' . $needle;
        continue;
    }
    if ($op === 'equals') {
        $whereParts[] = "$col = :$p";
        $params[$p] = $needle;
        continue;
    }
    if ($op === 'not_equals') {
        $whereParts[] = "$col <> :$p";
        $params[$p] = $needle;
        continue;
    }
    if ($op === 'not_contains') {
        $whereParts[] = "$col NOT LIKE :$p";
        $params[$p] = '%' . $needle . '%';
        continue;
    }
    // contains
    $whereParts[] = "$col LIKE :$p";
    $params[$p] = '%' . $needle . '%';
}

$where = '';
if (!empty($whereParts)) {
    $where = ' WHERE u.silinme_tarihi IS NULL AND ' . implode(' AND ', $whereParts);
}else{
    $where = ' WHERE u.silinme_tarihi IS NULL ';
}

// Count filtered
$filteredStmt = $db->prepare(
    "SELECT COUNT(*) AS c
     FROM users u
     LEFT JOIN user_roles r ON u.roles = r.id
     LEFT JOIN kisiler k ON u.kisi_id = k.id
     LEFT JOIN siteler s ON k.site_id = s.id
     $where"
);
$filteredStmt->execute($params);
$filtered = (int) (($filteredStmt->fetch(PDO::FETCH_OBJ)->c) ?? 0);

// Data
$sql =
    "SELECT u.*, r.role_name, s.site_adi
     FROM users u
     LEFT JOIN user_roles r ON u.roles = r.id
     LEFT JOIN kisiler k ON u.kisi_id = k.id
     LEFT JOIN siteler s ON k.site_id = s.id
     $where
     ORDER BY $orderColumn $orderDir
     LIMIT :start, :len";

$stmt = $db->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue(':' . $k, $v);
}
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':len', $length, PDO::PARAM_INT);
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_OBJ) ?? [];

$data = [];
$seq = $start + 1;
foreach ($items as $user) {
    $encId = Security::encrypt($user->id);
    $durumBadge = ((int) ($user->status ?? 0) === 1) ? 'success' : 'danger';

    $isMainHtml = ((int) ($user->is_main_user ?? 0) === 1)
        ? "<i class='ti ti-check text-success fs-24'></i>"
        : '';

    $statusHtml = "<span data-id=\"{$encId}\" data-status=\"{$user->status}\" class=\"badge text-$durumBadge border border-dashed border-gray-500 cursor-pointer durum-degistir\">" .
        (((int) ($user->status ?? 0) === 1) ? 'Aktif' : 'Pasif') .
        "</span>";

    // list.php içindeki onclick aynı kalsın diye raw id/status yolluyoruz
    $statusCell = "<div class=\"text-center cursor-pointer\" {$user->status})\">$statusHtml</div>";

    $editUrl = 'superadmin-kullanici-duzenle/' . $encId;

    $actions = '<div class="hstack gap-2">'
        . '<a href="' . $editUrl . '" class="avatar-text avatar-md"><i class="feather-edit"></i></a>';

    if ((int) ($user->is_main_user ?? 0) !== 1) {
        $actions .= '<a href="javascript:void(0);" class="avatar-text avatar-md kullanici-sil" data-id="' . htmlspecialchars($encId, ENT_QUOTES, 'UTF-8') . '">'
            . '<i class="feather-trash-2"></i></a>';
    }

    $actions .= '</div>';

    $data[] = [
        (string) $seq,
        htmlspecialchars($user->role_name ?? '', ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($user->site_adi ?? '', ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($user->full_name ?? '', ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($user->email ?? '', ENT_QUOTES, 'UTF-8'),
        htmlspecialchars($user->phone ?? '', ENT_QUOTES, 'UTF-8'),
        $isMainHtml,
        $statusCell,
        $actions,
    ];

    $seq++;
}

if (function_exists('ob_get_level')) {
    while (ob_get_level()) {
        ob_end_clean();
    }
}
echo json_encode([
    'draw' => $draw,
    'recordsTotal' => $total,
    'recordsFiltered' => (!empty($whereParts)) ? $filtered : $total,
    'data' => $data
], JSON_UNESCAPED_UNICODE);
