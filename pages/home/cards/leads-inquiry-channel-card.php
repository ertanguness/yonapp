<?php
use Model\BorclandirmaModel;

$BorclandirmaModel = new BorclandirmaModel();

// Site ID kontrolü ve güvenliği
$site_id = $_SESSION['site_id'] ?? 0;

$selectedYearLeads = isset($_GET['leads_chart_year']) ? (int)$_GET['leads_chart_year'] : (int)date('Y');
$currentYear = (int)date('Y');

// Son 5 yılı seçenek olarak hazırla
$yearOptionsLeads = [];
for ($y = $currentYear; $y >= $currentYear - 4; $y--) {
    $yearOptionsLeads[] = $y;
}

$monthsTR = ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'];

$aidatSummary = $BorclandirmaModel->getAidatYearlyStats($site_id, $selectedYearLeads);

$odenecekSeries = [];
$odenenSeries = [];
$kalanSeries = [];

for ($m = 1; $m <= 12; $m++) {
    $odenecek = $aidatSummary[$m]['odenecek'] ?? 0;
    $odenen = $aidatSummary[$m]['odenen'] ?? 0;
    $kalan = max(0, $odenecek - $odenen); // Kalan tutarı hesapla

    $odenecekSeries[] = $odenecek;
    $odenenSeries[] = $odenen;
    $kalanSeries[] = $kalan;
}

$hasDataLeads = (array_sum($odenecekSeries) > 0 || array_sum($odenenSeries) > 0);

$chartLeads = [
    'odenen' => $odenenSeries,
    'kalan' => $kalanSeries,
    'categories' => $monthsTR,
    'hasData' => $hasDataLeads
];
?>
<div class="col-xxl-12 mb-5 card-wrapper" data-card="leads-inquiry-channel-card">
    <div class="card stretch stretch-full">
        <div class="card-header d-flex align-items-center justify-content-between position-relative">
            <h5 class="card-title">Yıllık Aidat Ödeme Grafiği</h5>
            
            <!-- Yıl Seçimi (Ortalanmış) -->
             <div style="position: absolute; left: 50%; transform: translateX(-50%);">
                   <select id="chartYearSelectLeads" class="form-select form-select-sm" style="width: auto; padding-right: 35px; background-position: right 0.75rem center;">
                      <?php foreach ($yearOptionsLeads as $opt): ?>
                          <option value="<?= $opt ?>" <?= $opt === $selectedYearLeads ? 'selected' : '' ?>><?= $opt ?></option>
                      <?php endforeach; ?>
                  </select>
             </div>

            <div class="card-header-action d-flex align-items-center gap-2">
                <div class="card-header-btn ms-auto">
                    <div data-bs-toggle="tooltip" title="Delete">
                        <a href="javascript:void(0);" class="avatar-text avatar-xs bg-danger"
                            data-bs-toggle="remove"> </a>
                    </div>
                    <div data-bs-toggle="tooltip" title="Refresh">
                        <a href="javascript:void(0);" class="avatar-text avatar-xs bg-warning"
                            data-bs-toggle="refresh"> </a>
                    </div>
                    <div data-bs-toggle="tooltip" title="Maximize/Minimize">
                        <a href="javascript:void(0);" class="avatar-text avatar-xs bg-success"
                            data-bs-toggle="expand"> </a>
                    </div>
                </div>
            </div>
            <span class="drag-handle ms-3" title="Taşı"><i class="bi bi-arrows-move"></i></span>
        </div>
        <div class="card-body custom-card-action">
            <?php if (!$hasDataLeads): ?>
                <div class="alert alert-warning text-center" role="alert">
                    Bu yıla ait aidat verisi bulunmamaktadır.
                </div>
                <div id="leads-inquiry-channel" style="display:none;" data-dynamic="1" data-chart='<?= htmlspecialchars(json_encode($chartLeads, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES) ?>'></div>
            <?php else: ?>
                <div id="leads-inquiry-channel" data-dynamic="1" data-chart='<?= htmlspecialchars(json_encode($chartLeads, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES) ?>'></div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Yıl değiştiğinde sayfayı yenileyerek parametre gönder
    var yearSelect = document.getElementById('chartYearSelectLeads');
    if(yearSelect){
        yearSelect.addEventListener('change', function(){
            var url = new URL(window.location.href);
            url.searchParams.set('leads_chart_year', this.value);
            window.location.href = url.toString();
        });
    }

    var el = document.querySelector('#leads-inquiry-channel');
    if (!el) return;
    
    if (el.style.display === 'none') return;

    var chartDataRaw = el.getAttribute('data-chart');
    var chartData;
    try { chartData = JSON.parse(chartDataRaw); } catch(e) { chartData = null; }
    if (!chartData) return;
    
    if (!chartData.hasData) return;

    var odenenData = chartData.odenen || [];
    var kalanData = chartData.kalan || [];
    var categories = chartData.categories || [];
    
    new ApexCharts(el, {
        chart: {
            type: "bar",
            height: 350,
            stacked: true, 
            toolbar: { show: false }
        },
        plotOptions: {
            bar: {
                endingShape: "rounded",
                columnWidth: "20%", 
                horizontal: false
            }
        },
        colors: ["#25b865", "#fb8500"],
        series: [
            { name: "Ödenen Aidat", data: odenenData },
            { name: "Kalan Aidat", data: kalanData }
        ],
        xaxis: {
            categories: categories,
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: { style: { fontSize: "10px", colors: "#64748b" } }
        },
        yaxis: {
            labels: {
                formatter: function(e) { return e.toLocaleString('tr-TR') + ' ₺'; },
                offsetX: -5,
                offsetY: 0,
                style: { color: "#64748b" }
            }
        },
        grid: {
            xaxis: { lines: { show: false } },
            yaxis: { lines: { show: false } }
        },
        dataLabels: { enabled: false },
        tooltip: {
            shared: true,
            inverseOrder: true,
            y: {
                formatter: function(e) { return e.toLocaleString('tr-TR') + ' ₺'; }
            },
            style: { fontSize: "11px", fontFamily: "Inter" }
        },
        legend: {
            show: true,
            position: "top",
            horizontalAlign: "left",
            fontSize: "12px",
            fontFamily: "Inter",
            labels: { fontSize: "12px", colors: "#64748b" },
            markers: { width: 10, height: 10, radius: 25 },
            itemMargin: { horizontal: 15, vertical: 5 }
        }
    }).render();
});
</script>