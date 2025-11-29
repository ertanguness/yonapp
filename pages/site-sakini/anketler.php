<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Anketler</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/sakin/ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Anketler</li>
        </ul>
    </div>
    </div>

<div class="main-content">
    <div class="row g-4">
        <div class="col-12">
            <div class="card rounded-3">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Anket Listesi</h5>
                    <a href="/sakin/anket-listesi" class="btn btn-light">Tümü</a>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div class="fw-semibold mb-2">Aidat artırımı önerisi</div>
                        <div class="fs-12 text-muted mb-3">Son tarih: 31.12.2025</div>
                        <div class="d-flex flex-column gap-2">
                            <label class="d-flex align-items-center gap-2">
                                <input type="radio" name="anket1" class="form-check-input" />
                                <span>%10 artırılsın</span>
                            </label>
                            <label class="d-flex align-items-center gap-2">
                                <input type="radio" name="anket1" class="form-check-input" />
                                <span>%5 artırılsın</span>
                            </label>
                            <label class="d-flex align-items-center gap-2">
                                <input type="radio" name="anket1" class="form-check-input" />
                                <span>Artırılmasın</span>
                            </label>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-primary">Oy Ver</button>
                        </div>
                    </div>
                    <hr class="border-dashed">
                    <div>
                        <div class="fw-semibold mb-2">Sonuç</div>
                        <div id="anketResultChart" style="height:260px"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    function render(){
        if (!window.ApexCharts) return;
        new ApexCharts(document.querySelector('#anketResultChart'), {
            chart: { type: 'bar', height: 260, toolbar: { show: false } },
            series: [{ name: 'Oy', data: [45, 30, 25] }],
            xaxis: { categories: ['%10', '%5', 'Artırılmasın'] },
            colors: ['#5e60e8'],
            dataLabels: { enabled: false },
            grid: { strokeDashArray: 4 }
        }).render();
    }
    if (!window.ApexCharts) {
        var s = document.createElement('script');
        s.src = '/assets/vendors/js/apexcharts.min.js';
        s.onload = render;
        document.body.appendChild(s);
    } else {
        render();
    }
});
</script>