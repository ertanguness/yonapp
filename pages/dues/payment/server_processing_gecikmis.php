<?php
require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';

use App\Helper\Date;
use App\Helper\Helper;
use App\Helper\Security;
use App\Services\Gate;
use Model\FinansalRaporModel;

Security::checkLogin();
Gate::authorizeOrDie('yonetici_aidat_odeme');

$siteId = $_SESSION['site_id'] ?? null;
$request = $_GET;

$model = new FinansalRaporModel();
$records = $model->getGecikenBorclarGruplu((int)$siteId);

$rows = [];
foreach ($records as $borc) {
    $encId = Security::encrypt($borc->kisi_id);
    $badgeColor = ($borc->uyelik_tipi === 'Kiracı') ? 'warning' : 'teal';
    $oturumDurumColor = ($borc->durum === 'Aktif') ? 'success' : 'danger';
    $borc->adi_soyadi = $borc->adi_soyadi ?? '';
    $borc->uyelik_tipi = $borc->uyelik_tipi ?? '';
    $borc->durum = $borc->durum ?? '';
    $borc->daire_tipi = $borc->daire_tipi ?? '';

    $telefonHam = (string)($borc->telefon ?? '');
    $telefonTemiz = preg_replace('/\D/', '', $telefonHam);
    $adiHtml = '<div>'
        .'<a href="/site-sakini-duzenle/'.urlencode($encId).'">'.htmlspecialchars((string)($borc->adi_soyadi ?? '')).'</a>'
        .' '
        .'<div>'
        .'<a href="javascript:void(0)" class="badge text-'.$badgeColor.' border border-dashed border-gray-500">'.htmlspecialchars((string)($borc->uyelik_tipi ?? '')).'</a>'
        .'<a href="javascript:void(0)" class="badge text-'.$oturumDurumColor.' border border-dashed border-gray-500">'.htmlspecialchars($borc->durum).'</a>'
        .'<a href="javascript:void(0)" class="badge text-teal border border-dashed border-gray-500">'.htmlspecialchars($borc->daire_tipi).'</a>'
        .'</div>';

    $kalanAnapara = (float)($borc->kalan_anapara ?? 0);
    $gecikmeZammi = (float)($borc->hesaplanan_gecikme_zammi ?? 0);
    $toplamKalan = (float)($borc->toplam_kalan_borc ?? 0);
    $krediTutari = (float)($borc->kredi_tutari ?? 0);
    $netBorc = $krediTutari - $toplamKalan;
    $netColor = $netBorc < 0 ? 'danger' : 'success';

    $islemHtml = '<div class="hstack gap-2 ">'
        .'<a href="javascript:void(0);" data-id="'.$encId.'" class="avatar-text avatar-md kisi-borc-detay">'
        .'<i class="feather-eye"></i>'
        .'</a>';
    $islemHtml .= '</div>';

    $rows[] = [
        'daire_kodu' => htmlspecialchars($borc->daire_kodu ?? ''),
        'adi_soyadi_html' => $adiHtml,
        'giris_tarihi' => Date::dmY($borc->giris_tarihi ?? ''),
        'cikis_tarihi' => Date::dmY($borc->cikis_tarihi ?? ''),
        'kalan_anapara_formatted' => '<i class="feather-trending-down fw-bold text-danger"></i> '.Helper::formattedMoney($kalanAnapara),
        'hesaplanan_gecikme_zammi_formatted' => Helper::formattedMoney($gecikmeZammi),
        'toplam_kalan_borc_formatted' => Helper::formattedMoney($toplamKalan),
        'kredi_tutari_formatted' => Helper::formattedMoney($krediTutari),
        'net_borc_formatted' => '<span class="text-'.$netColor.'">'.Helper::formattedMoney($netBorc).'</span>',
        'islem_html' => $islemHtml,
        '_kalan_anapara' => $kalanAnapara,
        '_gecikme_zammi' => $gecikmeZammi,
        '_toplam_kalan' => $toplamKalan,
        '_kredi_tutari' => $krediTutari,
        '_net_borc' => $netBorc,
        '_adi_soyadi' => $borc->adi_soyadi ?? '',
    ];
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
            mb_strpos($normalize($r['daire_kodu']), $q) !== false
        );
    }));
}

$recordsFiltered = count($rows);

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
            3 => function($r){ return $r['giris_tarihi']; },
            4 => function($r){ return $r['cikis_tarihi']; },
            5 => function($r){ return $r['_kalan_anapara']; },
            6 => function($r){ return $r['_gecikme_zammi']; },
            7 => function($r){ return $r['_toplam_kalan']; },
            8 => function($r){ return $r['_kredi_tutari']; },
            9 => function($r){ return $r['_net_borc']; },
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

