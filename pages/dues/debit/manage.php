<?php

use App\Helper\Form;
use App\Helper\Date;
use App\Helper\Aidat;
use App\Helper\Helper;
use App\Helper\Security;
use Model\KisilerModel;
use Model\BorclandirmaModel;
use Model\BorclandirmaDetayModel;
use App\Helper\Debit;

use App\Services\Gate;


$DueHelper = new Aidat();
$Borc = new BorclandirmaModel();
$BorcDetay = new BorclandirmaDetayModel();

$KisiModel = new KisilerModel();


//$Auths->checkAuthorize('dues/debit/manage');

$id = Security::decrypt($id ?? 0) ?? 0;
$borc = $Borc->find($id) ?? null;

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
        $kisiListesi = $KisiModel->SiteAktifKisileri($_SESSION["site_id"]);
        $optionsForSelect = array_column($kisiListesi, 'adi_soyadi', 'kisi_id');
        
        $seciliKisiler = $BorcDetay->getKisiIdsByBorcId($id); // Borç detayında tanımlı hedef kişiler

        // Veri tek bir ID olsa bile, onu bir diziye koyun.

        // Eğer veriniz hiç olmayabilirse veya birden çok olabilirse, daha güvenli bir yapı:
        $seciliKisiIdleri = [];
        if (!empty($seciliKisiler)) {
            $seciliKisiIdleri = [(string)$seciliKisiler];
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
            <button type="button" class="btn btn-primary" id="save_debit">
                <i class="feather-save  me-2"></i>
                Kaydet
            </button>
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
        <div class="container-xl">

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
                <div class="card-body">
                    <form id="debitForm" action="" method="POST">
                        <input type="text" class="form-control d-none" name="borc_id" id="borc_id"
                            value="<?php echo $_GET["id"] ?? 0 ?>">
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
                                        value="<?php echo  $borc->tutar ?? '0,00'; ?>" placeholder="Tutar giriniz"
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

                        <div class="row mb-4 align-items-center">
                            <div class="col-lg-2">
                                <label for="hedef_tipi" class="fw-semibold">Kime Borçlandırılacak:</label>
                            </div>
                            <div class="col-lg-4">
                                <div class="input-group flex-nowrap w-100">
                                    <div class="input-group-text"><i class="fas fa-users"></i></div>
                                    <?php

                                    ?>
                                    <?php echo Helper::targetTypeSelect('hedef_tipi', $borc->hedef_tipi ?? "all", $disabled); ?>
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
                                        <option value="<?= $block->id ?>"><?= $block->name ?></option>
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
                                            'hedef_kisi[]',         // Form gönderildiğinde PHP'nin dizi olarak alması için name.
                                            $optionsForSelect ?? [],           // SEÇENEKLER: Veritabanından gelen [id => Ad Soyad] dizisi.
                                            $seciliKisiIdleri ?? [],      // SEÇİLİ OLANLAR: Seçili olacak kişi ID'lerini içeren bir DİZİ.
                                            'form-select select2 w-100', // CSS Sınıfı
                                            'hedef_kisi'            // JavaScript (Select2) için temiz bir ID.
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

<script>
$(document).ready(function() {
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
</script>