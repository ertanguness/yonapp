<?php
ob_start();
/**
 * DataTables server-side endpoint – Gelir/Gider Listesi
 *
 * Bu uç nokta DataTables’in POST ettiği standart parametreleri (draw, start, length, search, order, columns)
 * alır ve beklenen JSON yapısını döndürür.
 *
 * Özellikler:
 * - Global arama: tüm başlıca alanlarda LIKE araması
 * - Kolon bazlı arama: columns[n].search.value → ilgili DB alanına eşlenir
 * - Sıralama: sınırlı, güvenli kolon seti üzerinden
 * - Sayım: toplam ve filtreli kayıt sayısı
 *
 * Notlar:
 * - `$_SESSION['kasa_id']` zorunludur; mevcut kasaya göre filtrelenir
 * - Dönen `data` hücreleri HTML olarak oluşturulur (rozetler, formatlı tutar vb.)
 */
require_once dirname(__DIR__, levels: 3) . '/configs/bootstrap.php';

use App\Helper\Security;
use App\Helper\Date;
use App\Services\Gate;
use App\Helper\Helper;
use Model\KasaModel;
use Model\KasaHareketModel;

$KasaModel = new KasaModel();
$kasaHareketModel = new KasaHareketModel();
header('Content-Type: application/json; charset=utf-8');

// Oturumdan kasa kimliği
$kasa_id = $_SESSION['kasa_id'] ?? 0;
// DataTables standart parametreleri
$draw = intval($_POST['draw'] ?? 1);
$start = intval($_POST['start'] ?? 0);
$length = intval($_POST['length'] ?? 50);
$searchValue = isset($_POST['search']['value']) ? trim((string)$_POST['search']['value']) : '';
// Sadece üstteki global arama kutusu için searchValue kullan; 
// kolon bazlı aramalar ayrı filtreler olarak uygulanacak.
// Sıralama parametrelerini haritala
$orderColIndex = intval($_POST['order'][0]['column'] ?? 0);
$orderDir = strtolower($_POST['order'][0]['dir'] ?? 'desc');
$map = [
    0 => 'islem_tarihi',
    1 => 'islem_tipi',
    2 => 'daire_kodu',
    3 => 'adi_soyadi',
    4 => 'tutar',
    5 => 'yuruyen_bakiye',
    6 => 'kategori',
    7 => 'alt_tur',
    8 => 'makbuz_no',
    9 => 'aciklama',
    10 => 'islem_tarihi'
];
$orderColumn = $map[$orderColIndex] ?? 'islem_tarihi';

// Kolon bazlı filtreleri hazırla (columns[n].search.value)
$filters = [];
if (isset($_POST['columns']) && is_array($_POST['columns'])) {
    $mapCols = [
        0 => 'islem_tarihi',
        1 => 'islem_tipi',
        2 => 'daire_kodu',
        3 => 'adi_soyadi',
        4 => 'tutar',
        5 => 'yuruyen_bakiye',
        6 => 'kategori',
        7 => 'alt_tur',
        8 => 'makbuz_no',
        9 => 'aciklama'
    ];
    foreach ($_POST['columns'] as $idx => $col) {
        $raw = isset($col['search']['value']) ? trim((string)$col['search']['value']) : '';
        // UI tarafında filtre "yok" / JSON {op:"none"} gönderiliyorsa bunu filtre uygulanmasın olarak yorumla.
        // (Aksi halde hem sayım hem liste sorguları 0'a düşer.)
        if ($raw === '' || strcasecmp($raw, 'yok') === 0 || !isset($mapCols[$idx])) { continue; }
        if ($raw[0] === '{') {
            $j = json_decode($raw, true);
            if (is_array($j)) {
                $op = isset($j['op']) ? (string)$j['op'] : 'contains';
                if ($op === 'none') { continue; }
                $filters[$mapCols[$idx]] = [
                    'op' => $op,
                    'val' => isset($j['val']) ? (string)$j['val'] : '',
                    'type' => isset($j['type']) ? (string)$j['type'] : 'string'
                ];
                continue;
            }
        }
        $filters[$mapCols[$idx]] = $raw;
    }
}
// Sayım: toplam ve filtreli kayıt
$total = $kasaHareketModel->getKasaHareketleriCount($kasa_id, '');
$filtered = $kasaHareketModel->getKasaHareketleriCount($kasa_id, $searchValue, $filters);
// Kayıtları getir
$items = $kasaHareketModel->getKasaHareketleriPaginated($kasa_id, $start, $length, $searchValue, $orderColumn, $orderDir, $filters);

