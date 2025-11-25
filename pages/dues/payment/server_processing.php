<?php
require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';

use App\Helper\Date;
use App\Helper\Helper;
use App\Helper\Security;
use App\Services\Gate;
use Model\FinansalRaporModel;

$siteId = $_SESSION['site_id'] ?? null;

// DataTables istemci isteği
$request = $_GET;

$model = new FinansalRaporModel();
$records = $model->getGuncelBorclarGruplu((int)$siteId);

// Satırları DataTables için dönüştür
$rows = [];
foreach ($records as $borc) {
    $encId = Security::encrypt($borc->kisi_id);
    $badgeColor = ($borc->uyelik_tipi === 'Kiracı') ? 'warning' : 'teal';
    $oturumDurumColor = ($borc->durum === 'Aktif') ? 'success' : 'danger';
    $borc->adi_soyadi = $borc->adi_soyadi ?? '';
    $borc->uyelik_tipi = $borc->uyelik_tipi ?? '';
    $borc->durum = $borc->durum ?? '';
    $borc->daire_tipi = $borc->daire_tipi ?? '';

    $adiHtml = '<div><a href="/site-sakini-duzenle/'.urlencode($encId).'">'.htmlspecialchars((string)($borc->adi_soyadi ?? '')).'</a></div>'
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
    if (Gate::allows('tahsilat_ekle_sil')) {
        $islemHtml .= '<a href="javascript:void(0);" title="Tahsilat Gir" data-kisi-id="'.$encId.'" class="avatar-text avatar-md tahsilat-gir">'
            .'<i class="bi bi-credit-card-2-front"></i>'
            .'</a>';
    }
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
        // Sıralama için ham değerler
        '_kalan_anapara' => $kalanAnapara,
        '_gecikme_zammi' => $gecikmeZammi,
        '_toplam_kalan' => $toplamKalan,
        '_kredi_tutari' => $krediTutari,
        '_net_borc' => $netBorc,
        '_adi_soyadi' => $borc->adi_soyadi ?? '',
    ];
}

$recordsTotal = count($rows);

// Global arama (ad, daire kodu)
if (!empty($request['search']['value'])) {
    $q = mb_strtolower(trim($request['search']['value']));
    $rows = array_values(array_filter($rows, function($r) use ($q) {
        return (
            mb_strpos(mb_strtolower($r['_adi_soyadi']), $q) !== false ||
            mb_strpos(mb_strtolower($r['daire_kodu']), $q) !== false
        );
    }));
}

// İlk filtre sonrası
$recordsFiltered = count($rows);

// Sıralama
if (!empty($request['order'][0]['column'])) {
    $col = (int)$request['order'][0]['column'];
    $dir = ($request['order'][0]['dir'] ?? 'asc') === 'desc' ? -1 : 1;
    // Doğal (natural) sıralama için yardımcı fonksiyonlar
    $splitTokens = function(string $s){
        preg_match_all('/(\d+|[^\d]+)/u', $s, $m);
        return array_map(function($t){ return ctype_digit($t) ? (int)$t : mb_strtolower($t); }, $m[0] ?? []);
    };

    if ($col === 1) { // daire_kodu için doğal sıralama
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
                // string karşılaştırma
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

// Kolon bazlı arama
if (!empty($request['columns']) && is_array($request['columns'])) {
    foreach ($request['columns'] as $idx => $reqCol) {
        $val = trim($reqCol['search']['value'] ?? '');
        if ($val === '') continue;
        $q = mb_strtolower($val);
        if ($idx === 1) { // daire_kodu
            $rows = array_values(array_filter($rows, function($r) use ($q) {
                return mb_strpos(mb_strtolower($r['daire_kodu']), $q) !== false;
            }));
        }
        else if ($idx === 2) { // ad soyad
            $rows = array_values(array_filter($rows, function($r) use ($q) {
                return mb_strpos(mb_strtolower($r['_adi_soyadi']), $q) !== false;
            }));
        }
        else if ($idx === 3) { // giris_tarihi (formatted)
            $rows = array_values(array_filter($rows, function($r) use ($q) {
                return mb_strpos(mb_strtolower($r['giris_tarihi']), $q) !== false;
            }));
        }
        else if ($idx === 4) { // cikis_tarihi (formatted)
            $rows = array_values(array_filter($rows, function($r) use ($q) {
                return mb_strpos(mb_strtolower($r['cikis_tarihi']), $q) !== false;
            }));
        }
        // Diğer kolonlar için gerekirse filtre eklenebilir
    }
}

// Kolon bazlı filtrelerden sonra güncel filtre sayısını ayarla
$recordsFiltered = count($rows);

// Sayfalama
$start = isset($request['start']) ? (int)$request['start'] : 0;
$length = isset($request['length']) ? (int)$request['length'] : 10;
if ($length !== -1) {
    $rows = array_slice($rows, $start, $length);
}

// DataTables JSON
$response = [
    'draw' => isset($request['draw']) ? (int)$request['draw'] : 0,
    'recordsTotal' => $recordsTotal,
    'recordsFiltered' => $recordsFiltered,
    'data' => array_values($rows),
];

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response);