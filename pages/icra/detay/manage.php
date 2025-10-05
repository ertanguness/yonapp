<?php
$site_id = $_SESSION['site_id'];

use App\Helper\Date;
use App\Helper\Helper;
use Model\KisilerModel;
use Model\IcraModel;
use App\Helper\Security;
use Model\IcraOdemeModel;

$kisilerModel = new KisilerModel();
$Icra = new IcraModel();
$IcraOdeme = new IcraOdemeModel();

$kisiler = $kisilerModel->SiteTumKisileri($site_id);
$id = Security::decrypt($id ?? 0);

$icraBilgileri = $Icra->IcraBilgileri($id);
$icraOdemeler = $IcraOdeme->IcraOdemeBilgileri($id);

?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">İcra Takip Detayları</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
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
                <a href="/icra-takibi" class="btn btn-outline-secondary me-2">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </a>
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
    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <!-- İcra Takip Bilgileri -->
                    <div class="row mb-4">
                        <div class="col-lg-3 fw-semibold">Dosya No:</div>
                        <div class="col-lg-9">
                            <?php
                            echo htmlspecialchars($icraBilgileri->dosya_no ?? '-');
                            ?>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-lg-3 fw-semibold">Kişi:</div>
                        <div class="col-lg-9">
                            <?php
                            $kisiBilgisi = $kisilerModel->KisiBilgileri($icraBilgileri->kisi_id ?? 0);
                            echo htmlspecialchars($kisiBilgisi->adi_soyadi ?? '-');
                            ?>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-lg-3 fw-semibold">Borç Tutarı:</div>
                        <div class="col-lg-9">
                            <?php
                            echo htmlspecialchars(string: $icraBilgileri->borc_tutari . ' ₺' ?? '-');
                            ?>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-lg-3 fw-semibold">Faiz Oranı (%):</div>
                        <div class="col-lg-9">
                            <?php
                            echo htmlspecialchars($icraBilgileri->faiz_orani ?? '-');
                            ?>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-lg-3 fw-semibold">Başlangıç Tarihi:</div>
                        <div class="col-lg-9">
                            <?php
                            echo Date::dmY($icraBilgileri->icra_baslangic_tarihi ?? '-');
                            ?>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-lg-3 fw-semibold">İcra Dairesi:</div>
                        <div class="col-lg-9">
                            <?php
                            echo htmlspecialchars($icraBilgileri->icra_dairesi ?? '-');
                            ?>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-lg-3 fw-semibold">Durum:</div>
                        <div class="col-lg-9">
                            <?php
                            $durumKey = $icraBilgileri->durum ?? 0;
                            $durumInfo = Helper::Durum[$durumKey] ?? Helper::Durum[0];
                            ?>
                            <span class="badge <?php echo $durumInfo['class']; ?>">
                                <i class="<?php echo $durumInfo['icon']; ?>"></i>
                                <?php echo htmlspecialchars($durumInfo['label']); ?>
                            </span>
                        </div>
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
                                            <th>Taksit Tutarı</th>
                                            <th>Faiz Tutarı (₺)</th>
                                            <th>Toplam Tutar (₺)</th>
                                            <th>Son Ödeme Tarihi</th>
                                            <th>Ödenen Tarih</th>
                                            <th>Durum</th>
                                            <th>İşlem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($icraOdemeler)): ?>
                                            <?php foreach ($icraOdemeler as $odeme): ?>
                                                <tr>
                                                    <td><?= number_format($odeme->taksit_tutari, 2) ?> ₺</td>
                                                    <td><?= number_format($odeme->faiz_tutari, 2) ?> ₺</td>
                                                    <td><?= number_format($odeme->toplam_borc, 2) ?> ₺</td>
                                                    <td><?= Date::dmY($odeme->taksit_odeme_tarihi) ?></td>
                                                    <td><?= !empty($odeme->taksit_odenen_tarih) ? Date::dmY($odeme->taksit_odenen_tarih) : '-' ?></td>
                                                    <td>
                                                        <?php if ($odeme->durumu == 1): ?>
                                                            <span class="badge bg-success">Ödendi</span>
                                                        <?php else: ?>
                                                            <?php
                                                            $today = date("Y-m-d");
                                                            $sonOdeme = $odeme->taksit_odeme_tarihi;
                                                            if ($sonOdeme < $today) {
                                                                $badgeClass = "bg-warning";
                                                                $label = "Gecikmiş";
                                                            } else {
                                                                $badgeClass = "bg-danger";
                                                                $label = "Ödenmedi";
                                                            }
                                                            ?>
                                                            <span class="badge <?= $badgeClass ?>"><?= $label ?></span>
                                                        <?php endif; ?>

                                                    </td>
                                                    <td>
                                                        <div class="d-flex gap-2">
                                                            <button class="btn btn-success btn-sm"
                                                                onclick="updateStatus(this, 'Ödendi', '<?= Security::encrypt($odeme->id) ?>')"
                                                                title="Onayla">
                                                                <i class="feather-check"></i>
                                                            </button>
                                                            <button class="btn btn-danger btn-sm"
                                                                onclick="updateStatus(this, 'Ödenmedi', '<?= Security::encrypt($odeme->id) ?>')"
                                                                title="Red">
                                                                <i class="feather-x"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7">
                                                    <div class="alert alert-warning text-center m-0">
                                                        <i class="feather-info"></i> Ödeme planı oluşturulmamış.
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Dosya Durumu Tab -->
                            <div class="tab-pane fade" id="fileStatus" role="tabpanel">
                                <h5 class="mt-3 mb-3">Dosya Durumu</h5>
                                <p><?= $icraBilgileri->dosya_durumu ?? 'Dosya durumu bilgisi bulunamadı.' ?></p>
                            </div>

                            <!-- İcra Durumu Tab -->
                            <div class="tab-pane fade" id="icraStatus" role="tabpanel">
                                <h5 class="mt-3 mb-3">İcra Durumu</h5>
                                <p><?= $icraBilgileri->icra_durumu ?? 'İcra durumu bilgisi bulunamadı.' ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- /.card-body -->
        </div> <!-- /.card -->
    </div> <!-- /.col-12 -->
