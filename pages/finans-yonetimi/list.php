

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Finans Yönetimi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Gelir Gider İşlemleri</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items d-flex align-items-center gap-2">
            <a href="#" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse" data-bs-target="#collapseOne"
                title="Filtrele">
                <i class="feather-filter"></i>
            </a>
            <button type="button" class="btn btn-primary route-link" data-page="income-expense/manage">
                <i class="feather-plus me-2"></i> Yeni Gelir/Gider Ekle
            </button>
        </div>
    </div>
</div>
<div class="main-content">

    <?php
    $title = "Gelir ve Gider Listesi";
    $text = "Site gelir ve giderlerinizi buradan takip edebilir, yeni işlemler ekleyebilir, düzenleyebilir veya silebilirsiniz.";
    require_once 'pages/components/alert.php'
    ?>

    <!-- [Mini Card] start -->
    <div class="row ">
        <div class="col-xxl-4 col-md-6">
            <div class="card card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="me-3">
                        <h5 class="fs-4">₺15,000.00</h5>
                        <span class="text-muted">Toplam Gelir</span>
                    </div>
                    <div class="avatar-text avatar-lg bg-success text-white rounded">
                        <i class="feather-trending-up"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-4 col-md-6">
            <div class="card card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="me-3">
                        <h5 class="fs-4">₺7,500.00</h5>
                        <span class="text-muted">Toplam Gider</span>
                    </div>
                    <div class="avatar-text avatar-lg bg-danger text-white rounded">
                        <i class="feather-trending-down"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-4 col-md-6">
            <div class="card card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="me-3">
                        <h5 class="fs-4">₺7,500.00</h5>
                        <span class="text-muted">Net Kalan</span>
                    </div>
                    <div class="avatar-text avatar-lg bg-primary text-white rounded">
                        <i class="feather-bar-chart-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [Mini Card] end -->

    <!-- [Filtreleme] start -->
    <div class="row ">
        <div class="card-footer">
            <div id="collapseOne" class="accordion-collapse collapse page-header-collapse">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Raporları Filtrele</h5>
                        <form id="filterForm">
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="startDate" class="form-label">Başlangıç Tarihi</label>
                                    <input type="date" id="startDate" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label for="endDate" class="form-label">Bitiş Tarihi</label>
                                    <input type="date" id="endDate" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label for="incExpType" class="form-label">Gelir/Gider Türü</label>
                                    <select id="incExpType" class="form-select">
                                        <option value="all">Tümü</option>
                                        <option value="income">Gelir</option>
                                        <option value="expense">Gider</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-3 text-end">
                                <button type="submit" class="btn btn-primary">Filtrele</button>
                            </div>
                        </form>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Gelir ve Gider Grafiği</h5>
                                    <canvas id="incomeExpenseChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [Filtreleme] bitiş -->

    <!-- Liste Tablosu -->
    <div class="row row-deck row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered text-center datatables">
                            <thead >
                                <tr>
                                    <th>Sıra</th>
                                    <th>Tarih</th>
                                    <th>İşlem Türü</th>
                                    <th>Kategori</th>
                                    <th>Açıklama</th>
                                    <th>Tutar</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>01.04.2025</td>
                                    <td><span class="badge bg-success">Gelir</span></td>
                                    <td>Aidat</td>
                                    <td>Nisan Ayı Aidat Ödemesi</td>
                                    <td class="text-success">₺3.000,00</td>
                                    <td>
                                        <div class="hstack gap-2 justify-content-center">
                                            <a href="#" class="avatar-text avatar-md">
                                                <i class="feather-edit"></i>
                                            </a>
                                            <a href="#" class="avatar-text avatar-md">
                                                <i class="feather-trash-2"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>03.04.2025</td>
                                    <td><span class="badge bg-danger">Gider</span></td>
                                    <td>Elektrik</td>
                                    <td>Ortak Alan Elektrik Gideri</td>
                                    <td class="text-danger">₺1.250,00</td>
                                    <td>
                                        <div class="hstack gap-2 justify-content-center">
                                            <a href="#" class="avatar-text avatar-md">
                                                <i class="feather-edit"></i>
                                            </a>
                                            <a href="#" class="avatar-text avatar-md">
                                                <i class="feather-trash-2"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Diğer satırlar buraya eklenecek -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Liste Tablosu Bitiş -->


</div>

<script>
// Verileri dinamik olarak alıp grafiği güncelleyecek fonksiyon
document.getElementById('filterForm').addEventListener('submit', function(e) {
    e.preventDefault(); // Formun sayfayı yenilemesini engelle

    // Formdan alınan veriler
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const incExpType = document.getElementById('incExpType').value;

    // Örnek veri, burada filtrelenmiş gelir-gider verileri alınacak
    // Bu veriler, PHP'den dinamik olarak alınabilir.
    const filteredData = getFilteredData(startDate, endDate, incExpType);

    // Grafik verileri
    const labels = filteredData.dates;
    const incomeData = filteredData.income;
    const expenseData = filteredData.expenses;

    // Grafik oluşturulacak
    const ctx = document.getElementById('incomeExpenseChart').getContext('2d');
    const incomeExpenseChart = new Chart(ctx, {
        type: 'bar', // Bar grafiği
        data: {
            labels: labels,
            datasets: [{
                    label: 'Gelir',
                    data: incomeData,
                    backgroundColor: 'rgba(40, 167, 69, 0.7)', // Yeşil renk
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Gider',
                    data: expenseData,
                    backgroundColor: 'rgba(255, 99, 132, 0.7)', // Kırmızı renk
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                },
            },
            scales: {
                x: {
                    beginAtZero: true
                },
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});

// Örnek veri (Burada PHP ile gerçek veri çekilecektir)
function getFilteredData(startDate, endDate, incExpType) {
    // Burada sadece örnek veri var. PHP ile backend'den çekilen verilere göre dinamikleştirilmeli.
    return {
        dates: ['2025-04-01', '2025-04-02', '2025-04-03', '2025-04-04'],
        income: [3000, 2000, 4000, 5000],
        expenses: [1000, 1500, 2000, 1200]
    };
}
</script>