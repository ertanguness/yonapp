<?php

use App\Helper\Aidat;
use App\Helper\Date;
use App\Helper\Debit;
use App\Helper\Form;
use App\Helper\Helper;
use App\Helper\Security;
use App\Services\Gate;
use Model\BorclandirmaDetayModel;
use Model\BorclandirmaModel;
use Model\DueDetailModel;
use Model\DueModel;
use Model\KisilerModel;

$DueHelper = new Aidat();
$Borc      = new BorclandirmaModel();
$BorcDetay = new BorclandirmaDetayModel();


Security::ensureSiteSelected();

$DueModel       = new DueModel();
$DueDetailModel = new DueDetailModel();

$KisiModel = new KisilerModel();

//$Auths->checkAuthorize('dues/debit/manage');
$enc_borc_id = $id ?? 0;
$id     = Security::decrypt($id ?? 0) ?? 0;
$borc   = $Borc->find($id) ?? null;

$DebitHelper = new Debit();

//içinde olduğumuz ayın ilk gününü alıyoruz
$baslangic_tarihi = Date::firstDay(
    Date::getMonth(),
    Date::getYear()

);
//içinde olduğumuz ayın son gününü alıyoruz
$bitis_tarihi = Date::lastDay(
    Date::getMonth(),
    Date::getYear()
);

$tanimli_borclandirmalar = $DueDetailModel->getTanimliBorclandirmalar();



//eğer güncelleme yapılıyorsa bazı select'leri disabled yap
if (isset($borc->id) && $borc->id > 0) {
    $disabled = 'disabled';
} else {
    $disabled = '';
}

$hedef_tipi = $borc->hedef_tipi ?? 'all'; // Hedef tipi, eğer borç detayında tanımlı değilse 'all' olarak varsayılır

//Kişi borçlandırma yapılmışsa borçlandırma detayından kişi id'lerini al

switch ($hedef_tipi) {
    case 'all':
        //$hedef_kisi = $DebitHelper->getAllActiveUsers();
        break;
    case 'block':
        //$hedef_kisi = $DebitHelper->getActiveUsersByBlock($borc->block_id ?? 0);
        break;
    case 'apartment_type':
        //$hedef_kisi = $DebitHelper->getActiveUsersByApartmentType($borc->apartment_type_id ?? 0);
        break;
    case 'person':
        $kisiListesi      = $KisiModel->SiteAktifKisileri($_SESSION["site_id"]);
        $optionsForSelect = array_column($kisiListesi, 'adi_soyadi', 'kisi_id');

        $seciliKisiler = $BorcDetay->getKisiIdsByBorcId($id); // Borç detayında tanımlı hedef kişiler

        // Veri tek bir ID olsa bile, onu bir diziye koyun.

        // Eğer veriniz hiç olmayabilirse veya birden çok olabilirse, daha güvenli bir yapı:
        $seciliKisiIdleri = [];
        if (! empty($seciliKisiler)) {
            $seciliKisiIdleri = [(string) $seciliKisiler];
        }

    default:
        $hedef_kisi = [];
}

?>


<div class="page-header">

    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Borç Ekle</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Borçlandırma</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">

            <a href="/borclandirma" type="button" class="btn btn-outline-secondary me-2" data-page="">
                <i class="feather-arrow-left me-2"></i>
                Listeye Dön
            </a>

        </div>
    </div>
</div>



