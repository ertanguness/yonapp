<?php

require_once dirname(__DIR__, 2) . '/configs/bootstrap.php';

use App\Helper\Security;
use App\Helper\Helper;
use Model\IsletmeProjesiModel;
use Model\SitelerModel;
use Dompdf\Dompdf;

$enc_id = $id ?? ($_GET['id'] ?? 0);
$id = Security::decrypt($enc_id ?? 0) ?? 0;
if (!$id && is_numeric($enc_id)) {
    $id = (int)$enc_id;
}

$Model = new IsletmeProjesiModel();
$Sites = new SitelerModel();
$siteId = $_SESSION['site_id'] ?? 0;
$site = $Sites->SiteBilgileri($siteId);

// Logo hazırlığı (base64 data-uri)
$logoPath = $site->logo_path ?? '';
$logoFile = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/logo/' . $logoPath;
if (!file_exists($logoFile) || empty($logoPath)) {
    $logoFile = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/logo/default-logo.png';
}
$logoMime = 'image/png';
$ext = strtolower(pathinfo($logoFile, PATHINFO_EXTENSION));
if ($ext === 'jpg' || $ext === 'jpeg') { $logoMime = 'image/jpeg'; }
elseif ($ext === 'gif') { $logoMime = 'image/gif'; }
$logoData = @file_get_contents($logoFile);
$logoBase64 = $logoData ? 'data:' . $logoMime . ';base64,' . base64_encode($logoData) : '';
$summary = $Model->getProjectSummary($id);
$gelirKalemleri = $Model->getKalemleri($id, 'gelir');
$giderKalemleri = $Model->getKalemleri($id, 'gider');
$paylasim = $Model->getPaylasimWithDetails($id);

if (!$summary || !$summary->proje) {
    die('Proje bulunamadı');
}