$data = [];
foreach ($items as $hareket) {
    // Hücre içeriklerini render et (HTML)
    $enc_id = Security::encrypt($hareket->id);
    $tarih = Date::dmYHIS($hareket->islem_tarihi);
    $islemTipiHtml = (strtolower($hareket->islem_tipi) === 'gelir')
        ? '<span class="badge bg-success">Gelir</span>'
        : '<span class="badge bg-danger">Gider</span>';
    $daireKodu = $hareket->daire_kodu ? htmlspecialchars($hareket->daire_kodu) : '-';
    $hesapAdi = $hareket->adi_soyadi ? htmlspecialchars($hareket->adi_soyadi) : '-';
    $tutarHtml = (strtolower($hareket->islem_tipi) === 'gelir')
        ? '<span class="text-success fw-bold">+' . Helper::formattedMoney($hareket->tutar) . '</span>'
        : '<span class="text-danger">' . Helper::formattedMoney($hareket->tutar) . '</span>';
    $bakiyeHtml = ($hareket->yuruyen_bakiye ?? 0) >= 0
        ? '<span class="text-success fw-bold">' . Helper::formattedMoney($hareket->yuruyen_bakiye ?? 0) . '</span>'
        : '<span class="text-danger fw-bold">' . Helper::formattedMoney($hareket->yuruyen_bakiye ?? 0) . '</span>';
    $kategori = htmlspecialchars($hareket->kategori ?? '-');
    $altTur = htmlspecialchars((string)($hareket->alt_tur ?? ''));
    $makbuzNo = htmlspecialchars($hareket->makbuz_no ?? '-');
    $aciklama = htmlspecialchars($hareket->aciklama ?? '-');
    $gelirGiderGuncelle = ($hareket->guncellenebilir == 1) ? 'gelirGiderGuncelle' : 'GuncellemeYetkisiYok';
    $gelirGiderSil = ($hareket->guncellenebilir == 1) ? 'gelirGiderSil' : 'SilmeYetkisiYok';
    $buttons = '<div class="hstack gap-2 justify-content-center">'
        . '<a href="javascript:void(0)" class="avatar-text avatar-md ' . $gelirGiderGuncelle . '" data-id="' . $enc_id . '"><i class="feather-edit"></i></a>'
        . '<a href="javascript:void(0)" class="avatar-text avatar-md ' . $gelirGiderSil . '" data-id="' . $enc_id . '"><i class="feather-trash-2"></i></a>'
        . '</div>';

    $data[] = [
        $tarih,
        $islemTipiHtml,
        $daireKodu,
        $hesapAdi,
        $tutarHtml,
        $bakiyeHtml,
        $kategori,
        $altTur,
        $makbuzNo,
        '<span style="display:inline-block;max-width:200px;white-space:wrap;">' . $aciklama . '</span>',
        $buttons
    ];
}

// DataTables beklenen JSON çıktısı
if (function_exists('ob_get_level')) { while (ob_get_level()) { ob_end_clean(); } }
echo json_encode([
    'draw' => $draw,
    'recordsTotal' => $total,
    'recordsFiltered' => $filtered,
    'data' => $data
], JSON_UNESCAPED_UNICODE);
