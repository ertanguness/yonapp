<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">İcra Takip Detayları</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item"><a href="icra-takip.php">İcra Takibi</a></li>
            <li class="breadcrumb-item">Detaylar</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex d-md-none">
                <a href="javascript:void(0)" class="page-header-right-close-toggle">
                    <i class="feather-arrow-left me-2"></i>
                    <span>Geri</span>
                </a>
            </div>
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <button type="button" class="btn btn-outline-secondary route-link me-2" data-page="levy/list">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </button>
                <button type="button" class="btn btn-info" id="payPlanAdd" data-bs-toggle="modal" data-bs-target="#paymentPlanModal">
                    <i class="feather-refresh-cw me-2"></i>Ödeme Planı Ekle
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#updateStatusModal">Durum Güncelle</button>
            </div>
        </div>
        <div class="d-md-none d-flex align-items-center">
            <a href="javascript:void(0)" class="page-header-right-open-toggle">
                <i class="feather-align-right fs-20"></i>
            </a>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <!-- İcra Takip Bilgileri -->
                        <div class="row mb-4">
                            <div class="col-lg-3 fw-semibold">Daire / Kişi:</div>
                            <div class="col-lg-9">Ahmet Yılmaz / A-12</div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-lg-3 fw-semibold">Borç Tutarı:</div>
                            <div class="col-lg-9">12.500 ₺</div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-lg-3 fw-semibold">Başlangıç Tarihi:</div>
                            <div class="col-lg-9">2025-04-01</div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-lg-3 fw-semibold">Faiz Oranı (%):</div>
                            <div class="col-lg-9">15</div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-lg-3 fw-semibold">İcra Dairesi:</div>
                            <div class="col-lg-9">İstanbul 5. İcra</div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-lg-3 fw-semibold">Dosya No:</div>
                            <div class="col-lg-9">2025/1578</div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-lg-3 fw-semibold">Durum:</div>
                            <div class="col-lg-9"><span class="badge bg-warning">Devam Ediyor</span></div>
                        </div>

                        <!-- Page Tabs -->
                        <div class="nav-tabs-custom">
                            <ul class="nav nav-pills flex-wrap w-100 text-center customers-nav-tabs" id="tabNavigation" role="tablist">
                                <li class="nav-item flex-fill border-top" role="presentation">
                                    <a href="javascript:void(0);" class="nav-link active" data-bs-toggle="pill" data-bs-target="#paymentPlan" role="tab">
                                        <i class="feather-credit-card me-2"></i>Ödeme Planı
                                    </a>
                                </li>
                                <li class="nav-item flex-fill border-top" role="presentation">
                                    <a href="javascript:void(0);" class="nav-link" data-bs-toggle="pill" data-bs-target="#fileStatus" role="tab">
                                        <i class="feather-folder me-2"></i>Dosya Durumu
                                    </a>
                                </li>
                                <li class="nav-item flex-fill border-top" role="presentation">
                                    <a href="javascript:void(0);" class="nav-link" data-bs-toggle="pill" data-bs-target="#icraStatus" role="tab">
                                        <i class="feather-activity me-2"></i>İcra Durumu
                                    </a>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <!-- Ödeme Planı Tab -->
                                <div class="tab-pane fade show active" id="paymentPlan" role="tabpanel">
                                    <table class="table text-center table-hover">
                                        <thead style="background-color:antiquewhite;">
                                            <tr>
                                                <th>Aylık Ödeme</th>
                                                <th>Faizi Oranı (%)</th>
                                                <th>Toplam Borç (₺)</th>
                                                <th>Son Ödeme Tarihi</th>
                                                <th>Ödenen Tarih</th>
                                                <th>Durum</th>
                                                <th>İşlem</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>1.000 ₺</td>
                                                <td>10%</td>
                                                <td>12.500 ₺</td>
                                                <td>2025-05-01</td>
                                                <td>2025-05-01</td>
                                                <td><span class="badge bg-success">Ödendi</span></td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <button class="btn btn-success btn-sm" onclick="updateStatus(this, 'Ödendi')" title="Onayla">
                                                            <i class="feather-check"></i>
                                                        </button>
                                                        <button class="btn btn-danger btn-sm" onclick="updateStatus(this, 'Ödenmedi')" title="Red">
                                                            <i class="feather-x"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>1.000 ₺</td>
                                                <td>10%</td>
                                                <td>12.500 ₺</td>
                                                <td>2025-06-01</td>
                                                <td>-</td>
                                                <td><span class="badge bg-danger">Ödenmedi</span></td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <button class="btn btn-success btn-sm" onclick="updateStatus(this, 'Ödendi')" title="Onayla">
                                                            <i class="feather-check"></i>
                                                        </button>
                                                        <button class="btn btn-danger btn-sm" onclick="updateStatus(this, 'Ödenmedi')" title="Red">
                                                            <i class="feather-x"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>1.000 ₺</td>
                                                <td>10%</td>
                                                <td>12.500 ₺</td>
                                                <td>2025-07-01</td>
                                                <td>-</td>
                                                <td><span class="badge bg-danger">Ödenmedi</span></td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <button class="btn btn-success btn-sm" onclick="updateStatus(this, 'Ödendi')" title="Onayla">
                                                            <i class="feather-check"></i>
                                                        </button>
                                                        <button class="btn btn-danger btn-sm" onclick="updateStatus(this, 'Ödenmedi')" title="Red">
                                                            <i class="feather-x"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                        </td>
                                    </table>
                                </div>

                                <!-- Dosya Durumu Tab -->
                                <div class="tab-pane fade" id="fileStatus" role="tabpanel">
                                    <h5 class="mt-3 mb-3">Dosya Durumu</h5>
                                    <p>Dosya şu anda inceleniyor ve işlemler devam ediyor.</p>
                                </div>

                                <!-- İcra Durumu Tab -->
                                <div class="tab-pane fade" id="icraStatus" role="tabpanel">
                                    <h5 class="mt-3 mb-3">İcra Durumu</h5>
                                    <p>İcra takibi başlatıldı ve şu an icra dairesinde işlemler sürmektedir.</p>
                                </div>
                            </div>
                        </div>
                    </div> <!-- /.card-body -->
                </div> <!-- /.card -->
            </div> <!-- /.col-12 -->
        </div> <!-- /.row-cards -->
    </div> <!-- /.container-xl -->
