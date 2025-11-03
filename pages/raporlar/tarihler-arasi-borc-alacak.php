<?php
require_once dirname(__DIR__, 2) . '/configs/bootstrap.php';

use App\Helper\Security;
use App\Helper\Date;

$site_id = $_SESSION['site_id'] ?? 0;

$start = $_GET['start'] ?? date('01.01.Y');
$end   = $_GET['end']   ?? date('31.12.Y');
$format= $_GET['format']?? 'pdf';

?>
<div class="container-xxl py-3">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Tarihler Arası Borç / Alacak Raporu</h5>
    </div>
    <div class="card-body">
      <form id="raporForm" class="row g-3" method="get" action="">
        <div class="col-sm-4">
          <label for="start" class="form-label">Başlangıç Tarihi</label>
          <input type="text" class="form-control flatpickr" id="start" name="start" value="<?= htmlspecialchars($start) ?>" placeholder="gg.aa.yyyy">
        </div>
        <div class="col-sm-4">
          <label for="end" class="form-label">Bitiş Tarihi</label>
          <input type="text" class="form-control flatpickr" id="end" name="end" value="<?= htmlspecialchars($end) ?>" placeholder="gg.aa.yyyy">
        </div>
        <div class="col-sm-4">
          <label for="format" class="form-label">Format</label>
          <select id="format" name="format" class="form-select">
            <option value="pdf" <?= $format==='pdf'?'selected':'' ?>>PDF</option>
            <option value="xlsx" <?= $format==='xlsx'?'selected':'' ?>>Excel (XLSX)</option>
            <option value="csv" <?= $format==='csv'?'selected':'' ?>>CSV</option>
            <option value="html" <?= $format==='html'?'selected':'' ?>>HTML</option>
          </select>
        </div>
        <div class="col-12 d-flex gap-2 align-items-end flex-wrap">
          <button type="button" id="indirBtn" class="btn btn-primary">
            <i class="bi bi-download"></i> İndir
          </button>
          <button type="button" id="borcOzetBtn" class="btn btn-outline-primary">
            <i class="bi bi-list-columns"></i> Borç Bazında Ödeme Özeti
          </button>
          <a class="btn btn-outline-secondary" href="?start=<?= date('01.01.Y') ?>&end=<?= date('d.m.Y') ?>">Bugüne Sıfırla</a>
          <div class="vr d-none d-sm-inline mx-1"></div>
          <div class="btn-group" role="group" aria-label="Hızlı Tarihler">
            <button type="button" class="btn btn-light" data-range="this-month">Bu Ay</button>
            <button type="button" class="btn btn-light" data-range="last-month">Geçen Ay</button>
            <button type="button" class="btn btn-light" data-range="this-year">Bu Yıl</button>
          </div>
        </div>
      </form>

      <hr class="my-3">
      <p class="text-muted mb-0">Not: İndir butonu, seçilen tarih aralığı için raporu oluşturup indirir.</p>
    </div>
  </div>
</div>

<script>
  // Flatpickr init (varsa)
  if (window.flatpickr) {
    flatpickr('.flatpickr', { dateFormat: 'd.m.Y', allowInput: true });
  }

  function pad(n){return String(n).padStart(2,'0');}
  function ymd(d){ return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`; }
  function dmy(d){ return `${pad(d.getDate())}.${pad(d.getMonth()+1)}.${d.getFullYear()}`; }
  function parseDMY(s){ const m=s.match(/^(\d{2})\.(\d{2})\.(\d{4})$/); if(!m) return null; return new Date(parseInt(m[3]), parseInt(m[2])-1, parseInt(m[1])); }
  function toIsoFromDMY(s){ const d=parseDMY(s); return d? ymd(d) : s; }
  function rangePreset(type){
    const now = new Date();
    const firstDayOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);
    const lastDayOfMonth  = new Date(now.getFullYear(), now.getMonth()+1, 0);
    const firstDayOfLastMonth = new Date(now.getFullYear(), now.getMonth()-1, 1);
    const lastDayOfLastMonth  = new Date(now.getFullYear(), now.getMonth(), 0);
    const firstDayOfYear   = new Date(now.getFullYear(), 0, 1);
    const lastDayOfYear    = new Date(now.getFullYear(), 11, 31);
    if (type==='this-month') return [dmy(firstDayOfMonth), dmy(lastDayOfMonth)];
    if (type==='last-month') return [dmy(firstDayOfLastMonth), dmy(lastDayOfLastMonth)];
    if (type==='this-year')  return [dmy(firstDayOfYear), dmy(lastDayOfYear)];
    return [document.getElementById('start').value, document.getElementById('end').value];
  }

  document.querySelectorAll('[data-range]').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const [s,e] = rangePreset(btn.dataset.range);
      document.getElementById('start').value = s;
      document.getElementById('end').value   = e;
    });
  });

  document.getElementById('indirBtn').addEventListener('click', function(){
    const startDMY = document.getElementById('start').value || '<?= date('d.m.Y') ?>';
    const endDMY   = document.getElementById('end').value   || '<?= date('d.m.Y') ?>';
    const format= document.getElementById('format').value || 'pdf';
    const sd = parseDMY(startDMY), ed = parseDMY(endDMY);
    if (!sd || !ed) { alert('Lütfen geçerli tarih giriniz (gg.aa.yyyy).'); return; }
    if (sd > ed) { alert('Başlangıç tarihi bitişten büyük olamaz.'); return; }
    const start = toIsoFromDMY(startDMY);
    const end   = toIsoFromDMY(endDMY);
    const url = `pages/dues/payment/export/tarihler_arasi_borc_alacak.php?start=${encodeURIComponent(start)}&end=${encodeURIComponent(end)}&format=${encodeURIComponent(format)}`;
    window.location.href = url;
  });

  // Borç Bazında Özet butonu
  document.getElementById('borcOzetBtn').addEventListener('click', function(){
    const startDMY = document.getElementById('start').value || '<?= date('d.m.Y') ?>';
    const endDMY   = document.getElementById('end').value   || '<?= date('d.m.Y') ?>';
    const format= document.getElementById('format').value || 'pdf';
    const sd = parseDMY(startDMY), ed = parseDMY(endDMY);
    if (!sd || !ed) { alert('Lütfen geçerli tarih giriniz (gg.aa.yyyy).'); return; }
    if (sd > ed) { alert('Başlangıç tarihi bitişten büyük olamaz.'); return; }
    const start = toIsoFromDMY(startDMY);
    const end   = toIsoFromDMY(endDMY);
    const url = `pages/dues/payment/export/borc_bazli_ozet.php?start=${encodeURIComponent(start)}&end=${encodeURIComponent(end)}&format=${encodeURIComponent(format)}`;
    window.location.href = url;
  });
</script>
