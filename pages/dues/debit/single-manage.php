<?php

use App\Helper\Date;
use App\Helper\Aidat;
use App\Helper\Helper;
use App\Helper\Security;
use Model\KisilerModel;
use Model\BorclandirmaModel;
use Model\BorclandirmaDetayModel;
use App\Helper\Debit;
use App\Helper\Error;
use App\Helper\Form;

use App\Services\Gate;
use Random\Engine\Secure;

$DebitHelper = new Debit();
$DueHelper = new Aidat();
$KisiModel = new KisilerModel();
$BorcModel = new BorclandirmaModel();
$BorcDetayModel = new BorclandirmaDetayModel();



$borc_id = Security::decrypt($_GET["id"] ?? 0) ?? 0;
$borc_detay_id = Security::decrypt($_GET["borc_detay_id"] ?? 0) ?? 0;


$borc = $BorcModel->find($borc_id);
$borc_detay = $BorcDetayModel->BorclandirmaDetayByID($borc_id);

// if (!$borc_detay) {
//     header("Location: index?p=dues/debit/list");
//     exit;
// }

// 
$adi_soyadi = $borc_detay->adi_soyadi ?? '';
$borc_adi = $borc_detay->borc_adi ?? '';




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
        
        if ($borc_detay_id != 0) {

            $seciliKisiler = $BorcDetayModel->getKisiIdsByBorcId($borc_id); // Borç detayında tanımlı hedef kişiler

            // Veri tek bir ID olsa bile, onu bir diziye koyun.

            // Eğer veriniz hiç olmayabilirse veya birden çok olabilirse, daha güvenli bir yapı:
            $seciliKisiIdleri = [];
            if (!empty($seciliKisiler)) {
                $seciliKisiIdleri = [(string)$seciliKisiler];
            }
        }
        // echo "<pre>";
        // print_r($kisiListesi);
        // echo "</pre>";



    default:
        $hedef_kisi = [];
}







Gate::authorizeOrDie('debit_add');
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

            <a href="index?p=dues/debit/detail&id=<?php echo Security::encrypt($borc_id) ?>" type="button"
                class="btn btn-outline-secondary me-2" data-page="">
                <i class="feather-arrow-left me-2"></i>
                Listeye Dön
            </a>
            <button type="button" class="btn btn-primary" id="save_debit_single">
                <i class="feather-save  me-2"></i>
                Kaydet
            </button>
        </div>
    </div>
</div>

<div class="main-content">


    <div class="row">
        <div class="container-xl">
            <div class="col-xxl-12 single-note-item all-category note-important note-tasks">
                <div class="card card-body mb-4 stretch stretch-full">
                    <span class="side-stick"></span>
                    <h5 class="note-title text-truncate w-75 mb-1">
                        <?php echo $adi_soyadi . " - " .  $borc_adi ?>
                    </h5>

                    <div class="fs-12 mt-3">
                        <div class="hstack gap-2 text-muted mb-2">
                            <div class="avatar-text avatar-sm">
                                <i class="feather-calendar"></i>
                            </div>
                            <span class="text-truncate-1-line">
                                Dönemi :
                                <strong><?php echo Date::dmY($borc_detay->baslangic_tarihi) . "  -  " .  Date::dmY($borc_detay->bitis_tarihi)  ?>
                                </strong>
                            </span>
                        </div>
                        <div class="hstack gap-2 text-muted mb-2">
                            <div class="avatar-text avatar-sm">
                                <i class="feather-list"></i>
                            </div>
                            <span class="text-truncate-1-line">
                                Yapılan borçlandırmanın tutarını ve açıklamasını düzenleyebilirsiniz.
                            </span>
                        </div>


                    </div>

                </div>


            </div>
            <div class="card">

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
                                    <?php echo $DueHelper->AidatTuruSelect("borc_baslik", $borc->borc_tipi_id ?? '', "disabled") ?>

                                </div>
                            </div>

                            <div class="col-lg-2">
                                <label for="tutar" class="fw-semibold">Tutar (₺) / Ceza Oranı (%):</label>
                            </div>
                            <div class="col-lg-2">
                                <div class="input-group">
                                    <div class="input-group-text"><i class="fas fa-money-bill"></i></div>
                                    <input type="text" class="form-control money" name="tutar" id="tutar"
                                        value="<?php echo Helper::formattedMoney($borc_detay->tutar ?? 0) ?? ''; ?>"
                                        placeholder="Tutar giriniz" required>
                                </div>
                            </div>

                            <div class="col-lg-2">
                                <div class="input-group">
                                    <div class="input-group-text"><i class="fas fa-percentage"></i></div>
                                    <input type="number" class="form-control" name="ceza_orani" id="ceza_orani"
                                        placeholder="Ceza oranı" step="0.01" min="0"
                                        value="<?php echo $borc_detay->ceza_orani ?? ''; ?>">
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
                                    <?php echo Helper::targetTypeSelect('hedef_tipi', $borc->hedef_tipi ?? "all"); ?>
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

                                    <?php echo Helper::getApartmentTypesSelect($site_id) ?>
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
                                        placeholder="Açıklama giriniz"><?php echo $borc_detay->aciklama ?? $borc->aciklama; ?></textarea>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>