<div class="bg-white py-3 border-bottom rounded-0 p-md-0 mb-0 ">
    <div class="d-md-none d-flex align-items-center justify-content-between">
        <a href="javascript:void(0)" class="page-content-left-open-toggle">
            <i class="feather-align-left fs-20"></i>
        </a>
        <a href="javascript:void(0)" class="page-content-right-open-toggle">
            <i class="feather-align-right fs-20"></i>
        </a>
    </div>
    <div class="d-flex align-items-center justify-content-between">
        <div class="nav-tabs-wrapper page-content-left-sidebar-wrapper">
            <div class="d-flex d-md-none">
                <a href="javascript:void(0)" class="page-content-left-close-toggle">
                    <i class="feather-arrow-left me-2"></i>
                    <span>Back</span>
                </a>
            </div>
            <ul class="nav nav-tabs nav-tabs-custom-style" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tanimliBorclarTab">Tanımlı Borç Tipleri </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#manuelBorclarTab">Manuel Borçlandırma</button>
                </li>

            </ul>
        </div>
        <div class="page-content-right-sidebar-wrapper">
            <div class="d-flex d-md-none">
                <a href="javascript:void(0)" class="page-content-right-close-toggle">
                    <i class="feather-arrow-left me-2"></i>
                    <span>Back</span>
                </a>
            </div>
            <div class="proposal-action-btn">
                <div class="d-md-none d-lg-flex">
                    <a href="javascript:void(0);" class="action-btn" data-bs-toggle="tooltip" title="" data-bs-original-title="Views Trackign">
                        <i class="feather-eye"></i>
                    </a>
                </div>
                <div class="d-md-none d-lg-flex">
                    <a href="javascript:void(0);" class="action-btn" data-bs-toggle="tooltip" title="" data-bs-original-title="Send to Email">
                        <i class="feather-mail"></i>
                    </a>
                </div>
                <div class="d-md-none d-lg-flex">
                    <a href="proposal-edit.html" class="action-btn" data-bs-toggle="tooltip" title="" data-bs-original-title="Edit Proposal">
                        <i class="feather-edit"></i>
                    </a>
                </div>
                <div class="dropdown">
                    <a href="javascript:void(0);" class="action-btn dropdown-toggle c-pointer" data-bs-toggle="dropdown" data-bs-offset="0, 2" data-bs-auto-close="outside">Convert</a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-check-square me-3"></i>
                            <span>Draft</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-dollar-sign me-3"></i>
                            <span>Invoice</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="feather-cast me-3"></i>
                            <span>Estimate</span>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<div class="main-content">
    <?php

    Gate::authorizeOrDie('borclandirma_ekle_sil');
    ?>
    <?php
    /* $title = $pageTitle;
 if ($pageTitle === 'Borç Ekle') {
      $text = "Borç Ekleme sayfasındasınız. Bu sayfada yeni bir borç ekleyebilirsiniz.";
 } else {
      $text = "Borç Güncelleme sayfasındasınız. Bu sayfada borç bilgilerini güncelleyebilirsiniz.";
 }
 require_once 'pages/components/alert.php'; */
    ?>
    <div class="row">

        <div class="tab-content">
            <div class="tab-pane fade active show" id="tanimliBorclarTab">
                <div class="container-xl"></div>
                <div class="card">
                    <div class="card-header">
                        <h5>Tanımlı Borç Tipleri</h5>
                    </div>
                    <div class="card-body aidat-info">
                        <div class="table-responsive">

                            <table class="table table-hover table-center mb-0 datatables table-responsive" id="definedDebitsTable">
                                <thead>
                                    <tr>
                                        <th>Sıra</th>
                                        <th>Borç Başlığı</th>
                                        <th>Kime Borçlandırılacak / Tutar / Gecikme Oranı</th>
                                        <th>Dönemi</th>
                                        <th>Kişi(ler)</th>
                                        <th>Blok</th>
                                        <th>Daire Tipi(leri)</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tanimli_borclandirmalar as $borclandirma):
                                        $enc_id = Security::encrypt($borclandirma->id);
                                        $enc_borc_tipi_id = Security::encrypt($borclandirma->due_id);

                                    ?>
                                        <tr>
                                            <td></td>
                                            <td data-borclandirma-tipi=""><?php echo $borclandirma->due_name; ?></td>
                                            <td><?php echo $borclandirma->borclandirma_tipi_tutar; ?></td>
                                            <td><?php echo Date::dmY($borclandirma->start_date); ?></td>
                                            <td data-kisi-ids="<?php echo $borclandirma->kisi_ids; ?>"><?php echo $borclandirma->kisi_adlari; ?></td>
                                            <td data-blok-ids="<?php echo $borclandirma->blok_ids; ?>"><?php echo $borclandirma->blok_adlari; ?></td>
                                            <td data-daire-tipi-ids="<?php echo $borclandirma->daire_tipi_ids; ?>"><?php echo $borclandirma->daire_tipleri; ?></td>
                                            <td>
                                                <div class="hstack gap-2 ">
                                                    <a href="/borclandirilacak-kisiler/<?php echo $enc_id ?>" class="avatar-text avatar-md">
                                                        <i class="feather-eye"></i>
                                                    </a>
                                                    <a href="#"
                                                        data-borc-adi="<?php echo $borclandirma->due_name; ?>"
                                                        data-borc-tipi-id="<?php echo $enc_borc_tipi_id; ?>"
                                                        data-id="<?php echo $enc_id ?>"
                                                        class="avatar-text avatar-md borclandir">
                                                        <i class="feather-plus"></i>
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

            <div class="tab-pane fade" id="manuelBorclarTab">

                <div class="card">
                    <div class="card-header">
                        <div class="row justify-content-between ">
                            <h5>Tanımlı Borç Tipleri</h5>
                        </div>
                        <button type="button" class="btn btn-primary ms-auto" id="save_debit">
                            <i class="feather-save  me-2"></i>
                            Kaydet
                        </button>
                    </div>
                    <div class="row p-4">
                        <div class="col-lg-12">

                            <div class="alert alert-dismissible d-flex alert-soft-teal-message" role="alert">
                                <div class="me-4 d-none d-md-block">
                                    <i class="feather feather-alert-octagon fs-1"></i>
                                </div>
                                <div>
                                    <p class="fw-bold mb-1 text-truncate-1-line alert-header">Borç Ekleme!</p>
                                    <p class="fs-12 fw-medium text-truncate-1-line alert-description">
                                        Tüm Sakinler seçildiğinde, şu anda sitede oturan <strong>AKTİF</strong> ev sahibi ve
                                        kiracılara
                                        borclandırma yapılacaktır.

                                    </p>

                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="card-body">
                        <form id="debitForm" action="" method="POST">
                            <input type="text" class="form-control d-none" name="borc_id" id="borc_id"
                                value="<?php echo $enc_borc_id ?? 0 ?>">
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-2">
                                    <label for="title" class="fw-semibold">Borç Başlığı:</label>
                                </div>
                                <div class="col-lg-4">
                                    <div class="input-group flex-nowrap w-100">
                                        <div class="input-group-text"><i class="fas fa-file-invoice"></i></div>
                                        <?php echo $DueHelper->AidatTuruSelect("borc_baslik", $borc->borc_tipi_id ?? '', $disabled) ?>

                                    </div>
                                </div>

                                <div class="col-lg-2">
                                    <label for="tutar" class="fw-semibold">Tutar (₺):</label>
                                </div>
                                <div class="col-lg-4">
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="fas fa-money-bill"></i></div>
                                        <input type="text" class="form-control money" name="tutar" id="tutar"
                                            value="<?php echo Helper::formattedMoney($borc->tutar ?? '0'); ?>" placeholder="Tutar giriniz"
                                            required>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-2">
                                    <label for="bitis_tarihi" class="fw-semibold">Dönemi:</label>
                                </div>
                                <div class="col-lg-2">
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="fas fa-calendar-alt"></i></div>
                                        <input type="text" class="form-control flatpickr" name="baslangic_tarihi"
                                            id="baslangic_tarihi"
                                            value="<?php echo Date::dmY($borc->baslangic_tarihi ?? $baslangic_tarihi); ?>"
                                            autocomplete="off" required>
                                    </div>
                                </div>
                                <div class="col-lg-2">

                                    <div class="input-group">
                                        <div class="input-group-text"><i class="fas fa-calendar-alt"></i></div>
                                        <input type="text" class="form-control flatpickr" name="bitis_tarihi"
                                            id="bitis_tarihi"
                                            value="<?php echo Date::dmY($borc->bitis_tarihi ?? $bitis_tarihi); ?>"
                                            autocomplete="off" required>
                                    </div>
                                </div>

                                <div class="col-lg-2">
                                    <label for="ceza_orani" class="fw-semibold">Ceza Oranı (%):</label>
                                    <i class="bi bi-info-circle c-pointer text-primary" data-toggle="tooltip"
                                        data-placement="top"
                                        title="Son Ödeme tarihinden itibaren günlük olarak hesaplanacak ceza oranı(%)"></i>
                                </div>
                                <div class="col-lg-4">
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="fas fa-percentage"></i></div>
                                        <input type="number" class="form-control"
                                            value="<?php echo $borc->ceza_orani ?? 0; ?>" name="ceza_orani" id="ceza_orani"
                                            placeholder="Ceza oranı" step="0.01" min="0">
                                    </div>
                                </div>
                            </div>
                            <style>
                                .select2-results {
                                    height: 400px !important;
                                    max-height: 600px !important;
                                }

                                .select2-container--default .select2-results>.select2-results__options {
                                    height: 400px !important;
                                    max-height: 600px !important;
                                }
                            </style>
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-2">
                                    <label for="hedef_tipi" class="fw-semibold">Kime Borçlandırılacak:</label>
                                </div>
                                <div class="col-lg-4">
                                    <div class="input-group flex-nowrap w-100">
                                        <div class="input-group-text"><i class="fas fa-users"></i></div>
                                        <?php

                                        ?>
                                        <?php echo Helper::targetTypeSelectGrouped('hedef_tipi', $borc->hedef_tipi ?? "all", $disabled); ?>
                                    </div>
                                </div>
                                <div class="col-lg-2 ">
                                    <label for="block_id" class="fw-semibold blok-sec-label">Blok Seç:</label>
                                </div>
                                <div class="col-lg-4">
                                    <div class="input-group flex-nowrap w-100 blok-sec">
                                        <div class="input-group-text"><i class="fas fa-building"></i></div>
                                        <select class="form-control select2-single" name="block_id" id="block_id" disabled>
                                            <option value="">Seçiniz</option>
                                            <?php foreach ($blocks as $block): ?>
                                                <option value="<?php echo $block->id ?>"><?php echo $block->name ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="input-group flex-nowrap w-100 dairetipi-sec d-none">
                                        <div class="input-group-text"><i class="fas fa-building"></i></div>

                                        <?php echo Helper::getApartmentTypesSelect() ?>
                                    </div>
                                </div>

                            </div>
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-2">
                                    <label for="hedef_kisi" class="fw-semibold">Kişi(ler):</label>
                                </div>
                                <div class="col-lg-4">
                                    <div class="input-group flex-nowrap w-100">
                                        <div class="input-group-text"><i class="fas fa-user-friends"></i></div>
                                        <!-- <select name="hedef_kisi[]" id="hedef_kisi" multiple class="form-control select2">
                                        </select> -->
                                        <?php
                                        echo Form::Select2Multiple(
                                            'hedef_kisi[]',              // Form gönderildiğinde PHP'nin dizi olarak alması için name.
                                            $optionsForSelect ?? [],     // SEÇENEKLER: Veritabanından gelen [id => Ad Soyad] dizisi.
                                            $seciliKisiIdleri ?? [],     // SEÇİLİ OLANLAR: Seçili olacak kişi ID'lerini içeren bir DİZİ.
                                            'form-select select2 w-100', // CSS Sınıfı
                                            'hedef_kisi'                 // JavaScript (Select2) için temiz bir ID.
                                        );
                                        ?>

                                    </div>

                                </div>
                                <div class="col-lg-2">
                                    <label for="block" class="fw-semibold">
                                        Gün Bazında:
                                        <i class="bi bi-info-circle  c-pointer text-primary" data-toggle="tooltip"
                                            data-placement="top"
                                            title="Seçili olduğu zaman seçtiğiniz dönem arasındaki, daireye giriş çıkış tarihleri dikkate alınarak hesaplama yapılacaktır."></i>
                                    </label>

                                </div>
                                <div class="col-lg-1">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input " name="day_based"
                                            id="day_based">
                                        <label class="custom-control-label c-pointer text-muted" for="day_based"></label>

                                    </div>
                                </div>
                            </div>
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-2">
                                    <label for="aciklama" class="fw-semibold">Açıklama:</label>
                                </div>
                                <div class="col-lg-10">
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="fas fa-info-circle"></i></div>
                                        <textarea class="form-control" name="aciklama" id="aciklama" rows="3"
                                            placeholder="Açıklama giriniz"><?php echo $borc->aciklama ?? ''; ?></textarea>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Borçlandırma Modalı -->
