<?php

require_once dirname(__DIR__, 4) . '/configs/bootstrap.php';

use App\Helper\Date;
use App\Helper\Helper;
use App\Helper\Security;
use Model\SitelerModel;
use Model\TahsilatModel;
use Model\TahsilatDetayModel;
use Dompdf\Dompdf;

$encId = $_GET['makbuz_id'] ?? null;
$id = Security::decrypt($encId ?? null);
if (!$id) {
    die('Geçersiz makbuz');
}

$SiteModel = new SitelerModel();
$TahsilatModel = new TahsilatModel();
$TahsilatDetayModel = new TahsilatDetayModel();

$siteId = $_SESSION['site_id'] ?? 0;
$site = $SiteModel->find($siteId);

// Logo hazırlığı (base64)
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

$tahsilat = $TahsilatModel->getPaymentWithCaseName($id);
$tahsilatDetaylar = $TahsilatDetayModel->findAllByTahsilatIdWithDueDetails($tahsilat->id);

$aciklama = ($tahsilat->tutar < 0) ? 'iade' : 'tahsil';

$html = '<!DOCTYPE html><html lang="tr"><head><meta charset="UTF-8"><title>Makbuz</title><style>
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #000; }
    h1 { font-size: 18px; margin: 0 0 6px 0; text-transform: uppercase; }
    h2 { font-size: 14px; margin: 0 0 6px 0; }
    address { font-style: normal; color: #666; white-space: nowrap; }
    .header { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; }
    .brand { display: flex; align-items: center; }
    .brand .logo { height: 48px; width: auto; }
    .brand .brand-text { display: inline-block; margin-left: 8px; }
    .doc-meta { margin-left: auto; text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: 4px; }
    .section { margin-top: 10px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 6px; }
    th { background: #f7f7f7; }
    .right { text-align: right; }
    .muted { color: #666; }
    .text-warning { color: #FFA21D; }
    .signature { text-align: center; }
    @page { margin: 6mm; }
  </style></head><body>';

$html .= '<div class="header">'
       . '<div class="brand">'
       . ( $logoBase64 ? ('<img class="logo" src="' . $logoBase64 . '" alt="Logo">') : '' )
       . '<div class="brand-text">'
       . '<h1>' . htmlspecialchars($site->site_adi) . '</h1>'
       . '<address>' . htmlspecialchars($site->tam_adres . ' ' . $site->il . ' - ' . $site->ilce) . '</address>'
       . '</div>'
       . '</div>'
       . '<div class="doc-meta">'
       . '<div style="font-size:14px;font-weight:bold;">Makbuz</div>'
       . '<div><strong>Tarih:</strong> ' . date('d.m.Y H:i') . '</div>'
       . '</div>'
       . '</div>';

$html .= '<hr style="border:0;border-top:1px dashed #ccc;margin:8px 0">';

$html .= '<div class="section">'
       . '<h2>Tahsilat Detayı:</h2>'
       . '<div class="muted">'
       . '<div><span>İşlem Tarihi:</span> <strong>' . Date::dmYHIS($tahsilat->islem_tarihi) . '</strong></div>'
       . '<div><span>Tutar:</span> <strong>' . Helper::formattedMoney($tahsilat->tutar) . '</strong></div>'
       . '<div ><span>Ödeme Açıklaması:</span> <strong class="text-warning">' . htmlspecialchars($tahsilat->aciklama) . '</strong></div>'
       . '<div><span>Ödeme Yeri:</span> <strong>' . htmlspecialchars($tahsilat->kasa_adi) . '</strong></div>'
       . '</div>'
       . '</div>';

$html .= '<hr style="border:0;border-top:1px dashed #ccc;margin:8px 0">';

$html .= '<div class="section"><table><thead><tr>'
       . '<th>Sıra</th>'
       . '<th>Borç Adı</th>'
       . '<th class="right">Tutar</th>'
       . '<th>Açıklama</th>'
       . '</tr></thead><tbody>';

$i = 1;
foreach ($tahsilatDetaylar as $detay) {
    $html .= '<tr>'
          . '<td>' . $i++ . '</td>'
          . '<td>' . htmlspecialchars($detay->borc_adi) . '</td>'
          . '<td class="right">' . Helper::formattedMoney($detay->odenen_tutar) . '</td>'
          . '<td>' . htmlspecialchars($detay->borc_aciklama) . '</td>'
          . '</tr>';
}

$html .= '</tbody></table></div>';

$html .= '<div class="section" style="display:flex;justify-content:space-between;align-items:center">'
       . '<div>Yalnız <strong>' . Helper::sayiyiYaziyaCevir($tahsilat->tutar) . '</strong> TL ' . $aciklama . ' edilmiştir.</div>'
       . '<div class="signature">'
       . '<h6 style="margin:0 0 4px 0;font-size:13px">Yönetici İmza</h6>'
       . '<p style="margin:0;font-size:11px;color:#666">......./......../............</p>'
       . '</div>'
       . '</div>';

$html .= '</body></html>';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
if (ob_get_length()) { ob_end_clean(); }
$filename = 'makbuz_' . ($tahsilat->id ?? $id) . '.pdf';
$dompdf->stream($filename, ['Attachment' => true]);
exit;
