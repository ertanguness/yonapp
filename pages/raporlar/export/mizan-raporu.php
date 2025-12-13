<?php
require_once dirname(__DIR__, 3) . '/configs/bootstrap.php';

use Model\SitelerModel;
use Model\KasaModel;
use Model\FinansalRaporModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Html;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

$site_id = $_SESSION['site_id'] ?? 0;
$format = strtolower($_GET['format'] ?? 'pdf');
$start = $_GET['start'] ?? date('Y-m-01');
$end   = $_GET['end'] ?? date('Y-m-t');
$isPreview = ($format === 'html');
$showPeople = ((string)($_GET['people'] ?? '1') === '1');
$maxPreviewRows = isset($_GET['limit']) ? max(200, (int)$_GET['limit']) : 1200;
$isPreview = ($format === 'html');
$maxPreviewRows = isset($_GET['limit']) ? max(200, (int)$_GET['limit']) : 1200;

$Siteler = new SitelerModel();
$site = $Siteler->find($site_id);
if (!$site) { die('Site bulunamadı'); }

@ini_set('memory_limit', '1024M');
@set_time_limit(180);

$db = \Database\Db::getInstance()->connect();
$KasaModel = new KasaModel();
$FinansModel = new FinansalRaporModel();

if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $start)) { $start .= ' 00:00:00'; }
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $end))   { $end   .= ' 23:59:59'; }

$kasalar = $KasaModel->SiteKasalari();

$sumDevir = 0.0; $sumGelen = 0.0; $sumGiden = 0.0; $sumBakiye = 0.0;

$ss = new Spreadsheet();
$sheet = $ss->getActiveSheet();
$logoPath = $site->logo_path ?? '';
$logoFile = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/logo/' . ($logoPath ?: 'default-logo.png');
if (!file_exists($logoFile)) {
    $logoFile = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/logo/default-logo.png';
}
$ext = strtolower(pathinfo($logoFile, PATHINFO_EXTENSION));
$imageCreated = null;
if ($ext === 'png') { $imageCreated = function_exists('imagecreatefrompng') ? @imagecreatefrompng($logoFile) : null; }
elseif ($ext === 'jpg' || $ext === 'jpeg') { $imageCreated = function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($logoFile) : null; }
elseif ($ext === 'gif') { $imageCreated = function_exists('imagecreatefromgif') ? @imagecreatefromgif($logoFile) : null; }
if ($imageCreated) {
    $md = new MemoryDrawing();
    $md->setName('Logo');
    $md->setDescription('Site Logo');
    $md->setImageResource($imageCreated);
    $md->setRenderingFunction(MemoryDrawing::RENDERING_PNG);
    $md->setMimeType(MemoryDrawing::MIMETYPE_DEFAULT);
    $md->setHeight(40);
    $md->setCoordinates('A1');
    $md->setOffsetX(2);
    $md->setOffsetY(2);
    $md->setWorksheet($sheet);
} else {
    $drawing = new Drawing();
    $drawing->setName('Logo');
    $drawing->setDescription('Site Logo');
    $drawing->setPath($logoFile);
    $drawing->setHeight(40);
    $drawing->setCoordinates('A1');
    $drawing->setOffsetX(2);
    $drawing->setOffsetY(2);
    $drawing->setWorksheet($sheet);
}
$ss->getDefaultStyle()->getFont()->setName('Arial');
$ss->getDefaultStyle()->getFont()->setSize(10);
$sheet->setTitle('Mizan Raporu');

$sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
$sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
$sheet->getPageSetup()->setFitToWidth(1);
$sheet->getPageSetup()->setFitToHeight(0);

$sheet->getPageMargins()->setTop(0.2);
$sheet->getPageMargins()->setRight(0.2);
$sheet->getPageMargins()->setLeft(0.2);
$sheet->getPageMargins()->setBottom(0.2);

$sheet->getColumnDimension('A')->setWidth(30);
$sheet->getColumnDimension('B')->setWidth(32);
$sheet->getColumnDimension('C')->setWidth(16);
$sheet->getColumnDimension('D')->setWidth(16);
$sheet->getColumnDimension('E')->setWidth(16);
$sheet->getColumnDimension('F')->setWidth(16);