$html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
    h1 { font-size: 18px; margin: 0 0 8px 0; }
    h2 { font-size: 14px; margin: 8px 0; }
    p { margin: 4px 0; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #ccc; padding: 6px; }
    th { background: #f0f0f0; }
    .right { text-align: right; }
    .section { margin-top: 12px; }
    .muted { color: #666; }
    .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .header { position: relative; }
    .logo { position: absolute; right: 0; top: 0; width: 100px; height: auto; }
    </style></head><body>';

$html .= '<div class="header">';
if ($logoBase64) { $html .= '<img class="logo" src="' . $logoBase64 . '" alt="Logo">'; }
$html .= '<h1>' . htmlspecialchars($summary->proje->proje_adi) . '</h1>';
$html .= '<p class="muted">Dönem: ' . date('d.m.Y', strtotime($summary->proje->donem_baslangic)) . ' - ' . date('d.m.Y', strtotime($summary->proje->donem_bitis)) . '</p>';
$html .= '</div>';
$html .= '<div class="grid">
    <div><strong>Toplam Gelir:</strong> ' . Helper::formattedMoney($summary->toplam_gelir) . '</div>
    <div><strong>Toplam Gider:</strong> ' . Helper::formattedMoney($summary->toplam_gider) . '</div>
    <div><strong>Net Yıllık Gider:</strong> ' . Helper::formattedMoney($summary->net_yillik_gider) . '</div>
    <div><strong>Aylık Avans Toplam:</strong> ' . Helper::formattedMoney($summary->aylik_avans_toplam) . '</div>
</div>';

$html .= '<div class="section"><h2>Gelir Kalemleri</h2><table><thead><tr><th>Kategori</th><th class="right">Tutar</th></tr></thead><tbody>';
foreach ($gelirKalemleri as $k) {
    $html .= '<tr><td>' . htmlspecialchars($k->kategori) . '</td><td class="right">' . Helper::formattedMoney($k->tutar) . '</td></tr>';
}
$html .= '</tbody></table></div>';

$html .= '<div class="section"><h2>Gider Kalemleri</h2><table><thead><tr><th>Kategori</th><th class="right">Tutar</th></tr></thead><tbody>';
foreach ($giderKalemleri as $k) {
    $html .= '<tr><td>' . htmlspecialchars($k->kategori) . '</td><td class="right">' . Helper::formattedMoney($k->tutar) . '</td></tr>';
}
$html .= '</tbody></table></div>';

$html .= '<div class="section"><h2>Kanuni Dayanak</h2><p>' . nl2br(htmlspecialchars($summary->proje->kanuni_dayanak ?? '')) . '</p></div>';
$html .= '<div class="section"><h2>Varsayımlar ve Metodoloji</h2><p>' . nl2br(htmlspecialchars($summary->proje->varsayimlar ?? '')) . '</p><p>' . nl2br(htmlspecialchars($summary->proje->metodoloji ?? '')) . '</p></div>';
$html .= '<div class="section"><h2>Ödeme Planı ve Takvimi</h2><p>' . nl2br(htmlspecialchars($summary->proje->odeme_plani ?? '')) . '</p><p>' . nl2br(htmlspecialchars($summary->proje->takvim ?? '')) . '</p></div>';
$html .= '<div class="section"><h2>KMK 37 Kapsamı</h2><table><tbody>';
$html .= '<tr><td>Genel Kurul Türü</td><td>' . htmlspecialchars($summary->proje->genel_kurul_turu ?? '') . '</td></tr>';
$html .= '<tr><td>Genel Kurul Tarihi</td><td>' . (isset($summary->proje->genel_kurul_tarihi)?date('d.m.Y', strtotime($summary->proje->genel_kurul_tarihi)):'') . '</td></tr>';
$html .= '<tr><td>Kurul Onayı</td><td>' . htmlspecialchars($summary->proje->kurul_onay_durumu ?? '') . '</td></tr>';
$html .= '<tr><td>Onay Tarihi</td><td>' . (isset($summary->proje->kurul_onay_tarihi)?date('d.m.Y', strtotime($summary->proje->kurul_onay_tarihi)):'') . '</td></tr>';
$html .= '<tr><td>Divan Tutanak No</td><td>' . htmlspecialchars($summary->proje->divan_tutanak_no ?? '') . '</td></tr>';
$html .= '<tr><td>Bildirim Yöntemi</td><td>' . htmlspecialchars($summary->proje->bildirim_yontemi ?? '') . '</td></tr>';
$html .= '<tr><td>Bildirim Tarihi</td><td>' . (isset($summary->proje->bildirim_tarihi)?date('d.m.Y', strtotime($summary->proje->bildirim_tarihi)):'') . '</td></tr>';
$html .= '<tr><td>Kesinleşme Tarihi</td><td>' . (isset($summary->proje->kesinlesme_tarihi)?date('d.m.Y', strtotime($summary->proje->kesinlesme_tarihi)):'') . '</td></tr>';
$html .= '<tr><td>İtiraz</td><td>' . ((($summary->proje->itiraz_var_mi ?? 0)==1)?'Var':'Yok') . '</td></tr>';
$html .= '<tr><td>İtiraz Tarihi</td><td>' . (isset($summary->proje->itiraz_tarihi)?date('d.m.Y', strtotime($summary->proje->itiraz_tarihi)):'') . '</td></tr>';
$html .= '<tr><td>İtiraz Karar Tarihi</td><td>' . (isset($summary->proje->itiraz_karar_tarihi)?date('d.m.Y', strtotime($summary->proje->itiraz_karar_tarihi)):'') . '</td></tr>';
$html .= '<tr><td>İtiraz Sonucu</td><td>' . htmlspecialchars($summary->proje->itiraz_sonucu ?? '') . '</td></tr>';
$html .= '<tr><td>Paylandırma Esası</td><td>' . htmlspecialchars($summary->proje->paylandirma_esasi ?? '') . '</td></tr>';
$html .= '<tr><td>Yönetim Planı Referansı</td><td>' . nl2br(htmlspecialchars($summary->proje->yonetim_plani_referans ?? '')) . '</td></tr>';
$html .= '<tr><td>İmza Oranı</td><td>' . htmlspecialchars($summary->proje->imza_orani ?? '') . '</td></tr>';
$html .= '</tbody></table></div>';
$html .= '<div class="section"><h2>Daire Bazında Aylık Avans</h2><table><thead><tr><th>Blok</th><th>Daire</th><th>Daire Kodu</th><th class="right">Aylık Avans</th></tr></thead><tbody>';
foreach ($paylasim as $row) {
    $html .= '<tr><td>' . htmlspecialchars($row->blok_adi) . '</td><td>' . htmlspecialchars($row->daire_no) . '</td><td>' . htmlspecialchars($row->daire_kodu) . '</td><td class="right">' . Helper::formattedMoney($row->aylik_avans) . '</td></tr>';
}
$html .= '</tbody></table></div>';
$html .= '<div class="section muted">Oluşturulma: ' . date('d.m.Y H:i') . '</div>';

$html .= '</body></html>';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
// Sayfa alt bilgi: Sayfa X / Y
$canvas = $dompdf->get_canvas();
$w = $canvas->get_width();
$h = $canvas->get_height();
$font = $dompdf->getFontMetrics()->get_font('DejaVu Sans', 'normal');
$canvas->page_text($w - 120, $h - 30, "Sayfa {PAGE_NUM} / {PAGE_COUNT}", $font, 10, array(0,0,0));

if (ob_get_length()) { ob_end_clean(); }
$dompdf->stream('isletme_projesi_' . ($summary->proje->proje_adi ?? 'proje') . '.pdf', ['Attachment' => false]);
exit;