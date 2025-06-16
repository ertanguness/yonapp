<?php
session_start();
require_once '../../../vendor/autoload.php';

use App\Helper\Security;
use App\Helper\Date;

use App\Helper\Financial;
use App\Helper\Aidat;

$Aidat = new Aidat();
$enc_id = Security::decrypt($_GET["id"] ?? 0);
?>

<div class="notes-box">
    <div class="notes-content">
        <form action="javascript:void(0);" id="tahsilatForm">
            <input type="text" name="tahsilat_id" value="<?= $enc_id ?>" hidden >
            <div class="row">
                <div class="hstack justify-content-between border border-dashed rounded-3 p-3 mb-3">
                    <div class="hstack gap-3">
                        <div class="avatar-image">
                            <img src="assets/images/avatar/1.png" alt="" class="img-fluid">
                        </div>
                        <div>
                            <a href="javascript:void(0);">Alexandra Della</a>
                            <div class="fs-11 text-muted">Frontend Developer</div>
                        </div>
                    </div>
                    <div class="team-progress-1" role="progressbar" aria-valuemin="0" aria-valuemax="100"
                        aria-valuenow="40"><svg version="1.1" width="100" height="100" viewBox="0 0 100 100"
                            class="circle-progress">
                            <circle class="circle-progress-circle" cx="50" cy="50" r="47" fill="none" stroke="#ddd"
                                stroke-width="8"></circle>
                            <path d="M 50 3 A 47 47 0 0 1 77.62590685774623 88.02379873562253"
                                class="circle-progress-value" fill="none" stroke="#00E699" stroke-width="8"></path><text
                                class="circle-progress-text" x="50" y="50" font="16px Arial, sans-serif"
                                text-anchor="middle" fill="#999" dy="0.4em">40%</text>
                        </svg></div>
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
                        <input type="text" class="form-control money" name="tutar" value="0">
                    </div>
                </div>
                <div class="col-lg-6 mb-3">
                    <label class="form-label">Tarih</label>
                    <div class="input-group">
                        <div class="input-group-text"><i class="feather-calendar"></i></div>
                        <input type="text" class="form-control flatpickr" name="islem_tarihi"
                            value="<?php echo date("d.m.Y")?>">
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