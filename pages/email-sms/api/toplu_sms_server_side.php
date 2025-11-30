<?php
require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';

use App\Helper\Date;
use Model\KisilerModel;

$siteId = $_SESSION['site_id'] ?? null;

$request = $_GET;

$rows = [];
if ($siteId) {
    $kisiModel = new KisilerModel();
    $kisiler = $kisiModel->SiteTumKisileri((int)$siteId);
    foreach ($kisiler as $kisi) {
        $adiSoyadi = (string)($kisi->adi_soyadi ?? '');
        $uyelikTipi = (string)($kisi->uyelik_tipi ?? '');
        $uyelikHtml = '<a href="javascript:void(0)" class="badge text-'.(mb_strtolower($uyelikTipi,'UTF-8')==='kiracı'?'warning':'teal').' border border-dashed border-gray-500">'.htmlspecialchars($uyelikTipi).'</a>';
        $rawCikis = (string)($kisi->cikis_tarihi ?? '');
        $cikisTarihi = ($rawCikis && $rawCikis !== '0000-00-00') ? Date::dmY($rawCikis) : '';
        $today = strtotime(date('Y-m-d'));
        $cikisTs = null;
        if ($rawCikis && $rawCikis !== '0000-00-00') {
            $dt = \DateTime::createFromFormat('Y-m-d', $rawCikis) ?: \DateTime::createFromFormat('d.m.Y', $rawCikis);
            $cikisTs = $dt ? $dt->getTimestamp() : strtotime($rawCikis);
        }
        // Kural: Çıkış tarihi DOLU ve BUGÜNDEN BÜYÜK ise Pasif; boş ise Aktif; diğer durumda Aktif
        if ($cikisTs !== null && $cikisTs > $today) {
            $durumText = 'Pasif';
        } else if ($cikisTs === null) {
            $durumText = 'Aktif';
        } else {
            $durumText = 'Aktif';
        }
        $durumHtml = '<a href="javascript:void(0)" class="badge text-'.($durumText==='Pasif'?'danger':'success').' border border-dashed border-gray-500">'.$durumText.'</a>';
        $telefonHam = (string)($kisi->telefon ?? '');
        $telefonTemiz = preg_replace('/\D/', '', $telefonHam);

        $cbId = 'checkBox_'.(int)$kisi->id;
        $secHtml = '<div class="item-checkbox ms-1">'
            .'<div class="custom-control custom-checkbox">'
            .'<input type="checkbox" class="custom-control-input checkbox sms-sec" id="'.$cbId.'" data-id="'.(int)$kisi->id.'" data-phone="'.htmlspecialchars($telefonTemiz).'">'
            .'<label class="custom-control-label" for="'.$cbId.'"></label>'
            .'</div>'
            .'</div>';
        $rows[] = [
            'sec' => $secHtml,
            'daire_kodu' => htmlspecialchars((string)($kisi->daire_kodu ?? '')),
            'adi_soyadi' => htmlspecialchars($adiSoyadi),
            'uyelik_tipi' => $uyelikHtml,
            'durum' => $durumHtml,
            'cikis_tarihi' => htmlspecialchars($cikisTarihi),
            'telefon' => htmlspecialchars($telefonHam),
            'telefon_clean' => $telefonTemiz,
            '_adi_soyadi' => $adiSoyadi,
            '_uyelik_tipi' => $uyelikTipi,
            '_durum' => $durumText,
            '_cikis' => $cikisTarihi,
            '_id' => (int)$kisi->id,
        ];
    }
}

$recordsTotal = count($rows);

$normalize = function($s){
    $t = mb_strtolower((string)$s, 'UTF-8');
    return preg_replace('/\x{0307}/u', '', $t);
};

if (!empty($request['search']['value'])) {
    $q = $normalize(trim($request['search']['value']));
    $rows = array_values(array_filter($rows, function($r) use ($q, $normalize) {
        return (
            mb_strpos($normalize($r['_adi_soyadi']), $q) !== false ||
            mb_strpos($normalize($r['daire_kodu']), $q) !== false ||
            mb_strpos($normalize($r['telefon']), $q) !== false
        );
    }));
}