</div> <!-- /.row-cards -->
<!-- /.main-content -->

<!-- Ödeme Planı Modal -->
<div class="modal fade" id="paymentPlanModal" tabindex="-1" aria-labelledby="paymentPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentPlanModalLabel">Ödeme Planı Ekle / Güncelle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="odemePlanForm" method="post">
                    <input type="hidden" name="id" id="icra_id" value="<?= Security::encrypt($id) ?? 0; ?>">

                    <!-- Toplam Borç -->
                    <div class="mb-3">
                        <label for="borc_tutari" class="form-label">Toplam Borç (₺)</label>
                        <input type="number" class="form-control" id="borc_tutari" name="borc_tutari" placeholder="Toplam borç tutarını girin" required value="<?= $icraBilgileri->borc_tutari ?? ''; ?>">
                    </div>

                    <!-- Faiz Oranı -->
                    <div class="mb-3">
                        <label for="faiz_orani" class="form-label">Faiz Oranı (%)</label>
                        <input type="number" class="form-control" id="faiz_orani" name="faiz_orani" placeholder="Faiz oranını girin" value="<?= $icraBilgileri->faiz_orani ?? ''; ?>">
                    </div>

                    <!-- Kaç Aya Yayılacak -->
                    <div class="mb-3">
                        <label for="taksit" class="form-label">Kaç Aya Yayılacak?</label>
                        <input type="number" class="form-control" id="taksit" name="taksit" placeholder="Taksit sayısını girin" value="<?= $icraBilgileri->taksit ?? ''; ?>">
                    </div>

                    <!-- Aylık Ödeme -->
                    <div class="mb-3">
                        <label for="aylik_ödeme" class="form-label">Aylık Ödeme (₺)</label>
                        <input type="number" class="form-control" id="aylik_ödeme" name="aylik_ödeme" readonly>
                    </div>

                    <!-- Ödeme Tarihi -->
                    <div class="mb-3">
                        <label for="odeme_baslangic_tarihi" class="form-label">İlk Ödeme Tarihi</label>
                        <input type="text" class="form-control flatpickr" id="odeme_baslangic_tarihi" name="odeme_baslangic_tarihi" value="<?= $icraBilgileri->odeme_baslangic_tarihi ?? ''; ?>">
                    </div>

                    <!-- Açıklama -->
                    <div class="mb-3">
                        <label for="aciklama" class="form-label">Açıklama</label>
                        <textarea class="form-control" id="aciklama" name="aciklama" rows="3" placeholder="Ödeme planıyla ilgili notlar..."><?= $icraBilgileri->aciklama ?? ''; ?></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                <button type="button" class="btn btn-primary" id="odeme_kaydet">Kaydet</button>
            </div>
        </div>
    </div>
