<?php

use App\Helper\FinansalHelper;
use App\Helper\KisiHelper;
use App\Helper\Security;
use App\Helper\Helper;
use App\Services\Gate;
use Model\KasaModel;
use Model\KasaHareketModel;


Gate::authorizeOrDie("income_expense_add_update");

$Kasa = new KasaModel();
$KasaHareket = new KasaHareketModel();
$KisiHelper = new KisiHelper();

// Kasa ID'yi belirle
$kasa_id = null;

//1. Önce session'dan kontrol et
if (isset($_SESSION["kasa_id"]) && !empty($_SESSION["kasa_id"])) {
    $kasa_id = $_SESSION["kasa_id"];
}

// 2. POST ile yeni seçim geldi mi?
if (isset($_POST['kasalar'])) {
    $kasa_id = Security::decrypt($_POST['kasalar']) ?? 0;
    $_SESSION["kasa_id"] = $kasa_id;
         //Helper::dd(($kasa_id));
  
    echo "<script>history.replaceState({}, '', '/gelir-gider-islemleri');</script>";
    $id = null;
}

// 3. URL parametresi var mı?
if (isset($id) && !empty($id)) {
    $kasa_id = Security::decrypt($id);
    $_SESSION["kasa_id"] = $kasa_id;
}

// 4. Hiçbiri yoksa varsayılan kasayı al
if (!$kasa_id) {
    try {
        $varsayilanKasa = $Kasa->varsayilanKasa();
        $kasa_id = $varsayilanKasa ? $varsayilanKasa->id : 1;
        $_SESSION["kasa_id"] = $kasa_id;
    } catch (Exception $e) {
        // Hata durumunda fallback
        $kasa_id = 1;
        $_SESSION["kasa_id"] = $kasa_id;
    }
}

// echo "id " . ($_SESSION['kasa_id'] ?? 0);

$KasaFinansalDurum = $Kasa->KasaFinansalDurum($kasa_id);
$kasa_hareketleri = $KasaHareket->getKasaHareketleri($kasa_id);




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
            <div>
                <form method="post" id="kasalar">
                    <?php echo FinansalHelper::KasaSelect("kasalar", $kasa_id) ?>
                </form>
            </div>
            <div class="dropdown">
                <a class="btn btn-icon btn-light-brand" data-bs-toggle="dropdown" data-bs-offset="0, 10" data-bs-auto-close="outside" aria-expanded="false">
                    <i class="feather-paperclip"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end" style="">
                    <a href="javascript:void(0);" class="dropdown-item export" data-format="pdf">
                        <i class="bi bi-filetype-pdf me-3"></i>
                        <span>PDF</span>
                    </a>
                    <a href="javascript:void(0);" class="dropdown-item export" data-format="csv">
                        <i class="bi bi-filetype-csv me-3"></i>
                        <span>CSV</span>
                    </a>
                    <a href="javascript:void(0);" class="dropdown-item export" data-format="xml">
                        <i class="bi bi-filetype-xml me-3"></i>
                        <span>XML</span>
                    </a>
                    <a href="javascript:void(0);" class="dropdown-item export" data-format="txt">
                        <i class="bi bi-filetype-txt me-3"></i>
                        <span>Text</span>
                    </a>
                    <a href="javascript:void(0);" class="dropdown-item export" id="exportExcel" data-format="excel">
                        <i class="bi bi-filetype-exe me-3"></i>
                        <span>Excel</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="javascript:void(0);" class="dropdown-item export" data-format="print">
                        <i class="bi bi-printer me-3"></i>
                        <span>Print</span>
                    </a>
                </div>
            </div>

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
                        <h5 class="fs-4" id="toplamGelir"><?php echo Helper::formattedMoney($KasaFinansalDurum->toplam_gelir ?? 0); ?></h5>
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
                        <h5 class="fs-4" id="toplamGider"><?php echo Helper::formattedMoney($KasaFinansalDurum->toplam_gider ?? 0); ?></h5>
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
                        <h5 class="fs-4" id="netKalan"><?php echo Helper::formattedMoney(($KasaFinansalDurum->bakiye ?? 0)); ?></h5>
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
                        <table id="gelirGiderTable" class="table table-hover table-bordered datatables">
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
                                <?php
                                $i = 0;
                                foreach ($kasa_hareketleri as $hareket):
                                    $i++;
                                    $enc_id = Security::encrypt($hareket->id);
                                    $badge = $hareket->islem_tipi == 'gelir' ? 'success' : 'danger';
                                    $gelirGiderGuncelle = $hareket->guncellenebilir == 1  ? "gelirGiderGuncelle" : 'GuncellemeYetkisiYok';
                                    $gelirGiderSil = $hareket->guncellenebilir == 1  ? "gelirGiderSil" : 'SilmeYetkisiYok';

                                ?>
                                    <tr>
                                        <td><?php echo $i; ?></td>
                                        <td class="text-center"><?php echo $hareket->islem_tarihi; ?></td>
                                        <td class="text-center"><span class="badge bg-<?php echo $badge; ?>"><?php echo $hareket->islem_tipi; ?></span></td>
                                        <td><?php echo Helper::getOdemeKategori($hareket->kategori); ?></td>
                                        <td class="text-left" style="width: 30%;"><?php echo $hareket->aciklama; ?></td>
                                        <td class="text-success text-end"><?php echo Helper::formattedMoney($hareket->tutar); ?></td>
                                        <td>
                                            <div class="hstack gap-2 justify-content-center">
                                                <a href="#" class="avatar-text avatar-md <?php echo $gelirGiderGuncelle; ?>" data-id="<?php echo $enc_id; ?>">
                                                    <i class="feather-edit"></i>
                                                </a>
                                                <a href="#" class="avatar-text avatar-md <?php echo $gelirGiderSil; ?>" data-id="<?php echo $enc_id; ?>">
                                                    <i class="feather-trash-2"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Liste Tablosu Bitiş -->
</div>

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



                    <!-- İşleme Tarihi -->
                    <div class="mb-3">
                        <div class="row">

                            <div class="col-md-6">

                                <label for="islem_tarihi" class="form-label">İşlem Tarihi *</label>
                                <input type="text" class="form-control flatpickr" name="islem_tarihi" id="islem_tarihi" required
                                    value="<?= date('d-m-Y H:i'); ?>">
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
                        <?php echo Helper::getOdemeKategoriSelect("kategori",6) ?>

                    </div>
                    <div class="mb-3 kisiler d-none">
                        <label for="kisiler" class="form-label">Daire Sakini *</label>
                        <?php echo $KisiHelper->KisiSelect("kisiler") ?>

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
    $(function() {

        var myModalEl = document.getElementById('gelirGiderModal')
        myModalEl.addEventListener('hidden.bs.modal', function(event) {
            $("#islem_id").val(0);
            $('#gelirGiderForm')[0].reset();
        })

        //modali kapatınca sayfayı yenile
        $('#gelirGiderModal').on('hidden.bs.modal', function() {
            //location.reload();
        });

        //#kasalar'da değişiklik olduğunda
        $("#kasalar").on("change", function() {
            //kasalar formunu submit et
            $("#kasalar").submit();
        });

        $("#kategori").on("change", function() {
            if($(this).val() == '1')
                $(".kisiler").removeClass("d-none").fadeIn(500);
            else{
                $(".kisiler").addClass("d-none").fadeOut(500);
            }
        });

        //flatpickr
        $("#islem_tarihi").flatpickr({
            dateFormat: "d.m.Y H:i",
            locale: "tr",
            enableTime: true,
        })


        $(".modal .select2").select2({
            dropdownParent: $("#gelirGiderModal")
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