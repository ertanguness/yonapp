<?php

use App\Helper\FinansalHelper;
use App\Helper\Security;
use App\Helper\Helper;
use App\Services\Gate;
use Model\KasaModel;

Gate::authorizeOrDie("income_expense_add_update");

$Kasa = new KasaModel();


?>




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
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#gelirGiderModal">
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
                            <thead class="table-light">
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


<!-- Gelir Gider Modal -->

<!-- <div class="modal fade" id="gelirGiderModal" tabindex="-1" aria-labelledby="gelirGiderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="gelirGiderModalLabel">Gelir Gider İşlemleri</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="gelirGiderForm" method="post">
                    <input type="hidden" name="id" id="icra_id" value="<?= $_GET['id'] ?? 0; ?>">

                                  

                   
                    <div class="mb-3">
                        <label for="aciklama" class="form-label">Açıklama</label>
                        <textarea class="form-control" id="aciklama" name="aciklama" rows="3" placeholder="Gelir gider işlemleriyle ilgili notlar..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                <button type="button" class="btn btn-primary" id="gelirGiderKaydet">Kaydet</button>
            </div>
        </div>
    </div>
</div> -->
<style>
    .option-card {
        border: 1px dashed #e0e0e0;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .option-card:hover {
        border-color: #6c757d;
    }

    .option-card.selected {
        border-color: #0d6efd;
        background-color: #f0f8ff;
    }

    .option-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 5px;
    }

    .option-title {
        font-weight: 600;
        color: #333;
    }

    .option-price {
        font-weight: 600;
        color: #0d6efd;
    }

    .option-desc {
        color: #6c757d;
        font-size: 0.9rem;
        margin-bottom: 0;
    }

    input[type="radio"] {
        margin-right: 10px;
    }

    .radio-label {
        display: flex;
        align-items: flex-start;
        width: 100%;
        cursor: pointer;
    }

    .radio-content {
        flex-grow: 1;
    }
</style>

<!-- Gelir Gider Modal -->
<div class="modal fade" id="gelirGiderModal" tabindex="-1" aria-labelledby="gelirGiderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="gelirGiderModalLabel">Gelir Gider İşlemleri</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="gelirGiderForm" method="post">
                    <input type="hidden" name="id" id="icra_id" value="<?= $_GET['id'] ?? 0; ?>">
                    <input type="hidden" name="islem_id" id="islem_id" value="0">

                    <!-- İşlem Türü -->
                    <div class="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="option-card" id="standardOption">
                                    <label class="radio-label">
                                        <input type="radio" name="islem_tipi" value="gelir" checked>
                                        <div class="radio-content">
                                            <div class="option-header">
                                                <span class="option-title">Gelir</span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="option-card" id="expressOption">
                                    <label class="radio-label">
                                        <input type="radio" name="islem_tipi" value="gider">
                                        <div class="radio-content">
                                            <div class="option-header">
                                                <span class="option-title">Gider</span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kasa -->
                    <div class="mb-3">
                        <label for="kasa" class="form-label">Kasa *</label>

                        <?php echo FinansalHelper::KasaSelect("kasa") ?>
                    </div>

                    <!-- İşleme Tarihi -->
                    <div class="mb-3">
                        <div class="row">

                            <div class="col-md-6">

                                <label for="islem_tarihi" class="form-label">İşlem Tarihi *</label>
                                <input type="text" class="form-control flatpickr" name="islem_tarihi" id="islem_tarihi" required
                                    value="<?= date('Y-m-d'); ?>">
                            </div>

                            <div class="col-md-6">

                                <label for="tutar" class="form-label">Tutar (₺) *</label>
                                <div class="input-group">
                                    <input type="text" class="form-control money" id="tutar" name="tutar"
                                        placeholder="0.00" required>
                                    <span class="input-group-text">₺</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Kategori -->
                    <div class="mb-3">
                        <label for="kategori" class="form-label">Kategori *</label>
                        <?php echo Helper::getOdemeKategoriSelect("kategori") ?>
                       
                    </div>

                    <!-- Açıklama -->
                    <div class="mb-3">
                        <label for="aciklama" class="form-label">Açıklama</label>
                        <textarea class="form-control" id="aciklama" name="aciklama" rows="3"
                            placeholder="Gelir gider işlemleriyle ilgili detaylı açıklama..."></textarea>
                    </div>

                    <!-- Ödeme Yöntemi -->
                    <div class="mb-3">
                        <label for="odeme_yontemi" class="form-label">Ödeme Yöntemi</label>
                        <?php echo Helper::getOdemeYontemiSelect("odeme_yontemi") ?>
                    </div>

                    <!-- Belge No -->
                    <div class="mb-3">
                        <label for="belge_no" class="form-label">Belge No</label>
                        <input type="text" class="form-control" id="belge_no" name="belge_no"
                            placeholder="Fatura, fiş veya belge numarası">
                    </div>

                    <div class="alert alert-info">
                        <small><strong>*</strong> işaretli alanlar zorunludur.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                <button type="button" class="btn btn-primary" id="gelirGiderKaydet">Kaydet</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript için ek kod -->
<script>
    $(function(){

   
    document.addEventListener('DOMContentLoaded', function() {
        const islemTuruSelect = document.getElementById('islem_turu');
        const gelirKategorileri = document.getElementById('gelir_kategorileri');
        const giderKategorileri = document.getElementById('gider_kategorileri');
        const kategoriSelect = document.getElementById('kategori');

        // İşlem türü değiştiğinde kategorileri güncelle
        islemTuruSelect.addEventListener('change', function() {
            gelirKategorileri.style.display = 'none';
            giderKategorileri.style.display = 'none';
            kategoriSelect.value = '';

            if (this.value === 'gelir') {
                gelirKategorileri.style.display = 'block';
            } else if (this.value === 'gider') {
                giderKategorileri.style.display = 'block';
            }
        });

        // Form gönderme işlemi
        document.getElementById('gelirGiderKaydet').addEventListener('click', function() {
            const form = document.getElementById('gelirGiderForm');

            if (form.checkValidity()) {
                // Form verilerini gönderme işlemi burada yapılacak
                console.log('Form gönderiliyor...');
                // AJAX veya normal form submit işlemi
            } else {
                form.reportValidity();
            }
        });

        // Modal kapatıldığında formu temizle
        document.getElementById('gelirGiderModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('gelirGiderForm').reset();
            gelirKategorileri.style.display = 'none';
            giderKategorileri.style.display = 'none';
        });
    });
     });
</script>

<style>
    .optgroup-label {
        font-weight: bold;
        color: #495057;
        background-color: #f8f9fa;
    }
</style>