if (!empty($request['columns']) && is_array($request['columns'])) {
    $parseNumber = function($s){
        $s = trim((string)$s);
        if ($s === '') return null;
        $s = str_replace(['.', ' '], ['', ''], $s);
        $s = str_replace(',', '.', $s);
        return is_numeric($s) ? (float)$s : null;
    };
    $parseDate = function($s){
        $s = trim((string)$s);
        if ($s === '') return null;
        $dt = \DateTime::createFromFormat('d.m.Y', $s);
        return $dt ? $dt->getTimestamp() : null;
    };
    $applyString = function($target, $op, $val) use ($normalize){
        $t = $normalize($target);
        $q = $normalize($val);
        if ($op === 'none' || $q === '') return true;
        if ($op === 'starts') return mb_strpos($t, $q) === 0;
        if ($op === 'contains') return mb_strpos($t, $q) !== false;
        if ($op === 'not_contains') return mb_strpos($t, $q) === false;
        if ($op === 'ends') return $q === '' ? true : (mb_substr($t, -mb_strlen($q)) === $q);
        if ($op === 'equals') return $t === $q;
        if ($op === 'not_equals') return $t !== $q;
        return mb_strpos($t, $q) !== false;
    };
    $applyNumber = function($target, $op, $val) use ($parseNumber){
        $tv = (float)$target; $q = $parseNumber($val);
        if ($q === null) return true;
        if ($op === 'none') return true;
        if ($op === 'gt') return $tv > $q;
        if ($op === 'gte') return $tv >= $q;
        if ($op === 'lt') return $tv < $q;
        if ($op === 'lte') return $tv <= $q;
        if ($op === 'equals') return $tv == $q;
        if ($op === 'not_equals') return $tv != $q;
        return $tv == $q;
    };
    $applyDate = function($target, $op, $val) use ($parseDate){
        $tv = $parseDate($target); $q = $parseDate($val);
        if ($q === null || $tv === null) return true;
        if ($op === 'none') return true;
        if ($op === 'after') return $tv > $q;
        if ($op === 'before') return $tv < $q;
        if ($op === 'on') return $tv === $q;
        if ($op === 'not_on') return $tv !== $q;
        return $tv === $q;
    };

    foreach ($request['columns'] as $idx => $reqCol) {
        $raw = trim($reqCol['search']['value'] ?? '');
        if ($raw === '') continue;
        $decoded = json_decode($raw, true);
        $op = is_array($decoded) ? (string)($decoded['op'] ?? 'contains') : 'contains';
        $val = is_array($decoded) ? (string)($decoded['val'] ?? '') : $raw;
        $type = is_array($decoded) ? (string)($decoded['type'] ?? 'string') : 'string';

        if ($idx === 1) {
            $rows = array_values(array_filter($rows, function($r) use ($op, $val, $applyString) {
                return $applyString($r['daire_kodu'] ?? '', $op, $val);
            }));
        }
        else if ($idx === 2) {
            $rows = array_values(array_filter($rows, function($r) use ($op, $val, $applyString) {
                return $applyString($r['_adi_soyadi'] ?? '', $op, $val);
            }));
        }
        else if ($idx === 3) {
            $rows = array_values(array_filter($rows, function($r) use ($op, $val, $applyDate) {
                return $applyDate($r['giris_tarihi'] ?? '', $op, $val);
            }));
        }
        else if ($idx === 4) {
            $rows = array_values(array_filter($rows, function($r) use ($op, $val, $applyDate) {
                return $applyDate($r['cikis_tarihi'] ?? '', $op, $val);
            }));
        }
        else if ($idx === 5) {
            $rows = array_values(array_filter($rows, function($r) use ($op, $val, $applyNumber) {
                return $applyNumber($r['_kalan_anapara'] ?? 0, $op, $val);
            }));
        }
        else if ($idx === 6) {
            $rows = array_values(array_filter($rows, function($r) use ($op, $val, $applyNumber) {
                return $applyNumber($r['_gecikme_zammi'] ?? 0, $op, $val);
            }));
        }
        else if ($idx === 7) {
            $rows = array_values(array_filter($rows, function($r) use ($op, $val, $applyNumber) {
                return $applyNumber($r['_toplam_kalan'] ?? 0, $op, $val);
            }));
        }
        else if ($idx === 8) {
            $rows = array_values(array_filter($rows, function($r) use ($op, $val, $applyNumber) {
                return $applyNumber($r['_kredi_tutari'] ?? 0, $op, $val);
            }));
        }
        else if ($idx === 9) {
            $rows = array_values(array_filter($rows, function($r) use ($op, $val, $applyNumber) {
                return $applyNumber($r['_net_borc'] ?? 0, $op, $val);
            }));
        }
    }
}

$recordsFiltered = count($rows);

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
