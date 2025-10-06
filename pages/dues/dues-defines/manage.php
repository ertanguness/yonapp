<?php

use Model\DueModel;
use App\Helper\Date;
use App\Helper\Form;
use App\Helper\Helper;
use App\Helper\Security;
use App\Helper\BlokHelper;
use Model\KisilerModel;
use Mpdf\Tag\Pre;

$Dues = new DueModel();
$BlokHelper = new BlokHelper();
$KisiModel = new KisilerModel();

// Yeni eklemelerde 0 olarak gönderilmesi gerekir
$enc_id = $id ?? 0;
$id = Security::decrypt($id ?? 0) ?? 0;
$due = $Dues->find($id ?? null);

//Kime borçlandırılacak
$hedef_tipi = $due->borclandirma_tipi ?? 'all'; // Hedef tipi, eğer borç detayında tanımlı değilse 'all' olarak varsayılır



if($id == 0 ){
    //blok_id, hedef_kisi[], disabled olaak
    $disabled_blok_id = "disabled";
    $disabled_hedef_kisi = "disabled";

}


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


        //Kişi borçlandırma yapılmışsa borçlandırma detayından kişi id'lerini al 
        $kisiListesi = $KisiModel->SiteAktifKisileri($_SESSION["site_id"]);
        //column_key' i daire_kodu | adi_soyadi şeklinde yaz
        $optionsForSelect = array_map(function ($kisi) {
            return [
                'kisi_id' => $kisi->kisi_id,
                'label' => $kisi->daire_kodu . ' | ' . $kisi->adi_soyadi
            ];
        }, $kisiListesi);

        $optionsForSelect = array_column($optionsForSelect, 'label', 'kisi_id');


        //borclandirilacak kişiler $due->borclandirilacaklar
        $borclandirilacaklarRaw = $due->borclandirilacaklar ?? '[]';

        // JSON decode et
        $seciliKisiler = json_decode($borclandirilacaklarRaw, true);

        // Decode başarısızsa veya null geldiyse dizi yap
        if (!is_array($seciliKisiler)) {
            // Tek bir ID düz string olarak kaydedilmiş olabilir
            if (is_numeric($seciliKisiler) || (is_string($seciliKisiler) && trim($seciliKisiler) !== '')) {
                $seciliKisiler = [$seciliKisiler];
            } else {
                $seciliKisiler = [];
            }
        }

        // Bütün ID’leri string’e normalize et (Form helper çoğunlukla string karşılaştırır)
        $seciliKisiIdleri = array_map(fn($v) => (string)$v, $seciliKisiler);


    default:
        $hedef_kisi = [];
}






?>