if (!empty($request['columns']) && is_array($request['columns'])) {
    foreach ($request['columns'] as $idx => $reqCol) {
        $val = trim($reqCol['search']['value'] ?? '');
        if ($val === '') continue;
        $q = $normalize($val);
        if ($idx === 2) {
            $rows = array_values(array_filter($rows, function($r) use ($q, $normalize){
                return mb_strpos($normalize($r['_adi_soyadi']), $q) !== false;
            }));
        } else if ($idx === 3) {
            $rows = array_values(array_filter($rows, function($r) use ($q, $normalize){
                return mb_strpos($normalize($r['_uyelik_tipi']), $q) !== false;
            }));
        } else if ($idx === 4) {
            $rows = array_values(array_filter($rows, function($r) use ($q, $normalize){
                return mb_strpos($normalize($r['_durum']), $q) !== false;
            }));
        } else if ($idx === 5) {
            $rows = array_values(array_filter($rows, function($r) use ($q, $normalize){
                return mb_strpos($normalize($r['cikis_tarihi']), $q) !== false;
            }));
        } else if ($idx === 6) {
            $rows = array_values(array_filter($rows, function($r) use ($q, $normalize){
                return mb_strpos($normalize($r['telefon']), $q) !== false;
            }));
        }
    }
}

$recordsFiltered = count($rows);

if (!empty($_GET['fetch']) && $_GET['fetch'] === 'all_ids') {
    $items = array_map(function($r){
        return [ 'id' => (int)($r['_id'] ?? 0), 'phone' => (string)($r['telefon_clean'] ?? '') ];
    }, $rows);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['items' => $items, 'recordsFiltered' => $recordsFiltered]);
    exit;
}

if (!empty($request['order'][0]['column'])) {
    $col = (int)$request['order'][0]['column'];
    $dir = ($request['order'][0]['dir'] ?? 'asc') === 'desc' ? -1 : 1;
    $splitTokens = function(string $s){
        preg_match_all('/(\d+|[^\d]+)/u', $s, $m);
        return array_map(function($t){ return ctype_digit($t) ? (int)$t : mb_strtolower($t); }, $m[0] ?? []);
    };

    if ($col === 1) {
        usort($rows, function($a, $b) use ($dir, $splitTokens) {
            $ta = $splitTokens($a['daire_kodu'] ?? '');
            $tb = $splitTokens($b['daire_kodu'] ?? '');
            $len = max(count($ta), count($tb));
            for ($i=0; $i<$len; $i++) {
                $va = $ta[$i] ?? null; $vb = $tb[$i] ?? null;
                if ($va === $vb) continue;
                if ($va === null) return -1 * $dir;
                if ($vb === null) return 1 * $dir;
                if (is_int($va) && is_int($vb)) {
                    return ($va < $vb ? -1 : 1) * $dir;
                }
                $cmp = strcmp((string)$va, (string)$vb);
                if ($cmp !== 0) return ($cmp < 0 ? -1 : 1) * $dir;
            }
            return 0;
        });
    } else {
        $keyMap = [
            2 => function($r){ return $r['_adi_soyadi']; },
            3 => function($r){ return $r['_uyelik_tipi']; },
            4 => function($r){ return $r['_durum']; },
            5 => function($r){ return $r['_cikis']; },
            6 => function($r){ return $r['telefon']; },
        ];
        if (isset($keyMap[$col])) {
            $getter = $keyMap[$col];
            usort($rows, function($a, $b) use ($getter, $dir) {
                $va = $getter($a); $vb = $getter($b);
                if ($va == $vb) return 0;
                return ($va < $vb ? -1 : 1) * $dir;
            });
        }
    }
}

$start = isset($request['start']) ? (int)$request['start'] : 0;
$length = isset($request['length']) ? (int)$request['length'] : 10;
if ($length !== -1) {
    $rows = array_slice($rows, $start, $length);
}

$response = [
    'draw' => isset($request['draw']) ? (int)$request['draw'] : 0,
    'recordsTotal' => $recordsTotal,
    'recordsFiltered' => $recordsFiltered,
    'data' => array_values($rows),
];

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response);