$thin = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];

$sheet->mergeCells('A1:F1');
$sheet->setCellValue('A1', mb_strtoupper(($site->site_adi ?? 'Site') . ' – Mizan Raporu', 'UTF-8'));
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(15);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->mergeCells('A2:F2');
$sheet->setCellValue('A2', 'Dönem: ' . date('d.m.Y', strtotime($start)) . ' - ' . date('d.m.Y', strtotime($end)));
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->setCellValue('A4', 'Hesap Adı');
$sheet->setCellValue('B4', 'Açıklama');
$sheet->setCellValue('C4', 'Devir');
$sheet->setCellValue('D4', 'Gelen/Yatan');
$sheet->setCellValue('E4', 'Giden/Ödeme');
$sheet->setCellValue('F4', 'Bakiye');
$sheet->getStyle('A4:F4')->getFont()->setBold(true);
$sheet->getStyle('A4:F4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A4:F4')->applyFromArray($thin);
if (!$isPreview) { $sheet->getStyle('A4:F4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEFF5'); }
$sheet->getHeaderFooter()->setOddFooter('&C&B Sayfa &P / &N');
$sheet->getStyle('C5:F10000')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
if (!$isPreview) { $sheet->getStyle('A4:F4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEFF5'); }

$r = 5;

foreach ($kasalar as $k) {
    $sqlDevir = $db->prepare("SELECT SUM(tutar) AS toplam FROM kasa_hareketleri WHERE kasa_id = :kid AND site_id = :sid AND silinme_tarihi IS NULL AND islem_tarihi < :start AND kategori != 'Kasa Transferi'");
    $sqlDevir->bindValue(':kid', (int)$k->id, \PDO::PARAM_INT);
    $sqlDevir->bindValue(':sid', (int)$site_id, \PDO::PARAM_INT);
    $sqlDevir->bindValue(':start', $start, \PDO::PARAM_STR);
    $sqlDevir->execute();
    $devir = (float)($sqlDevir->fetchColumn() ?: 0);

    $sqlGelenTop = $db->prepare("SELECT SUM(kh.tutar) AS toplam 
                                 FROM kasa_hareketleri kh 
                                 LEFT JOIN kasa k ON k.id = kh.kasa_id 
                                 WHERE kh.kasa_id = :kid 
                                   AND k.site_id = :sid 
                                   AND kh.silinme_tarihi IS NULL 
                                   AND (kh.islem_tipi='Gelir' OR kh.islem_tipi='gelir') 
                                   AND kh.kategori != 'Kasa Transferi'
                                   AND kh.islem_tarihi BETWEEN :start AND :end");
    $sqlGelenTop->execute([':kid'=>(int)$k->id, ':sid'=>(int)$site_id, ':start'=>$start, ':end'=>$end]);
    $gelenTop = (float)($sqlGelenTop->fetchColumn() ?: 0);

    $sqlGidenTop = $db->prepare("SELECT SUM(ABS(kh.tutar)) AS toplam 
                                 FROM kasa_hareketleri kh 
                                 LEFT JOIN kasa k ON k.id = kh.kasa_id 
                                 WHERE kh.kasa_id = :kid 
                                   AND k.site_id = :sid 
                                   AND kh.silinme_tarihi IS NULL 
                                   AND (kh.islem_tipi='Gider' OR kh.islem_tipi='gider') 
                                   AND kh.kategori != 'Kasa Transferi'
                                   AND kh.islem_tarihi BETWEEN :start AND :end");
    $sqlGidenTop->execute([':kid'=>(int)$k->id, ':sid'=>(int)$site_id, ':start'=>$start, ':end'=>$end]);
    $gidenTop = (float)($sqlGidenTop->fetchColumn() ?: 0);

    $bakiye = $devir + $gelenTop - $gidenTop;

    $sumDevir += $devir; $sumGelen += $gelenTop; $sumGiden += $gidenTop; $sumBakiye += $bakiye;

    $sheet->setCellValue('A' . $r, (string)($k->kasa_adi ?? 'Kasa'));
    $sheet->setCellValue('B' . $r, 'Hareketler ve Toplam');
    $sheet->setCellValue('C' . $r, $devir);
    $sheet->setCellValue('D' . $r, $gelenTop);
    $sheet->setCellValue('E' . $r, $gidenTop);
    $sheet->setCellValue('F' . $r, $bakiye);
    $sheet->getStyle('A' . $r . ':F' . $r)->getFont()->setBold(true);
    $sheet->getStyle('A' . $r . ':F' . $r)->applyFromArray($thin);
    $r++;

    $sheet->setCellValue('A' . $r, '');
    $sheet->setCellValue('B' . $r, 'DEVİR');
    $sheet->setCellValue('C' . $r, $devir);
    $sheet->setCellValue('D' . $r, 0);
    $sheet->setCellValue('E' . $r, 0);
    $sheet->setCellValue('F' . $r, '');
    $r++;

    $sqlGelirKat = $db->prepare("SELECT COALESCE(kh.kategori,'Diğer Gelir') AS kategori, SUM(kh.tutar) AS toplam 
                                        FROM kasa_hareketleri kh
                                        LEFT JOIN kasa k on k.id =kh.kasa_id
                                        WHERE kasa_id=:kid AND k.site_id=:sid 
                                        AND kh.silinme_tarihi IS NULL 
                                        AND (kh.islem_tipi='Gelir' OR kh.islem_tipi = 'gelir') 
                                        AND kh.kategori != 'Kasa Transferi'
                                        AND kh.islem_tarihi BETWEEN :start AND :end 
                                        GROUP BY kh.kategori");
    $sqlGelirKat->execute([':kid'=>(int)$k->id, ':sid'=>(int)$site_id, ':start'=>$start, ':end'=>$end]);
    $gelirAgg = [];
    $normalize = function($s) {
        $x = mb_strtoupper(trim((string)$s), 'UTF-8');
        $x = preg_replace('/\s+/u', ' ', $x);
        if (preg_match('/A[Iİ]DAT/u', $x)) { $x = 'AİDAT'; }
        return $x;
    };
    foreach ($sqlGelirKat->fetchAll(\PDO::FETCH_OBJ) as $row) {
        $key = $normalize($row->kategori ?? 'Diğer Gelir');
        $gelirAgg[$key] = ($gelirAgg[$key] ?? 0) + (float)($row->toplam ?? 0);
    }
    foreach ($gelirAgg as $kat => $top) {
        $sheet->setCellValue('A' . $r, '');
        $sheet->setCellValue('B' . $r, $kat);
        $sheet->setCellValue('C' . $r, 0);
        $sheet->setCellValue('D' . $r, $top);
        $sheet->setCellValue('E' . $r, 0);
        $sheet->setCellValue('F' . $r, '');
        $r++;
    }

    $sqlGiderKat = $db->prepare("SELECT COALESCE(kh.kategori,'Ödeme') AS kategori, SUM(ABS(kh.tutar)) AS toplam 
                                 FROM kasa_hareketleri kh 
                                 LEFT JOIN kasa k ON k.id = kh.kasa_id 
                                 WHERE kh.kasa_id=:kid 
                                   AND k.site_id=:sid 
                                   AND kh.silinme_tarihi IS NULL 
                                   AND (kh.islem_tipi='Gider' OR kh.islem_tipi='gider') 
                                   AND kh.kategori != 'Kasa Transferi'
                                   AND kh.islem_tarihi BETWEEN :start AND :end 
                                 GROUP BY kh.kategori");
    $sqlGiderKat->execute([':kid'=>(int)$k->id, ':sid'=>(int)$site_id, ':start'=>$start, ':end'=>$end]);
    $giderAgg = [];
    $normalizeG = function($s) {
        $x = mb_strtoupper(trim((string)$s), 'UTF-8');
        $x = preg_replace('/\s+/u', ' ', $x);
        if (preg_match('/A[Iİ]DAT/u', $x)) { $x = 'AİDAT'; }
        return $x;
    };
    foreach ($sqlGiderKat->fetchAll(\PDO::FETCH_OBJ) as $row) {
        $key = $normalizeG($row->kategori ?? 'Ödeme');
        $giderAgg[$key] = ($giderAgg[$key] ?? 0) + (float)($row->toplam ?? 0);
    }
    foreach ($giderAgg as $kat => $top) {
        $sheet->setCellValue('A' . $r, '');
        $sheet->setCellValue('B' . $r, $kat);
        $sheet->setCellValue('C' . $r, 0);
        $sheet->setCellValue('D' . $r, 0);
        $sheet->setCellValue('E' . $r, $top);
        $sheet->setCellValue('F' . $r, '');
        $r++;
    }

    $sheet->getStyle('A' . ($r-1) . ':F' . ($r-1))->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
}

$headerRow = 5;
if ($r > $headerRow) {
    $sheet->insertNewRowBefore($headerRow, 1);
    $sheet->setCellValue('A' . $headerRow, 'KASA ve BANKA TOPLAM');
    $sheet->setCellValue('B' . $headerRow, 'DETAYLAR ve TOPLAMI');
    $sheet->setCellValue('C' . $headerRow, $sumDevir);
    $sheet->setCellValue('D' . $headerRow, $sumGelen);
    $sheet->setCellValue('E' . $headerRow, $sumGiden);
    $sheet->setCellValue('F' . $headerRow, $sumDevir + $sumGelen - $sumGiden);
    $sheet->getStyle('A' . $headerRow . ':F' . $headerRow)->getFont()->setBold(true);
    $sheet->getStyle('A' . $headerRow . ':F' . $headerRow)->applyFromArray($thin);
}

$sheet->getStyle('C5:F' . ($sheet->getHighestRow()))->getNumberFormat()->setFormatCode('#,##0.00');
$sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(4, 4);

// --- Daire Sakinleri Bölümü ---
try {
    if ($showPeople) {
        if ($format === 'pdf') {
            $stmtCount = $db->prepare("SELECT COUNT(*) FROM kisiler k LEFT JOIN daireler d ON k.daire_id = d.id WHERE d.site_id = :sid");
            $stmtCount->execute([':sid' => $site_id]);
            $totalPeople = (int)($stmtCount->fetchColumn() ?: 0);
            if ($totalPeople > 400) { $showPeople = false; }
        }
    $startDateOnly = substr($start, 0, 10);
    $endDateOnly   = substr($end, 0, 10);

    $openMap = [];
    $payMap = [];
    $accTotalMap = [];
    $accCatMap = [];

    // Önce FinModel yoluyla dene
    $openRowsTry = $FinansModel->getOpeningBreakdownByDate($startDateOnly);
    foreach ($openRowsTry as $o) {
        $openMap[(int)$o->kisi_id] = (float)($o->open_anapara ?? 0) + (float)($o->open_gecikme ?? 0) - (float)($o->open_odenen ?? 0);
    }
    $payRowsTry  = $FinansModel->getPaymentsByDateRange($startDateOnly, $endDateOnly);
    foreach ($payRowsTry as $p) { $payMap[(int)$p->kisi_id] = (float)($p->donem_odenen ?? 0); }
    $accRowsTry  = $FinansModel->getAccrualsBySiteBetween($site_id, $startDateOnly, $endDateOnly);
    foreach ($accRowsTry as $a) {
        $kid = (int)$a->kisi_id;
        $accTotalMap[$kid] = ($accTotalMap[$kid] ?? 0) + (float)($a->toplam_tahakkuk ?? 0);
        $kat = mb_strtoupper(trim((string)$a->kategori), 'UTF-8');
        $kat = preg_replace('/\s+/u', ' ', $kat);
        if (preg_match('/A[Iİ]DAT/u', $kat)) { $kat = 'AİDAT TAHAKKUK'; }
        $accCatMap[$kid][$kat] = ($accCatMap[$kid][$kat] ?? 0) + (float)($a->toplam_tahakkuk ?? 0);
    }

    $kisiIds = array_unique(array_merge(array_keys($openMap), array_keys($payMap), array_keys($accTotalMap)));
    if (empty($kisiIds)) {
        $qAccBefore = $db->prepare("SELECT kisi_id, SUM(COALESCE(tutar,0)) AS toplam FROM view_borclandirma_detay_raporu WHERE site_id = :sid AND baslangic_tarihi < :start GROUP BY kisi_id");
        $qAccBefore->execute([':sid'=>$site_id, ':start'=>$startDateOnly]);
        $beforeAccMap = [];
        foreach ($qAccBefore->fetchAll(\PDO::FETCH_OBJ) as $r0) { $beforeAccMap[(int)$r0->kisi_id] = (float)($r0->toplam ?? 0); }

        $qPayBefore = $db->prepare("SELECT kisi_id, SUM(ABS(tutar)) AS toplam FROM kasa_hareketleri WHERE site_id=:sid AND islem_tipi='Gelir' AND kisi_id IS NOT NULL AND islem_tarihi < :start GROUP BY kisi_id");
        $qPayBefore->execute([':sid'=>$site_id, ':start'=>$start]);
        $beforePayMap = [];
        foreach ($qPayBefore->fetchAll(\PDO::FETCH_OBJ) as $r1) { $beforePayMap[(int)$r1->kisi_id] = (float)($r1->toplam ?? 0); }
        foreach ($beforeAccMap as $kid => $val) { $openMap[$kid] = (float)$val - (float)($beforePayMap[$kid] ?? 0); }

        $qPayRange = $db->prepare("SELECT kisi_id, SUM(ABS(tutar)) AS toplam FROM kasa_hareketleri WHERE site_id=:sid AND islem_tipi='Gelir' AND kisi_id IS NOT NULL AND islem_tarihi BETWEEN :start AND :end GROUP BY kisi_id");
        $qPayRange->execute([':sid'=>$site_id, ':start'=>$start, ':end'=>$end]);
        foreach ($qPayRange->fetchAll(\PDO::FETCH_OBJ) as $r2) { $payMap[(int)$r2->kisi_id] = (float)($r2->toplam ?? 0); }

        $qAccRange = $db->prepare("SELECT kisi_id, COALESCE(borc_adi, aciklama, 'Diğer') AS kategori, SUM(COALESCE(tutar,0)) AS toplam FROM view_borclandirma_detay_raporu WHERE site_id=:sid AND baslangic_tarihi BETWEEN :start AND :end GROUP BY kisi_id, kategori");
        $qAccRange->execute([':sid'=>$site_id, ':start'=>$startDateOnly, ':end'=>$endDateOnly]);
        foreach ($qAccRange->fetchAll(\PDO::FETCH_OBJ) as $r3) {
            $kid = (int)$r3->kisi_id;
            $accTotalMap[$kid] = ($accTotalMap[$kid] ?? 0) + (float)($r3->toplam ?? 0);
            $kat = mb_strtoupper(trim((string)$r3->kategori), 'UTF-8');
            $kat = preg_replace('/\s+/u', ' ', $kat);
            if (preg_match('/A[Iİ]DAT/u', $kat)) { $kat = 'AİDAT TAHAKKUK'; }
            $accCatMap[$kid][$kat] = ($accCatMap[$kid][$kat] ?? 0) + (float)($r3->toplam ?? 0);
        }
        $kisiIds = array_unique(array_merge(array_keys($openMap), array_keys($payMap), array_keys($accTotalMap)));
    }

    // Kişi id listesi (FinansModel verilerinden)
    $kisiIds = array_unique(array_merge(array_keys($openMap), array_keys($payMap), array_keys($accTotalMap)));

    // İsimler (boş olsa da bölüm başlığını ekleyeceğiz)
    $nameMap = [];
    if (!empty($kisiIds)) {
        $placeholders = implode(',', array_fill(0, count($kisiIds), '?'));
        $stmtNames = $db->prepare("SELECT k.id, k.adi_soyadi, d.daire_kodu FROM kisiler k LEFT JOIN daireler d ON k.daire_id = d.id WHERE k.id IN ($placeholders)");
        $stmtNames->execute($kisiIds);
        foreach ($stmtNames->fetchAll(\PDO::FETCH_OBJ) as $nm) {
            $nameMap[(int)$nm->id] = trim(((string)($nm->daire_kodu ?? '') ? ($nm->daire_kodu . ' - ') : '') . (string)($nm->adi_soyadi ?? 'Kişi'));
        }
    }

    $stmtPeople = $db->prepare("SELECT k.id, k.adi_soyadi, d.daire_kodu, b.blok_adi
                                FROM kisiler k
                                LEFT JOIN daireler d ON k.daire_id = d.id
                                LEFT JOIN bloklar b ON d.blok_id = b.id
                                WHERE d.site_id = :sid
                                ORDER BY b.blok_adi ASC, d.daire_kodu ASC, k.adi_soyadi ASC");
    $stmtPeople->execute([':sid' => $site_id]);
    $people = $stmtPeople->fetchAll(\PDO::FETCH_OBJ);

    $startRow = $sheet->getHighestRow() + 2;
    $sheet->setCellValue('A' . $startRow, 'SAKİN DETAY ve TOPLAMLARI');
    $sheet->setCellValue('B' . $startRow, 'DETAYLAR ve TOPLAMI');
    $sheet->setCellValue('C' . $startRow, array_sum($openMap));
    $sheet->setCellValue('D' . $startRow, array_sum($payMap));
    $sheet->setCellValue('E' . $startRow, array_sum($accTotalMap));
    $sheet->setCellValue('F' . $startRow, array_sum($openMap) + array_sum($payMap) - array_sum($accTotalMap));
    $sheet->getStyle('A' . $startRow . ':F' . $startRow)->getFont()->setBold(true);
    $sheet->getStyle('A' . $startRow . ':F' . $startRow)->applyFromArray($thin);
    $sheet->getStyle('A' . $startRow . ':F' . $startRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF9E8');

    $r = $startRow + 1;
    $currBlock = null;
    $rendered = 0;
    foreach ($people as $pinfo) {
        if ($isPreview && $rendered >= $maxPreviewRows) { break; }
        $blockName = (string)($pinfo->blok_adi ?? '');
        if ($currBlock !== $blockName) {
            if ($format === 'pdf' || $format === 'xlsx') { $sheet->setBreak('A' . $r, \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW); }
            $currBlock = $blockName;
            $sheet->setCellValue('A' . $r, ($blockName !== '' ? ('Blok ' . $blockName) : 'Blok Bilgisi Yok'));
            $sheet->mergeCells('A' . $r . ':B' . $r);
            $sheet->getStyle('A' . $r . ':F' . $r)->getFont()->setBold(true);
            $sheet->getStyle('A' . $r . ':F' . $r)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('E8F5FF');
            $sheet->getStyle('A' . $r . ':F' . $r)->applyFromArray($thin);
            $r++;
        }
        $kid = (int)$pinfo->id;
        $devir = (float)($openMap[$kid] ?? 0);
        $tahsil = (float)($payMap[$kid] ?? 0);
        $tahakkuk = (float)($accTotalMap[$kid] ?? 0);
        $bakiye = $devir + $tahsil - $tahakkuk;

        $name = trim(((string)($pinfo->daire_kodu ?? '') ? ($pinfo->daire_kodu . ' - ') : '') . (string)($pinfo->adi_soyadi ?? 'Kişi'));
        $sheet->setCellValue('A' . $r, $name);
        $sheet->setCellValue('B' . $r, 'Hareketler ve Toplam');
        $sheet->setCellValue('C' . $r, $devir);
        $sheet->setCellValue('D' . $r, $tahsil);
        $sheet->setCellValue('E' . $r, $tahakkuk);
        $sheet->setCellValue('F' . $r, $bakiye);
    $sheet->getStyle('A' . $r . ':F' . $r)->getFont()->setBold(true);
    $sheet->getStyle('A' . $r . ':F' . $r)->applyFromArray($thin);
    if (!$isPreview && (($r % 2) === 0)) { $sheet->getStyle('A' . $r . ':F' . $r)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('F7F9FC'); }
        $r++;
        $rendered++;

        $sheet->setCellValue('A' . $r, '');
        $sheet->setCellValue('B' . $r, 'DEVİR');
        $sheet->setCellValue('C' . $r, $devir);
        $sheet->setCellValue('D' . $r, 0);
        $sheet->setCellValue('E' . $r, 0);
        $sheet->setCellValue('F' . $r, '');
        $r++;

        if (abs($tahsil) >= 0.0001) {
            $sheet->setCellValue('A' . $r, '');
            $sheet->setCellValue('B' . $r, 'TAHSİLAT');
            $sheet->setCellValue('C' . $r, 0);
            $sheet->setCellValue('D' . $r, $tahsil);
            $sheet->setCellValue('E' . $r, 0);
            $sheet->setCellValue('F' . $r, '');
            if (!$isPreview && (($r % 2) === 0)) { $sheet->getStyle('A' . $r . ':F' . $r)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('F7F9FC'); }
            $r++;
        }

        if (!empty($accCatMap[$kid])) {
            foreach ($accCatMap[$kid] as $kat => $val) {
                if (abs($val) < 0.0001) { continue; }
                $sheet->setCellValue('A' . $r, '');
                $sheet->setCellValue('B' . $r, (string)$kat);
                $sheet->setCellValue('C' . $r, 0);
                $sheet->setCellValue('D' . $r, 0);
                $sheet->setCellValue('E' . $r, (float)$val);
                $sheet->setCellValue('F' . $r, '');
                $r++;
            }
        }

        $sheet->setCellValue('A' . $r, '');
        $sheet->setCellValue('B' . $r, 'Daire Kişi Toplam');
        $sheet->setCellValue('C' . $r, $devir);
        $sheet->setCellValue('D' . $r, $tahsil);
        $sheet->setCellValue('E' . $r, $tahakkuk);
        $sheet->setCellValue('F' . $r, $bakiye);
        $sheet->getStyle('A' . $r . ':F' . $r)->getFont()->setBold(true);
        $sheet->getStyle('A' . $r . ':F' . $r)->applyFromArray($thin);
        $sheet->getStyle('A' . $r . ':F' . $r)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
        if (!$isPreview) { $sheet->getStyle('A' . $r . ':F' . $r)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF9E8'); }
        $r++;
    }

    $sheet->getStyle('C' . $startRow . ':F' . ($sheet->getHighestRow()))->getNumberFormat()->setFormatCode('#,##0.00');
    }
} catch (\Exception $e) {
    error_log('Daire sakinleri bölümü üretilemedi: ' . $e->getMessage());
}

// Tüm eklemelerden sonra yazdırma alanını güncelle
$sheet->getPageSetup()->setPrintArea('A1:F' . $sheet->getHighestRow());

$filename = (($site->site_adi ?? 'site')) . '_mizan_' . date('Y_m');
if (
    $format === 'pdf'
    && !in_array(strtolower((string)($_GET['force'] ?? '0')), ['1','pdf'], true)
    && $sheet->getHighestRow() > 50000
) {
    $format = 'xlsx';
}

try {
    switch ($format) {
        case 'xlsx':
        case 'excel':
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');
            if (ob_get_length()) { ob_end_clean(); }
            $writer = new Xlsx($ss);
            if (method_exists($writer, 'setPreCalculateFormulas')) { $writer->setPreCalculateFormulas(false); }
            if (method_exists($writer, 'setUseDiskCaching')) { $writer->setUseDiskCaching(true, sys_get_temp_dir()); }
            $writer->save('php://output');
            break;
        case 'html':
            header('Content-Type: text/html; charset=utf-8');
            if (ob_get_length()) { ob_end_clean(); }
            (new Html($ss))->save('php://output');
            break;
        case 'pdf':
        default:
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
            header('Cache-Control: max-age=0');
            IOFactory::registerWriter('Pdf', \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf::class);
            if (ob_get_length()) { ob_end_clean(); }
            $writer = IOFactory::createWriter($ss, 'Pdf');
            $writer->save('php://output');
            break;
    }
    exit;
} catch (\Exception $e) {
    die('Export hatası: ' . $e->getMessage());
}