<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Aidat Tanımlama</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home/list">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Aidat Yönetimi</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex d-md-none">
                <a href="javascript:void(0)" class="page-header-right-close-toggle">
                    <i class="feather-arrow-left me-2"></i>
                    <span>Back</span>
                </a>
            </div>
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">

                <a href="/aidat-turu-listesi" class="btn btn-outline-secondary route-link me-2">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </a>
                <button type="button" class="btn btn-primary" id="save_dues">
                    <i class="feather-save  me-2"></i>
                    Kaydet
                </button>
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
    <div class="card">


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
    </div>
    <form id="duesForm" method="POST">
        <input type="hidden" name="dues_id" id="dues_id" value="<?php echo $enc_id ?? 0; ?>">
        <div class="row">
            <div class="container-xl">
                <div class="card">
                    <div class="card-header">
                        <h5>Aidat Bilgileri</h5>
                    </div>
                    <div class="card-body aidat-info">

                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-2">
                                <label for="block" class="fw-semibold">Tür Adı:</label>
                            </div>
                            <div class="col-lg-4">
                                <div class="input-group flex-nowrap w-100">
                                    <div class="input-group-text"><i class="fas fa-building"></i></div>
                                    <input type="text" class="form-control" name="due_name" id="due_name"
                                        placeholder="Aidat/Borç adı Giriniz" value="<?php echo $due->due_name ?? ''; ?>" required>
                                </div>
                            </div>

                            <div class="col-lg-2">
                                <label for="status" class="fw-semibold">Durum:</label>
                            </div>
                            <div class="col-lg-4">
                                <div class="input-group flex-nowrap w-100">
                                    <div class="input-group-text"><i class="fas fa-toggle-on"></i></div>
                                    <?php echo Helper::StateSelect("state", $due->state ?? 1); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-2">
                                <label for="description" class="fw-semibold">Açıklama:</label>
                            </div>
                            <div class="col-lg-10">
                                <textarea class="form-control" name="description" id="description"
                                    placeholder="Açıklama Giriniz"
                                    rows="3"><?php echo $due->description ?? ''; ?></textarea>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-5">
            <div class="container-xl">
                <div class="card">
                    <div class="card-header">
                        <h5>Aidat Ayarları</h5>
                    </div>
                    <div class="card-body aidat-info">
                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-2">
                                <label for="start_date" class="fw-semibold">Başlangıç/Bitiş Tarihi:</label>
                            </div>
                            <div class="col-lg-2">
                                <div class="input-group">
                                    <div class="input-group-text"><i class="fas fa-calendar-alt"></i></div>
                                    <input type="text" class="form-control flatpickr" name="start_date" id="start_date"
                                        required value="<?php echo Date::dmY($due->start_date ?? ''); ?>">
                                </div>
                            </div>

                            <div class="col-lg-2">
                                <div class="input-group">
                                    <div class="input-group-text"><i class="fas fa-calendar-alt"></i></div>
                                    <input type="text" class="form-control flatpickr" name="end_date" id="end_date"
                                        value="<?php echo Date::dmY($due->end_date ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <label for="amount" class="fw-semibold">Aidat Tutarı/ Ceza Oranı:</label>
                            </div>
                            <div class="col-lg-2">
                                <div class="input-group">
                                    <div class="input-group-text"><i class="fas fa-money-bill"></i></div>
                                    <input type="text" class="form-control money" name="amount" id="amount"
                                        placeholder="Aidat Tutarı Giriniz" value="<?php echo Helper::formattedMoneyWithoutCurrency($due->amount ?? 0) ?? ''; ?>"
                                        required>
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="input-group">
                                    <div class="input-group-text"><i class="fas fa-percentage"></i></div>
                                    <input type="number" class="form-control" name="penalty_rate" id="penalty_rate"
                                        placeholder="Ceza Oranı Giriniz" value="<?php echo $due->penalty_rate ?? ''; ?>"
                                        required step="0.01" min="0">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4 align-items-center">




                        </div>

                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-2">
                                <label for="block" class="fw-semibold">Periyodu:</label>
                            </div>
                            <div class="col-lg-4">
                                <div class="input-group flex-nowrap w-100">
                                    <div class="input-group-text"><i class="fas fa-building"></i></div>
                                    <?php echo Helper::PeriodSelect(); ?>
                                </div>
                            </div>

                            <div class="col-lg-2">
                                <label for="block" class="fw-semibold">Otomatik Yenileme:</label>
                            </div>
                            <div class="col-lg-1">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input " name="auto_renew"
                                        id="auto_renew" <?php echo isset($_POST['auto_renew']) ? 'checked' : ''; ?>>
                                    <label class="custom-control-label c-pointer text-muted" for="auto_renew"></label>

                                </div>
                            </div>



                        </div>
                        <div class="row mb-4 align-items-center auto-renew d-none">
                              <div class="col-lg-2">
                                <label for="block" class="fw-semibold">Her dönemin </label>
                            </div>
                            <div class="col-lg-4 d-flex align-items-center">
                                <input type="number" class="form-control" name="day_of_period" id="day_of_period"
                                    placeholder="Örn:Ayın 1. günü" value="<?php echo $due->day_of_period ?? 1; ?>"
                                    min="1" max="28"> 

                            </div>

                        </div>

                        <div class="row mb-4 align-items-center auto-renew d-none">

                            <div class="col-lg-2">
                                <label for="hedef_tipi" class="fw-semibold">Kime Borçlandırılacak:</label>
                            </div>
                            <div class="col-lg-4">
                                <div class="input-group flex-nowrap w-100">
                                    <div class="input-group-text"><i class="fas fa-users"></i></div>
                                    <?php

                                    ?>
                                    <?php echo Helper::targetTypeSelect('hedef_tipi', $borc->hedef_tipi ?? "all"); ?>
                                </div>
                            </div>
                            <div class="col-lg-2 ">
                                <label for="block_id" class="fw-semibold blok-sec-label">Blok Seç:</label>
                            </div>
                            <div class="col-lg-4">
                                <div class="input-group flex-nowrap w-100 blok-sec">
                                    <div class="input-group-text"><i class="fas fa-building"></i></div>
                                    <select class="form-control select2" multiple name="block_id[]" id="block_id">
                                     
                                    </select>
                                </div>

                                <div class="input-group flex-nowrap w-100 dairetipi-sec d-none">
                                    <div class="input-group-text"><i class="fas fa-building"></i></div>

                                    <?php echo Helper::getApartmentTypesSelect("apartment_type") ?>
                                </div>
                            </div>

                        </div>
                        <div class="row mb-4 align-items-center auto-renew d-none">
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
                                        'hedef_kisi[]',         // Form gönderildiğinde PHP'nin dizi olarak alması için name.
                                        $optionsForSelect ?? [],           // SEÇENEKLER: Veritabanından gelen [id => Ad Soyad] dizisi.
                                        $seciliKisiIdleri ?? [],      // SEÇİLİ OLANLAR: Seçili olacak kişi ID'lerini içeren bir DİZİ.
                                        'form-select select2 w-100', // CSS Sınıfı
                                        'hedef_kisi'            // JavaScript (Select2) için temiz bir ID.
                                    );
                                    ?>

                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </form>
</div>

<script>
    $(document).ready(function() {
        $('#period').on('change', function() {
            var period = $(this).val();
            switch (parseInt(period)) {
                case 0: // Aylık
                case 1: // 3 Aylık
                case 2: // 6 Aylık
                case 3: // Yıllık
                    $('#day_based, #auto_renew').prop('checked', false);
                    $('#day_based, #auto_renew').prop('disabled', false);
                    break;
                default: // Tek Seferlik
                    $('#day_based, #auto_renew').prop('checked', false);
                    $('#day_based, #auto_renew').prop('disabled', true);

            }
        })
    })

    $('#auto_renew').on('change', function() {
        if ($(this).is(':checked')) {
            $('.auto-renew').removeClass('d-none');
        } else {
            $('.auto-renew').addClass('d-none');
        }
    });
</script>