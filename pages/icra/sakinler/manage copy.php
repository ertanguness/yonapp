<?php
//$site_id = $_SESSION['site_id'];

use App\Helper\Date;
use App\Helper\Helper;
use Model\KisilerModel;
use Model\IcraModel;
use App\Helper\Security;
use Model\IcraOdemeModel;

$kisilerModel = new KisilerModel();
$Icra = new IcraModel();
$IcraOdeme = new IcraOdemeModel();

//$kisiler = $kisilerModel->SiteTumKisileri($site_id);
//$id = isset($_GET['id']) ? Security::decrypt($_GET['id']) : 0;
$id = Security::decrypt($id ?? 0);

$icraBilgileri = $Icra->IcraBilgileri($id);
$icraOdemeler = $IcraOdeme->IcraOdemeBilgileri($id);

?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">İcra Dosyası Detayı</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="/ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item"><a href="icra-dosyalarim.php">İcra Dosyalarım</a></li>
            <li class="breadcrumb-item">Dosya Detayı</li>
        </ul>
    </div>
</div>

<div class="main-content">
    <div class="container-xl">

        <!-- Dosya Bilgileri -->
        <div class="card mb-4">
            <div class="card-body">
                <h5>Dosya Bilgileri</h5>
                <div class="row mb-2">
                    <div class="col-md-2 fw-semibold">İcra Dairesi:</div>
                    <div class="col-md-4">
                        <?= htmlspecialchars($icraBilgileri->icra_dairesi ?? '-') ?>
                    </div>
                    <div class="col-md-2 fw-semibold">Dosya No:</div>
                    <div class="col-md-4">
                        <?= htmlspecialchars($icraBilgileri->dosya_no ?? '-') ?>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-2 fw-semibold">İcra Tarihi:</div>
                    <div class="col-md-4">
                        <?= !empty($icraBilgileri->icra_baslangic_tarihi) ? Date::dmY($icraBilgileri->icra_baslangic_tarihi) : '-' ?>
                    </div>
                    <div class="col-md-2 fw-semibold">İcra Durumu:</div>
                    <div class="col-md-4">
                        <?php
                        $durum = $icraBilgileri->durum ?? 0;
                        $durumBilgi = Helper::Durum[$durum] ?? Helper::Durum[0];
                        ?>
                        <span class="badge <?= $durumBilgi['class']; ?>">
                            <i class="<?= $durumBilgi['icon']; ?>"></i>
                            <?= htmlspecialchars($durumBilgi['label']); ?>
                        </span>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-md-2 fw-semibold">Toplam Borç:</div>
                    <div class="col-md-4">
                        <?= $icraBilgileri->borc_tutari ?? 0 ?> ₺
                    </div>
                    
                    <div class="col-md-2 fw-semibold">Açıklama:</div>
                    <div class="col-md-10">
                        <?= htmlspecialchars($icraBilgileri->aciklama ?? '-') ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Online Ödeme Alanı -->
        <div class="card mb-4">
            <div class="tab-pane fade show active " id="paymentPlan" role="tabpanel">
                <table class="table text-center table-hover">
                    <thead style="background-color:antiquewhite;">
                        <tr>
                            <th>Taksit No</th>
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
                            <td>1</td>
                            <td>1.000 ₺</td>
                            <td>10%</td>
                            <td>12.500 ₺</td>
                            <td>2025-07-01</td>
                            <td>-</td>
                            <td><span class="badge bg-danger">Ödenmedi</span></td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#paymentModal">
                                        Öde </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-body text-center">
                <h5>Kalan Borç: <span class="text-danger">7.500 ₺</span></h5>
                <p>Ödemelerinizi aşağıdaki yöntemlerle gerçekleştirebilirsiniz:</p>
                <div class="alert alert-info mt-3" role="alert">
                    <strong>Havale Bilgileri</strong> <br>
                    <strong>Banka:</strong> XYZ Bankası <br>
                    <strong>IBAN:</strong> TR00 0000 0000 0000 0000 00 <br>
                    <strong>Alıcı:</strong> Site Yönetimi A.Ş.
                </div>

            </div>
        </div>

        <!-- Ödeme Geçmişi -->
        <div class="card mb-4">
            <div class="tab-pane fade show active " id="paymentHistory" role="tabpanel">
                <table class="table text-center table-hover">
                    <thead style="background-color:lightblue;">
                        <tr>
                            <th>Taksit No</th>
                            <th>Ödeme Tutarı</th>
                            <th>Ödeme Tarihi</th>
                            <th>Durum</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>2.500 ₺</td>
                            <td>2025-04-10</td>
                            <td><span class="badge bg-success">Ödendi</span></td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>2.500 ₺</td>
                            <td>2025-05-10</td>
                            <td><span class="badge bg-success">Ödendi</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-body text-center">
                <h5>Toplam Ödenen Tutar: <span class="text-success">5.000 ₺</span></h5>
                <p>Ödeme geçmişiniz yukarıda listelenmiştir.</p>
            </div>
        </div>

    </div> <!-- /.container-xl -->
</div> <!-- /.main-content -->

<!-- Ödeme Modalı -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="paymentForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Sanal POS ile Ödeme</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kart Üzerindeki İsim</label>
                        <input type="text" class="form-control" name="card_name" placeholder="Ad Soyad" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kart Numarası</label>
                        <input type="text" class="form-control" name="card_number" id="card_number" maxlength="19" placeholder="0000 0000 0000 0000" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Son Kullanma Tarihi</label>
                            <input type="text" class="form-control" name="card_expiry" id="card_expiry" maxlength="5" placeholder="AA/YY" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">CVV</label>
                            <input type="text" class="form-control" name="card_cvv" id="card_cvv" maxlength="4" placeholder="123" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ödenecek Tutar</label>
                        <input type="text" class="form-control" name="amount" value="7.500 ₺" readonly>
                    </div>

                    <div class="alert alert-warning small mt-3" role="alert">
                        Bu ödeme sayfası test amaçlıdır. Gerçek ödeme için sanal POS kurulumu gereklidir.
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">
                        <i data-feather="credit-card" class="me-2"></i> Ödemeyi Tamamla
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Ödeme Başarılı Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center">
            <div class="modal-body p-5">
                <i class="text-success" data-feather="check-circle" style="width: 80px; height: 80px;"></i>
                <h4 class="mt-3">Ödeme Başarılı!</h4>
                <p class="mb-4">Ödemeniz başarıyla tamamlandı. Teşekkür ederiz.</p>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript: Otomatik Formatlama ve Başarı Modalı -->
<script>
    document.getElementById('card_number').addEventListener('input', function(e) {
        let input = e.target.value.replace(/\D/g, '').substring(0, 16);
        let sections = [];
        for (let i = 0; i < input.length; i += 4) {
            sections.push(input.substring(i, i + 4));
        }
        e.target.value = sections.join(' ');
    });

    document.getElementById('card_expiry').addEventListener('input', function(e) {
        let input = e.target.value.replace(/\D/g, '').substring(0, 4);
        if (input.length >= 3) {
            input = input.substring(0, 2) + '/' + input.substring(2);
        }
        e.target.value = input;
    });

    document.getElementById('card_cvv').addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '').substring(0, 4);
    });

    // Form Submit: Ödeme Simülasyonu
    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
        paymentModal.hide();

        setTimeout(function() {
            var successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();
        }, 500); // Modal kapanıp açılırken küçük gecikme
    });
</script>