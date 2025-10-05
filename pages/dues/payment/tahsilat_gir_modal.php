<?php
require_once dirname(__DIR__, levels: 3) . '/configs/bootstrap.php';


use App\Helper\Security;
use App\Helper\Date;
use App\Helper\Helper;
use App\Helper\FinansalHelper;
use App\Helper\Aidat;
use Model\DairelerModel;
use Model\KisilerModel;
use Model\KisiKredileriModel;
use Model\BorclandirmaDetayModel;
use Model\FinansalRaporModel;


use App\Services\Gate;

Gate::authorizeOrDie(
    permissionName: 'tahsilat_ekle_sil',
    customMessage: 'Bu sayfayı görüntüleme yetkiniz yok!',
    redirectUrl: false
);


$Aidat = new Aidat();
$Daire = new DairelerModel();
$Kisiler = new KisilerModel();
$KisiKredi = new KisiKredileriModel();
$BorcDetay = new BorclandirmaDetayModel();
$FinansalRapor = new FinansalRaporModel();

$id = Security::decrypt($_GET['kisi_id']) ?? 0;


//$borclandirmalar = $BorcDetay->KisiBorclandirmalari($id);

$kisi_guncel_borclar = $FinansalRapor->getKisiGuncelBorclar($id);
$kredi = $KisiKredi->getKullanilabilirKrediByKisiId($id) ?? 0;

// Kullanıcının finansal durumunu al
$finansalDurum = $FinansalRapor->getKisiGuncelBorcOzet($id);


//kişinin bakiyesini getir
$bakiye = $finansalDurum->guncel_borc;


$enc_id = Security::decrypt($_GET["id"] ?? 0);
$kisi_id = $_GET["kisi_id"] ?? 0;

$kisi = $Kisiler->find($kisi_id, true);

if (!$kisi) {
    echo '<div class="alert alert-danger">Kişi bulunamadı.</div>';
    exit;
}

//Toplam Borcun Yüzdelik hesaplanması
$kisi_finans = $BorcDetay->KisiFinansalDurum(Security::decrypt($kisi_id));



?>


<div class="row">
                <!-- Overlay (Modal içi) -->

    <div class="hstack justify-content-between border border-dashed rounded-3 p-3 mb-3">
        <div class="hstack gap-3">
            <div class="avatar-image">
                <img src="assets/images/avatar/1.png" alt="" class="img-fluid">
            </div>
            <div>
                <a href="javascript:void(0);">
                    <h5 class="mb-0"><?php echo $kisi->adi_soyadi ?></h5>
                </a>
                <div class="fs-11 text-muted">

                    <?php echo $Daire->DaireKodu($kisi->daire_id) ?>
                    <h6 class="fs-14 text-truncate-1-line">Toplam Borç
                        <span class="text-dark fw-medium">:

                            <?php echo Helper::formattedMoney($bakiye); ?>
                        </span>
                    </h6>


                </div>
            </div>
        </div>

        <div class="hstack justify-content-between gap-4">
            <div class="cursor-pointer">
                <h6 class="fs-14 text-truncate-1-line">Kullanılabilir Kredi</h6>
                <div class="fs-14 text-muted kredi-kullan" data-kredi="<?php echo $kredi ?>"><span class="text-dark fw-medium">Kullan :</span>
                    <?php echo Helper::formattedMoney($kredi); ?> </div>
            </div>
            <div class="d-column">

                <h6 class="fs-14 text-truncate-1-line">Kullanılacak Kredi</h6>
                <div class="fs-14">
                    <input type="text" class="form-control w-50" id="kullanilacak_kredi" name="kullanilacak_kredi"
                        value="0">
                </div>
            </div>


        </div>

        <div class="float-end text-end">

            <div>
                <a href="javascript:void(0);" class="fw-bold" id="secilen-tahsilat-tutari">0,00 TL</a>
            </div>
            <div>
                <span class="fs-12 text-muted">Seçilen Toplam Borç</span>
            </div>

        </div>
    </div>
