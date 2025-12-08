<?php
use Model\KasaHareketModel;

$KasaHareketModel = new KasaHareketModel();
// Yıl seçimi için varsayılan yıl veya URL'den gelen yıl
$selectedYear = isset($_GET['payment_chart_year']) ? (int)$_GET['payment_chart_year'] : (int)date('Y');
$currentYear = (int)date('Y');

// Son 5 yılı seçenek olarak hazırla
$yearOptions = [];
for ($y = $currentYear; $y >= $currentYear - 4; $y--) {
    $yearOptions[] = $y;
}

$monthsTR = ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'];

$monthlySummary = $KasaHareketModel->getMonthlySummaryBySiteId($site_id, $selectedYear);

$gelirSeries = [];
$giderSeries = [];

for ($m = 1; $m <= 12; $m++) {
    $gelirSeries[] = $monthlySummary[$m]['gelir'];
    $giderSeries[] = $monthlySummary[$m]['gider'];
}

$karSeries = [];
foreach ($gelirSeries as $i => $g) {
    $karSeries[] = $g - $giderSeries[$i];
}

$totalGelir = array_sum($gelirSeries);
$totalGider = array_sum($giderSeries);
$karZarar = $totalGelir - $totalGider;

// Veri var mı kontrolü: Eğer toplam gelir ve toplam gider 0 ise veri yok kabul edelim.
$hasData = ($totalGelir > 0 || $totalGider > 0);

$chart = [
    'gelir' => $gelirSeries,
    'gider' => $giderSeries,
    'kar' => $karSeries,
    'categories' => $monthsTR,
    'hasData' => $hasData
];
?>
<div class="col-xxl-12 card-wrapper" data-card="payment-records-chart-card">
    <div class="card stretch stretch-full">
        <div class="card-header d-flex align-items-center justify-content-between position-relative">
            <h5 class="card-title">Yıllık Gelir-Gider Grafiği</h5>
            
             <!-- Yıl Seçimi (Ortalanmış) -->
             <div style="position: absolute; left: 50%; transform: translateX(-50%);">
                   <select id="chartYearSelect" class="form-select form-select-sm" style="width: auto; padding-right: 35px; background-position: right 0.75rem center;">
                      <?php foreach ($yearOptions as $opt): ?>
                          <option value="<?= $opt ?>" <?= $opt === $selectedYear ? 'selected' : '' ?>><?= $opt ?></option>
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
            <?php if (!$hasData): ?>
                <div class="alert alert-warning text-center" role="alert">
                    Bu yıla ait gelir veya gider verisi bulunmamaktadır.
                </div>
                <!-- Grafik yine de render edilsin ama boş görünsün veya gizlensin isteniyorsa burayı yönetebiliriz. 
                     Kullanıcı 'grafik üstünde veri varsa gözükmesin' dedi, veri yoksa uyarı ver dedi. 
                     Veri yoksa grafiği hiç çizdirmeyip sadece uyarıyı gösteriyoruz. -->
                <div id="payment-records-chart" style="display:none;" data-dynamic="1" data-chart='<?= htmlspecialchars(json_encode($chart, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES) ?>'></div>
            <?php else: ?>
                <div id="payment-records-chart" data-dynamic="1" data-chart='<?= htmlspecialchars(json_encode($chart, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES) ?>'></div>
            <?php endif; ?>
            
            <div class="d-none d-md-flex flex-wrap pt-4 border-top">
                <div class="flex-fill">
                    <p class="fs-11 fw-medium text-uppercase text-muted mb-2">Toplam Gelir</p>
                    <h2 class="fs-20 fw-bold mb-0" id="lblTotalGelir"><?= number_format($totalGelir, 2, ',', '.') ?> ₺</h2>
                </div>
                <div class="vr mx-4 text-gray-600"></div>
                <div class="flex-fill">
                    <p class="fs-11 fw-medium text-uppercase text-muted mb-2">Toplam Gider</p>
                    <h2 class="fs-20 fw-bold mb-0" id="lblTotalGider"><?= number_format($totalGider, 2, ',', '.') ?> ₺</h2>
                </div>
                <div class="vr mx-4 text-gray-600"></div>
                <div class="flex-fill">
                    <p class="fs-11 fw-medium text-uppercase text-muted mb-2">Kar / Zarar</p>
                    <h2 class="fs-20 fw-bold mb-0" id="lblKarZarar"><?= number_format($karZarar, 2, ',', '.') ?> ₺</h2>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Yıl değiştiğinde sayfayı yenileyerek parametre gönder (Basit çözüm)
     // Veya AJAX ile sadece bu kartı/veriyi yenilemek daha şık olur ama şu an tüm sayfa yapısına hakim değiliz.
     // Kullanıcı "filtrelemeli seçime dayalı" dedi, en temiz yöntem şimdilik reload.
     var yearSelect = document.getElementById('chartYearSelect');
     if(yearSelect){
         yearSelect.addEventListener('change', function(){
             var url = new URL(window.location.href);
             url.searchParams.set('payment_chart_year', this.value);
             window.location.href = url.toString();
         });
     }

    var el = document.querySelector('#payment-records-chart');
    if (!el) return;
    
    // Veri yoksa ve display:none ise çık
    if (el.style.display === 'none') return;

    var chartDataRaw = el.getAttribute('data-chart');
    var chartData;
    try { chartData = JSON.parse(chartDataRaw); } catch(e) { chartData = null; }
    if (!chartData) return;
    
    // hasData kontrolü sunucuda yapıldı ama burada da emin olalım
    if (!chartData.hasData) return;

    var gelirData = chartData.gelir || [];
    var giderData = chartData.gider || [];
    var categories = chartData.categories || [];
    
    new ApexCharts(el, {
        chart: {
            type: "bar",
            height: 350,
            stacked: false,
            toolbar: { show: false }
        },
        plotOptions: {
            bar: {
                endingShape: "rounded",
                columnWidth: "50%",
                horizontal: false
            }
        },
        colors: ["#25b865", "#d13b4c"],
        series: [
            { name: "Gelir", data: gelirData },
            { name: "Gider", data: giderData }
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
        stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
        },
        tooltip: {
            shared: true,
            inverseOrder: false,
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
