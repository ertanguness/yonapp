<?php 
use App\Helper\Date;
?>

<form id="personelForm">
    <input type="hidden" name="personelId" id="personelId" value="<?php echo $id ?? 0 ?>">

    <div class="card-body">
        <div class="row mb-4 align-items-center">
            <div class="col-lg-2">
                <label for="personnelName" class="fw-semibold">Ad Soyad:</label>
            </div>
            <div class="col-lg-4">
                <div class="input-group">
                    <div class="input-group-text"><i class="fas fa-user"></i></div>
                    <input type="text" class="form-control" name="adi_soyadi" id="adi_soyadi" placeholder="Ad Soyad Giriniz"
                        value="<?php echo $personel->adi_soyadi ?? '' ?>">
                </div>
            </div>
            <div class="col-lg-2">
                <label for="personnelPhone" class="fw-semibold">Telefon Numarası:</label>
            </div>
            <div class="col-lg-4">
                <div class="input-group">
                    <div class="input-group-text"><i class="fas fa-phone"></i></div>
                    <input type="text" class="form-control" name="telefon" id="telefon" placeholder="Telefon Giriniz"
                        value="<?php echo $personel->telefon ?? '' ?>">
                </div>
            </div>
        </div>
        <div class="row mb-4 align-items-center">
            <div class="col-lg-2">
                <label for="personnelEmail" class="fw-semibold">E-Posta:</label>
            </div>
            <div class="col-lg-4">
                <div class="input-group">
                    <div class="input-group-text"><i class="fas fa-envelope"></i></div>
                    <input type="email" class="form-control" name="email" id="email" placeholder="E-Posta Giriniz"
                        value="<?php echo $personel->email ?? '' ?>">
                </div>
            </div>
            <div class="col-lg-2">
                <label for="personel_tipi" class="fw-semibold">Pozisyon:</label>
            </div>
            <div class="col-lg-4">
                <div class="input-group">
                    <div class="input-group-text"><i class="fas fa-briefcase"></i></div>
                    <input type="text" class="form-control" name="personel_tipi" id="personel_tipi" placeholder="Pozisyon Giriniz"
                        value="<?php echo $personel->personel_tipi ?? '' ?>">

                </div>
            </div>
        </div>

        <div class="row mb-4 align-items-center">
            <div class="col-lg-2">
                <label for="gorev_baslama_tarihi" class="fw-semibold">İşe Başlama Tarihi:</label>
            </div>
            <div class="col-lg-4">
                <div class="input-group">
                    <div class="input-group-text"><i class="fas fa-calendar"></i></div>
                    <input type="text" class="form-control flatpickr" name="ise_baslama_tarihi" id="ise_baslama_tarihi"
                        value="<?php echo Date::dmY($personel->ise_baslama_tarihi) ?? '' ?>">
                </div>
            </div>

            <div class="col-lg-2">
                <label for="isten_ayrilma_tarihi" class="fw-semibold">İşten Ayrılma Tarihi:</label>
            </div>
            <div class="col-lg-4">
                <div class="input-group">
                    <div class="input-group-text"><i class="fas fa-calendar"></i></div>
                    <input type="text" class="form-control flatpickr" name="isten_ayrilma_tarihi" id="isten_ayrilma_tarihi"
                        value="<?php echo Date::dmY($personel->isten_ayrilma_tarihi) ?? '' ?>">
                </div>
            </div>
        </div>
    </div>
</form>
<script src="/pages/personel/js/personInfo.js?<?= filemtime("pages/personel/js/personInfo.js") ?>"></script>