</div>
<div class="row">


    <div class="col-lg-6">

        <div class="card widget-tickets-content">
            <div class="card-header">
                <h5 class="card-title">Borçlar</h5>
                <div class="card-header-action">
                    <div class="card-header-btn">

                        <div data-bs-toggle="tooltip" title="" data-bs-original-title="Refresh">
                            <a href="#" class="avatar-text avatar-xs bg-warning" data-bs-toggle="refresh"> </a>
                        </div>
                        <div data-bs-toggle="tooltip" title="" data-bs-original-title="Maximize/Minimize">
                            <a href="#" class="avatar-text avatar-xs bg-success" data-bs-toggle="expand"> </a>
                        </div>
                    </div>
                    <div class="dropdown">
                        <a href="#" class="avatar-text avatar-sm" data-bs-toggle="dropdown" data-bs-offset="25, 25">
                            <div data-bs-toggle="tooltip" title="" data-bs-original-title="Options">
                                <i class="feather-more-vertical"></i>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-at-sign"></i>New</a>
                            <a href="javascript:void(0);" class="dropdown-item"><i
                                    class="feather-calendar"></i>Event</a>
                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-bell"></i>Snoozed</a>
                            <a href="javascript:void(0);" class="dropdown-item"><i
                                    class="feather-trash-2"></i>Deleted</a>
                            <div class="dropdown-divider"></div>

                        </div>
                    </div>
                </div>
            </div>

            <style>
                .tickets-sort-desc {
                    min-height: 20px;
                    align-items: bottom;
                }
            </style>
            <div class="overflow-auto tasks-items-wrapper" style="height: 400px;">
                <div class="card-body custom-card-action p-0">
                    <div class="table-responsive tickets-items-wrapper">
                        <table class="table table-hover mb-0">
                            <tbody>
                                <?php foreach ($kisi_guncel_borclar as $borc): ?>
                                    <tr class="borc-satiri" data-borc-id="<?= Security::encrypt($borc->id) ?>">
                                        <td style="width:4%;">
                                            <div class="avatar-text bg-gray-100">
                                                <a href="javascript:void(0);">
                                                    <?php echo Helper::getInitials($borc->borc_adi); ?>
                                                </a>
                                            </div>

                                        </td>
                                        <td>
                                            <a href="javascript:void(0);"><?php echo $borc->borc_adi ?> <span
                                                    class="fs-12 fw-normal text-muted"><?= " Son Ödeme : " . $borc->bitis_tarihi ?></span>
                                            </a>
                                            <p class="fs-12 text-muted text-truncate-1-line tickets-sort-desc">
                                                <?php echo $borc->aciklama; ?>
                                            </p>
                                            <div class="tickets-list-action d-flex align-items-center gap-3">

                                                <a href="javascript:void(0);" class="tahsilat-islem-btn"
                                                    data-action="ekle">Ekle</a>

                                            </div>
                                        </td>
                                        <td class="text-end" style="width: 35%;">
                                            <a href="javascript:void(0);"
                                                class="fw-bold d-block"><?php echo Helper::formattedMoney($borc->kalan_anapara); ?></a>
                                            <span class="fs-12 text-danger">
                                                <?php echo "G. Zammı : " . Helper::formattedMoney($borc->hesaplanan_gecikme_zammi); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach ?>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


            <!-- Kayıt yok ise  -->
            <?php if (empty($kisi_guncel_borclar)): ?>
                <div class="text-center text-muted">
                    <p>Kayıt Bulunamadı!!!</p>

                </div>
            <?php endif ?>



        </div>
    </div>
    <div class="col-md-6">
        <div class="card widget-tickets-content">
            <div class="card-header">
                <h5 class="card-title">Tahsilat Bilgileri</h5>
                <div class="card-header-action">
                    <div class="card-header-btn">

                        <div data-bs-toggle="tooltip" title="" data-bs-original-title="Refresh">
                            <a href="#" class="avatar-text avatar-xs bg-warning" data-bs-toggle="refresh"> </a>
                        </div>
                        <div data-bs-toggle="tooltip" title="" data-bs-original-title="Maximize/Minimize">
                            <a href="#" class="avatar-text avatar-xs bg-success" data-bs-toggle="expand"> </a>
                        </div>
                    </div>
                    <div class="dropdown">
                        <a href="#" class="avatar-text avatar-sm" data-bs-toggle="dropdown" data-bs-offset="25, 25">
                            <div data-bs-toggle="tooltip" title="" data-bs-original-title="Options">
                                <i class="feather-more-vertical"></i>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-at-sign"></i>New</a>
                            <a href="javascript:void(0);" class="dropdown-item"><i
                                    class="feather-calendar"></i>Event</a>
                            <a href="javascript:void(0);" class="dropdown-item"><i class="feather-bell"></i>Snoozed</a>
                            <a href="javascript:void(0);" class="dropdown-item"><i
                                    class="feather-trash-2"></i>Deleted</a>
                            <div class="dropdown-divider"></div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body custom-card-action">
                <div class="notes-box">
                    <div class="notes-content">
                        <form action="javascript:void(0);" id="tahsilatForm">
                            <input type="text" name="tahsilat_id" value="<?= $enc_id ?>" hidden>
                            <input type="text" name="kisi_id" value="<?= $kisi_id ?>" hidden>
                            <div class="row">

                                <div class="col-lg-6 mb-3">
                                    <label class="form-label">Tutar</label>
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="feather-credit-card"></i></div>
                                        <input type="text" class="form-control money" name="tutar" id="tutar" value=""
                                            placeholder="₺ 0,00" required>
                                    </div>
                                </div>
                                <div class="col-lg-6 mb-3">
                                    <label class="form-label">Tarih</label>
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="feather-calendar"></i></div>
                                        <input type="text" class="form-control flatpickr" name="islem_tarihi" id="islem_tarihi"
                                            value="<?php echo date("d.m.Y H:i") ?>">
                                    </div>
                                </div>
                                <div class="col-lg-12 mb-3">
                                    <label class="form-label">Kasa(*) <span class="text-muted">Tahsilatın İşleneceği
                                            Kasa</span></label>
                                    <div class="input-group flex-nowrap w-100">
                                        <div class="input-group-text">
                                            <i class="feather-briefcase"></i>
                                        </div>
                                        <?php echo FinansalHelper::KasaSelect('kasa_id')  ?>
                                    </div>
                                </div>



                                <div class="col-md-12">
                                    <label class="form-label">Açıklama</label>
                                    <div class="input-group flex-nowrap mb-3">
                                        <div class="input-group-text">
                                            <i class="feather-file-text"></i>
                                        </div>
                                        <textarea id="note-has-description" name="tahsilat_aciklama"
                                            class="form-control" placeholder="Açıklama giriniz.(Referans No vb."
                                            rows="5"></textarea>

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

<script>
    // $(document).on("focus", ".flatpickr", function() {
    //     $(this).inputmask("99.99.9999", {
    //         placeholder: "gg.aa.yyyy",
    //         clearIncomplete: true,

    //     });
    // });

    $(function() {


    })

    $(document).on("focus", ".money", function() {
        $(this).inputmask("decimal", {
            radixPoint: ",",
            groupSeparator: ".",
            prefix: "₺ ",
            digits: 2,
            autoGroup: true,
            rightAlign: false,
            removeMaskOnSubmit: true,
        });
    });
</script>