<div class="modal fade" id="borclandirModal" tabindex="-1" aria-labelledby="borclandirModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="borclandirModalLabel">Borçlandırma Bilgileri</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <form id="borclandirForm">
                    <input type="hidden" class="form-control hidden" name="tanimli_borc_tipi_id" id="tanimli_borc_tipi_id" value="0">
                    <input type="hidden" class="form-control hidden" name="borc_tipi_id" id="borc_tipi_id" value="0">
                    <div class="d-md-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar-text avatar-lg bg-soft-primary text-primary border-soft-primary rounded me-3">
                                <i class="feather-airplay"></i>
                            </div>
                            <div>
                                <a href="javascript:void(0);" id="modal_borc_adi"></a>
                                <p class="fs-12 text-muted mb-0" id="modal_borc_aciklama"></p>
                            </div>
                        </div>

                    </div>
                    <hr class="mb-4 border-dashed">
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="modal_borc_tarihi" class="form-label">Borçlandırma Tarihi</label>
                                <input type="text" class="form-control flatpickr time-input w-100"
                                    id="borclandirma_tarihi" name="borclandirma_tarihi"
                                    value="<?php echo date('01.m.Y 01:00'); ?>"
                                    required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="modal_baslangic_tarihi" class="form-label">Dönem Başlangıç</label>
                                <input type="text" class="form-control flatpickr"
                                    id="modal_baslangic_tarihi" name="baslangic_tarihi"
                                    value="<?php echo date('01.m.Y'); ?>"
                                    required>
                            </div>

                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="modal_bitis_tarihi" class="form-label">Dönem Bitiş</label>
                                <input type="text" class="form-control flatpickr"
                                    id="modal_bitis_tarihi" name="bitis_tarihi"
                                    value="<?php echo date('t.m.Y'); ?>">
                            </div>
                        </div>

                    </div>

                    <div class="mb-3">
                        <label for="modal_aciklama" class="form-label">Açıklama</label>
                        <textarea class="form-control" id="modal_aciklama" name="aciklama" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                <button type="button" class="btn btn-primary" id="modal_borclandir_kaydet">Kaydet</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {

        $('.flatpickr.time-input').flatpickr({
            dateFormat: "d.m.Y H:i",
            enableTime: true,
            time_24hr: true,
            minuteIncrement: 1
        });

        $('.select2').select2({
            // templateResult'ı daha kompakt bir fonksiyonla tanımla
            templateResult: function(option) {
                // Arama kutusu gibi öğeleri atla
                if (!option.id) {
                    return option.text;
                }
                // data-description özniteliğini al
                var description = $(option.element).data('description');

                // Eğer açıklama varsa, başlığın altına küçük ve gri bir şekilde ekle
                if (description) {
                    return $(`<span>${option.text}<br><small style="color:#888;">${description}</small></span>`);
                }

                // Açıklama yoksa sadece başlığı döndür
                return option.text;
            }
        });
    });

    $(document).on('click', '.borclandir', function() {
        // Örnek: Borç adı butonun data-borc-adi attribute'undan alınabilir
        var borcAdi = $(this).data('borc-adi') || '';
        var borcId = $(this).data('id') || '';
        var borc_tipi_id = $(this).data('borc-tipi-id') || '';


        $('#modal_borc_adi').text(borcAdi);
        $('#tanimli_borc_tipi_id').val(borcId);
        $('#borc_tipi_id').val(borc_tipi_id);
        //Borç açıklamasını doldur
        //Başlangıç tarihindeki ay adını al
        var currentDate = new Date();
        var monthNames = [
            "Ocak", "Şubat", "Mart", "Nisan", "Mayıs", "Haziran",
            "Temmuz", "Ağustos", "Eylül", "Ekim", "Kasım", "Aralık"
        ];
        var monthName = monthNames[currentDate.getMonth()];
        var year = currentDate.getFullYear();

        // Borç açıklamasını doldur
        $('#modal_aciklama').text(monthName + ' ' + year + ' ' + borcAdi);

        // Diğer alanları da isterseniz doldurabilirsiniz
        $('#borclandirModal').modal('show');
    });

    $('#modal_borclandir_kaydet').on('click', function() {
        // Form verilerini al
        var form = $('#borclandirForm');
        var formData = new FormData(form[0]);

        formData.append('action', 'tanimli_borc_ekle');
        formData.append('baslangic_tarihi', $('#modal_baslangic_tarihi').val());
        formData.append('bitis_tarihi', $('#modal_bitis_tarihi').val());

        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }

        fetch('pages/dues/debit/api.php', {
                method: 'POST',
                body: formData
            }).then(response => response.json())
            .then(data => {
                console.log(data);
                let title = data.status === 'success' ? 'Başarılı!' : 'Hata!';
                swal.fire({
                    title: title,
                    text: data.message,
                    icon: data.status,
                })



            })



    });