</div>
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
                        <textarea class="form-control" id="dosya_durumu" rows="3">Dosya şu anda inceleniyor ve işlemler devam ediyor.</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="icraStatusInput" class="form-label">İcra Durumu</label>
                        <textarea class="form-control" id="icra_durumu" rows="3">İcra takibi başlatıldı ve şu an icra dairesinde işlemler sürmektedir.</textarea>
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
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const borcInput = document.getElementById('borc_tutari');
        const taksitInput = document.getElementById('taksit');
        const aylikOdemeInput = document.getElementById('aylik_ödeme');

        function hesaplaAylikOdeme() {
            const borc = parseFloat(borcInput.value) || 0;
            const taksit = parseInt(taksitInput.value) || 0;

            if (borc > 0 && taksit > 0) {
                const aylikOdeme = borc / taksit;
                aylikOdemeInput.value = aylikOdeme.toFixed(2);
            } else {
                aylikOdemeInput.value = '';
            }
        }

        // Inputlara event ekle
        borcInput.addEventListener('input', hesaplaAylikOdeme);
        taksitInput.addEventListener('input', hesaplaAylikOdeme);

        // Modal açıldığında mevcut değerleri al ve aylık ödemeyi hesapla
        const paymentPlanModal = document.getElementById('paymentPlanModal');
        paymentPlanModal.addEventListener('shown.bs.modal', function() {
            hesaplaAylikOdeme();
        });
    });
</script>


<script>
    function updateStatus(button, status, id) {
        var row = button.closest('tr');

        fetch("/pages/icra/api.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "action=odeme_durum_guncelle&id=" + id + "&status=" + status
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    var statusCell = row.querySelector('td:nth-child(6)');
                    var badge = statusCell.querySelector('span');
                    var paymentDateCell = row.querySelector('td:nth-child(5)');

                    badge.classList.remove('bg-success', 'bg-danger', 'bg-warning');

                    // Son ödeme tarihi al
                    var dueDate = row.querySelector('td:nth-child(4)').textContent;
                    var today = new Date().toISOString().split('T')[0];

                    if (data.durum == 1) {
                        badge.textContent = "Ödendi";
                        badge.classList.add('bg-success');
                        paymentDateCell.textContent = data.taksit_odenen_tarih ?
                            data.taksit_odenen_tarih.split('-').reverse().join('.') :
                            "-";
                    } else {
                        const isLate = dueDate < today;
                        if (isLate) {
                            badge.textContent = "Gecikmiş";
                            badge.classList.add('bg-warning');
                        } else {
                            badge.textContent = "Ödenmedi";
                            badge.classList.add('bg-danger');
                        }
                        paymentDateCell.textContent = "-";
                    }
                } else {
                    alert("Durum güncellenemedi!");
                }
            })
            .catch(err => console.error("Hata:", err));
    }
</script>