</div> <!-- /.main-content -->

<!-- Ödeme Planı Modal -->
<div class="modal fade" id="paymentPlanModal" tabindex="-1" aria-labelledby="paymentPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentPlanModalLabel">Ödeme Planı Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="paymentPlanForm">
                    <!-- Toplam Borç -->
                    <div class="mb-3">
                        <label for="totalDebt" class="form-label">Toplam Borç (₺)</label>
                        <input type="number" class="form-control" id="totalDebt" name="totalDebt" required readonly value="12.500">
                    </div>
                    <!-- Kaç Aya Yayılacak -->
                    <div class="mb-3">
                        <label for="installments" class="form-label">Kaç Aya Yayılacak?</label>
                        <input type="number" class="form-control" id="installments" name="installments" required min="1" max="12" placeholder="Örneğin 6, 12" value="6">
                    </div>
                    <!-- Aylık Ödeme -->
                    <div class="mb-3">
                        <label for="monthlyPayment" class="form-label">Aylık Ödeme (₺)</label>
                        <input type="number" class="form-control" id="monthlyPayment" name="monthlyPayment" required readonly>
                    </div>
                    <!-- Ödeme Tarihi -->
                    <div class="mb-3">
                        <label for="paymentStartDate" class="form-label">İlk Ödeme Tarihi</label>
                        <input type="date" class="form-control" id="paymentStartDate" name="paymentStartDate" required>
                    </div>
                    <!-- Açıklama -->
                    <div class="mb-3">
                        <label for="notes" class="form-label">Açıklama</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Ödeme planıyla ilgili notlar veya açıklamalar..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                <button type="button" class="btn btn-primary" id="savePaymentPlan">Kaydet</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Ödeme Planı Durum güncelleme fonksiyonu
    function updateStatus(button, status) {
        var row = button.closest('tr');
        var statusCell = row.querySelector('td:nth-child(6)'); // Durum hücresini seç
        var badge = statusCell.querySelector('span'); // Durumun gösterildiği badge

        // Durumu güncelle
        badge.textContent = status;
        badge.classList.remove('bg-success', 'bg-danger'); // Eski sınıfları kaldır

        // Duruma göre yeni sınıf ekle
        if (status === 'Ödendi') {
            badge.classList.add('bg-success');
        } else {
            badge.classList.add('bg-danger');
        }

        // Ödenen tarihi belirt
        var paymentDateCell = row.querySelector('td:nth-child(5)');
        if (status === 'Ödendi') {
            var currentDate = new Date().toISOString().split('T')[0]; // Bugünün tarihi
            paymentDateCell.textContent = currentDate;
        } else {
            paymentDateCell.textContent = '-';
        }
    }
</script>
<!-- Durum Güncelle Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateStatusModalLabel">Durum Güncelle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="statusUpdateForm">
                    <div class="mb-3">
                        <label for="fileStatusInput" class="form-label">Dosya Durumu</label>
                        <textarea class="form-control" id="fileStatusInput" rows="3">Dosya şu anda inceleniyor ve işlemler devam ediyor.</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="icraStatusInput" class="form-label">İcra Durumu</label>
                        <textarea class="form-control" id="icraStatusInput" rows="3">İcra takibi başlatıldı ve şu an icra dairesinde işlemler sürmektedir.</textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                <button type="button" class="btn btn-primary" id="updateStatusBtn">Güncelle</button>
            </div>
        </div>
    </div>
</div>