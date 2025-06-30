<?php
session_start();
require_once '../../../vendor/autoload.php';

use App\Helper\Security;
use App\Helper\Date;
use App\Helper\Helper;
use App\Helper\Financial;
use App\Helper\Aidat;
use Model\DairelerModel;
use Model\KisilerModel;
use Model\BorclandirmaDetayModel;
use Model\Auths;



$Aidat = new Aidat();
$Daire = new DairelerModel();
$Kisiler = new KisilerModel();
$BorcDetay = new BorclandirmaDetayModel();
$Auths = new Auths();

$Auths->checkPermissionAlert('tahsilat_ekle_sil');

$enc_id = Security::decrypt($_GET["id"] ?? 0);
$kisi_id = $_GET["kisi_id"] ?? 0;

$kisi = $Kisiler->find($kisi_id, true);

if (!$kisi) {
    echo '<div class="alert alert-danger">Kişi bulunamadı.</div>';
    exit;
}

//Toplam Borcun Yüzdelik hesaplanması
$kisi_finans = $BorcDetay->KisiFinansalDurum(Security::decrypt($kisi_id));

$toplam_borc = $kisi_finans->toplam_borc ?? 0;
$toplam_odeme = $kisi_finans->toplam_odeme ?? 0;

//Eğer borcu varsa yüzdelik hesapla
$odeme_yuzdesi = $toplam_borc > 0 ? round(($toplam_odeme / $toplam_borc) * 100, 2) : 0;

// Eğer yüzdelik 100'den büyükse 100 olarak ayarla
// Eğer toplam borç 0 ve toplam ödeme 0'dan büyükse yüzdelik 100 olarak ayarla
if ($odeme_yuzdesi > 100 || $toplam_borc == 0 && $toplam_odeme > 0) {
    $odeme_yuzdesi = 100;
}

//rengi kırmızdan yeşile doğru değişen bir renk için dinamik renk oluştur
$color = sprintf('#%02X%02X%02X', 255 - (int)(2.55 * $odeme_yuzdesi), (int)(2.55 * $odeme_yuzdesi), 0);

//Bakiyeyi tutar alanına atamak için mutlak değere çevrilmiş bakiye
$tutar = abs($kisi_finans->bakiye ?? 0);


?>

<div class="notes-box">
    <div class="notes-content">
        <form action="javascript:void(0);" id="tahsilatForm">
            <input type="text" name="tahsilat_id" value="<?= $enc_id ?>" hidden>
            <input type="text" name="kisi_id" value="<?= $kisi_id ?>" hidden>
            <div class="row">
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
                            </div>
                        </div>
                    </div>
                    <style>
                        .team-progress-1 .circle-progress-value {
                            stroke: <?php echo $color; ?>;
                            /* Dinamik renk */
                        }
                    </style>

                    <div class="team-progress-1"
                        role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="64">
                        <svg version="1.1" width="100" height="100" viewBox="0 0 100 100" class="circle-progress"
                            accesskey="">
                            <!-- Arka plan çemberi -->
                            <circle class="circle-progress-circle" cx="50" cy="50" r="47" fill="none" stroke="#ddd" stroke-width="8"></circle>

                            <!-- Tamamlanma oranını gösteren çember -->
                            <circle
                                class="circle-progress-value"
                                cx="50"
                                cy="50"
                                r="47"
                                fill="none"
                                stroke="<?php echo $color; ?>"
                                stroke-width="8"
                                stroke-dasharray="295.31"
                                stroke-dashoffset="<?php echo 295.31 * (1 - $odeme_yuzdesi / 100); ?>"
                                transform="rotate(-90 50 50)"></circle>

                            <!-- Yüzde metni -->
                            <text class="circle-progress-text" x="50" y="50" font="16px Arial, sans-serif" text-anchor="middle" fill="#999" dy="0.4em">
                                <?php echo $odeme_yuzdesi; ?>%
                            </text>
                        </svg>
                    </div>
                </div>

                <div class="col-lg-12 mb-3">
                    <label class="form-label">Kategori(*) </label>
                    <div class="input-group flex-nowrap w-100">
                        <div class="input-group-text">
                            <i class="feather-briefcase"></i>
                        </div>
                        <?php echo $Aidat->AidatTuruSelect('tahsilat_turu')  ?>
                    </div>
                </div>


                <div class="col-lg-6 mb-3">
                    <label class="form-label">Tutar</label>
                    <div class="input-group">
                        <div class="input-group-text"><i class="feather-credit-card"></i></div>
                        <input type="text" class="form-control money" name="tutar" value="<?php echo Helper::formattedMoneyWithoutCurrency($tutar) ?>"
                            placeholder="₺ 0,00" required>
                    </div>
                </div>
                <div class="col-lg-6 mb-3">
                    <label class="form-label">Tarih</label>
                    <div class="input-group">
                        <div class="input-group-text"><i class="feather-calendar"></i></div>
                        <input type="text" class="form-control flatpickr" name="islem_tarihi"
                            value="<?php echo date("d.m.Y") ?>">
                    </div>
                </div>
                <div class="col-lg-12 mb-3">
                    <label class="form-label">Kasa(*) <span class="text-muted">Tahsilatın İşleneceği Kasa</span></label>
                    <div class="input-group flex-nowrap w-100">
                        <div class="input-group-text">
                            <i class="feather-briefcase"></i>
                        </div>
                        <?php echo Financial::KasaSelect('kasa_id')  ?>
                    </div>
                </div>



                <div class="col-md-12">
                    <label class="form-label">Açıklama</label>
                    <div class="input-group flex-nowrap mb-3">
                        <div class="input-group-text">
                            <i class="feather-file-text"></i>
                        </div>
                        <textarea id="note-has-description" name="tahsilat_aciklama" class="form-control"
                            placeholder="Açıklama giriniz.(Referans No vb." rows="5"></textarea>

                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).on("focus", ".flatpickr", function() {
        $(this).inputmask("99.99.9999", {
            placeholder: "gg.aa.yyyy",
            clearIncomplete: true,
        });
    });
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