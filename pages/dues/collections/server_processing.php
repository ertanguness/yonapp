<?php
require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';

use Model\SSPModel;
use Database\Db;
use App\Helper\Helper;
use App\Helper\Date;
use App\Helper\Security;

$pdo = Db::getInstance()->connect();
$siteId = $_SESSION['site_id'] ?? null;

// DataTables çoğu kurulumda parametreleri POST ile gönderir.
// Bu endpoint hem GET hem POST desteklesin diye tek bir request dizisi kullanıyoruz.
$request = $_REQUEST;

// Hızlı debug (gerekirse): ?dt_debug=1 ile gelen isteği ve filtreleri log'a basar
$dtDebug = !empty($request['dt_debug']);
if ($dtDebug) {
    error_log('[DT] dues/collections request: ' . json_encode($request, JSON_UNESCAPED_UNICODE));
}

// Bazı arayüzlerde search[value] bir string yerine obje gelebiliyor.
// Örn: {op:"contains", val:"m", type:"string"}
// SSPModel string beklediği için burada normalize ediyoruz.
if (isset($request['search']) && is_array($request['search']) && isset($request['search']['value']) && is_array($request['search']['value'])) {
    $sv = $request['search']['value'];
    if (isset($sv['val']) && is_scalar($sv['val'])) {
        $request['search']['value'] = (string)$sv['val'];
    } else {
        // fallback: boş yap
        $request['search']['value'] = '';
    }
}

$table = "tahsilatlar t 
LEFT JOIN kisiler kisi ON t.kisi_id = kisi.id 
LEFT JOIN daireler d ON kisi.daire_id = d.id 
LEFT JOIN bloklar bl ON d.blok_id = bl.id 
LEFT JOIN kasa kasa ON t.kasa_id = kasa.id 
LEFT JOIN (SELECT tahsilat_id, SUM(kullanilan_tutar) AS kullanilan_kredi FROM kisi_kredi_kullanimlari GROUP BY tahsilat_id) kkk ON t.id = kkk.tahsilat_id";

$primaryKey = 't.id';

$columns = [
    [ 'db' => 't.makbuz_no', 'dt' => 0 ],
    [ 'db' => 't.islem_tarihi', 'dt' => 1, 'formatter' => function($d){ return Date::dmy($d); } ],
    [ 'db' => '', 'dt' => 2, 'formatter' => function($row){
        $name = htmlspecialchars($row['kisi_adi_soyadi'] ?? ($row['kisi_adi_soyadi'] ?? ''));
        $apt = htmlspecialchars($row['d_daire_kodu'] ?? ($row['d_daire_kodu'] ?? ''));
        return '<div class="fw-bold">'.$name.'</div><small class="text-muted">'.$apt.'</small>';
    }],
    [ 'db' => '', 'dt' => 3, 'formatter' => function($row){
        $desc = htmlspecialchars($row['t_aciklama'] ?? 'Genel Tahsilat');
        $kasa = htmlspecialchars($row['kasa_kasa_adi'] ?? '');
        $encKasa = Security::encrypt($row['kasa_id'] ?? 0);
        return '<div>'.$desc.'</div><small class="text-muted"><a href="kasa-hareketleri/'.$encKasa.'"><i class="bi bi-safe me-1"></i>'.$kasa.'</a></small>';
    }],
    [ 'db' => '', 'dt' => 4, 'formatter' => function($row){
        $tutar = Helper::formattedMoney($row['t_tutar'] ?? 0);
        $kredi = (float)($row['kkk_kullanilan_kredi'] ?? 0);
        $extra = $kredi > 0 ? '<div>'.Helper::formattedMoney($kredi).'</div>' : '';
        return '<div class="text-end"><div class="fw-bold">'.$tutar.'</div>'.$extra.'</div>';
    }],
    [ 'db' => '', 'dt' => 5, 'formatter' => function($row){
        $encId = Security::encrypt($row['t_id'] ?? 0);
        return '<div class="text-center d-flex justify-content-center align-items-center gap-1">'
            .'<button class="avatar-text avatar-md tahsilat-detay-goster" data-id="'.$encId.'"><i class="feather-chevron-down"></i></button>'
            .'<a href="#" id="delete-tahsilat" data-id="'.$encId.'" class="avatar-text avatar-md"><i class="feather-trash-2"></i></a>'
            .'</div>';
    }],
    [ 'db' => 'kasa.id', 'dt' => 6 ],
    [ 'db' => 't.id', 'dt' => 7 ],
    [ 'db' => 't.aciklama', 'dt' => 8 ],
    [ 'db' => 't.tutar', 'dt' => 9 ],
    // Formatter'larda kullanılan alanlar (SQL SELECT'e dahil olması için)
    [ 'db' => 'kisi.adi_soyadi', 'dt' => 10 ],
    [ 'db' => 'd.daire_kodu', 'dt' => 11 ],
    [ 'db' => 'kasa.kasa_adi', 'dt' => 12 ],
    [ 'db' => 'kkk.kullanilan_kredi', 'dt' => 13 ],
];

$baseCond = 'bl.site_id = :site_id AND t.silinme_tarihi IS NULL AND t.tutar >= 0';
$bindings = [ ':site_id' => $siteId ];

// Sütun bazlı arama eşlemesi (istemci index → DB kolonları)
$colSearchMap = [
    0 => ['t.makbuz_no'],
    1 => ['t.islem_tarihi'],
    2 => ['kisi.adi_soyadi','d.daire_kodu'],
    3 => ['t.aciklama','kasa.kasa_adi'],
    4 => ['t.tutar','kkk.kullanilan_kredi'],
    // 5 Detay sütunu arama dışı
];

if (!empty($request['columns']) && is_array($request['columns'])) {
    $idx = 0;
    foreach ($request['columns'] as $c) {
        $rawVal = $c['search']['value'] ?? '';

        // Column search value bazen JSON string olarak gelebiliyor
        // Örn: "{\"op\":\"contains\",\"val\":\"m\",\"type\":\"string\"}"
        if (is_string($rawVal)) {
            $trimmed = trim($rawVal);
            if ($trimmed !== '' && ($trimmed[0] === '{' || $trimmed[0] === '[')) {
                $decoded = json_decode($trimmed, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && isset($decoded['val']) && is_scalar($decoded['val'])) {
                    $rawVal = (string)$decoded['val'];
                }
            }
        } elseif (is_array($rawVal) && isset($rawVal['val']) && is_scalar($rawVal['val'])) {
            $rawVal = (string)$rawVal['val'];
        }

        $val = trim((string)$rawVal);

        if ($dtDebug && $val !== '') {
            error_log("[DT] dues/collections colSearch idx=$idx val=" . $val);
        }
        if ($val !== '' && isset($colSearchMap[$idx])) {
            $orParts = [];
            foreach ($colSearchMap[$idx] as $col) {
                $param = ":s{$idx}_" . str_replace(['.'], ['_'], $col);
                $orParts[] = "$col LIKE $param";
                $bindings[$param] = "%$val%";
            }
            if (!empty($orParts)) {
                $baseCond .= ' AND (' . implode(' OR ', $orParts) . ')';
            }
        }
        $idx++;
    }
}

$whereAll = [
    'condition' => $baseCond,
    'bindings' => $bindings,
];

if ($dtDebug) {
    error_log('[DT] dues/collections whereAll.condition: ' . $whereAll['condition']);
    error_log('[DT] dues/collections whereAll.bindings: ' . json_encode($whereAll['bindings'], JSON_UNESCAPED_UNICODE));
}

echo json_encode(SSPModel::complex($request, $pdo, $table, $primaryKey, $columns, null, $whereAll));