$(document).ready(function() {
    // Flatpickr örneğini al
    var baslangicTarihiFlatpickr = flatpickr('#modal_baslangic_tarihi', {
        dateFormat: "d.m.Y",
        locale: "tr", // locale for this instance only
        dateFormat: "d.m.Y",
        onChange: function(selectedDates, dateStr, instance) {
            if (!dateStr) return;
            
            var tarihParcalari = dateStr.split('.');
            var gun = tarihParcalari[0];
            var ay = tarihParcalari[1];
            var yil = tarihParcalari[2];

            // Ayın son gününü hesapla
            var sonGun = new Date(yil, ay, 0).getDate();

            // Bitiş tarihini ayın son günü olarak ayarla
            var bitisTarihi = sonGun + '.' + ay + '.' + yil;
            console.log('Yeni Bitiş Tarihi:', bitisTarihi);
            $('#modal_bitis_tarihi').val(bitisTarihi);

            // Açıklama kısmını da güncelle
            var monthNames = [
                "Ocak", "Şubat", "Mart", "Nisan", "Mayıs", "Haziran",
                "Temmuz", "Ağustos", "Eylül", "Ekim", "Kasım", "Aralık"
            ];
            var monthName = monthNames[parseInt(ay) - 1];
            $('#modal_aciklama').val(monthName + ' ' + yil + ' ' + 'AİDAT');
        }
    });
});

</script>