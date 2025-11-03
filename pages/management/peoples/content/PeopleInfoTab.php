<?php
$site_id = $_SESSION['site_id'] ?? 0;

use App\Helper\Date;
use App\Helper\Helper;
use Model\BloklarModel;
use Model\DairelerModel;
use Model\KisilerModel;
use Psr\Log\Test\DummyTest;

$Block = new BloklarModel();
$Daireler = new DairelerModel();
$Kisiler = new KisilerModel();


$enc_id = $id ?? 0;
$blocks = $Block->SiteBloklari($site_id);
$kisi = $Kisiler->KisiBilgileri($id);
$daireler = $Daireler->BlokDaireleri($kisi->blok_id ?? 0);

//echo "<pre>"; var_dump($kisi); echo "</pre>";exit;
?>
<div class="card-body people-info">

    <!-- TC Kimlik No / Pasaport No ve Konut Sakini Türü -->
    <div class="row mb-4 align-items-center">

        <div class="col-lg-2">
            <label for="fullName" class="fw-semibold">Ad Soyad:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group flex-nowrap w-100">
                <div class="input-group-text"><i class="fas fa-user"></i></div>
                <input type="text" class="form-control" id="fullName" name="fullName" placeholder="Adı Soyadı Giriniz" value="<?php echo $kisi->adi_soyadi ?? ''; ?>">
            </div>
        </div>

        <div class="col-lg-2">
            <label for="phoneNumber" class="fw-semibold">Telefon Numarası:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text"><i class="fas fa-phone"></i></div>
                <input type="text" class="form-control" id="phoneNumber" name="phoneNumber" placeholder="Telefon Numarası Giriniz" value="<?php echo $kisi->telefon ?? ''; ?>">
            </div>
            
        </div>

    </div>


    <!-- Blok Adı ve Daire No -->
    <div class="row mb-4 align-items-center">
        <div class="col-lg-2">
            <label for="blokAdi" class="fw-semibold">Blok Adı:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group flex-nowrap w-100">
                <div class="input-group-text"><i class="fas fa-building"></i></div>
                <select class="form-select select2 w-100 blokAdi" name="blok_id">
                    <option value="">Blok Seçiniz</option>
                    <?php foreach ($blocks as $block): ?>
                        <option value="<?= htmlspecialchars($block->id) ?>"
                            <?= (isset($kisi->blok_id) && $kisi->blok_id == $block->id) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($block->blok_adi) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="col-lg-2">
            <label for="daireNo" class="fw-semibold">Daire No:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group flex-nowrap w-100">
                <div class="input-group-text"><i class="fas fa-door-closed"></i></div>
                <select class="form-select select2 w-100 daireNo" name="daire_id">
                    <option value="">Daire Seçiniz</option>
                    <?php if (!empty($daireler)) : ?>
                        <?php foreach ($daireler as $daire): ?>
                            <option value="<?= $daire->id ?>" <?= ($kisi->daire_id == $daire->id) ? 'selected' : '' ?>>
                                <?= $daire->daire_no ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>

            </div>
        </div>
    </div>
    <!-- Satın Alma Tarihi -->
    <div class="row mb-4 align-items-center">

        <div class="col-lg-2">
            <label for="residentType" class="fw-semibold">Konut Sakini Türü:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group flex-nowrap w-100">
                <div class="input-group-text"><i class="fas fa-user"></i></div>
               <?php echo Helper::ikametTuruSelect('residentType', $kisi->uyelik_tipi ?? '0'); ?>
            </div>
            <small id="sakinTürü" class="form-text text-muted">
            </small>
        </div>
        <div class="col-lg-2">
            <label for="buyDate" class="fw-semibold">Satın Alma Tarihi:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text"><i class="fas fa-shopping-cart"></i></div>
                <input type="text" class="form-control flatpickr" id="buyDate" name="buyDate" placeholder="Satın Alma Tarihi Giriniz" disabled value="<?php echo $kisi->satin_alma_tarihi ?? ''; ?>">
            </div>
            <small id="buyDateHelp" class="form-text text-muted">
                Sadece Kat Maliki seçildiğinde aktif olur.
            </small>
        </div>

    </div>
    <!-- Giriş Tarihi / Satın Alma Tarihi ve Çıkış Tarihi -->
    <div class="row mb-4 align-items-center">
        <div class="col-lg-2">
            <label for="entryDate" class="fw-semibold">Giriş Tarihi:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text"><i class="fas fa-calendar-check"></i></div>
                <input type="text" class="form-control flatpickr" id="entryDate" autocomplete="off" name="entryDate" placeholder="Giriş Tarihi Giriniz" 
                value="<?php echo Date::dmY($kisi->giris_tarihi ?? null)  ?? ''; ?>">
            </div>
            <small id="buyDateHelp" class="form-text text-muted">
                Kişi kayıt yaptığında tarih girilmelidir. </small>
        </div>

        <div class="col-lg-2">
            <label for="exitDate" class="fw-semibold">Çıkış Tarihi: </label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text"><i class="fas fa-calendar-times"></i></div>
                <input type="text" class="form-control flatpickr" id="exitDate" name="exitDate" placeholder="Çıkış Tarihi Giriniz" value="<?php echo Date::dmY($kisi->cikis_tarihi ?? '') ; ?>">
            </div>
            <small id="buyDateHelp" class="form-text text-muted">
                Kişi çıkış yaptığında tarih girilmelidir. </small>
        </div>
    </div>


    <!-- Doğum Bilgileri -->
    <div class="row mb-4 align-items-center">

        <div class="col-lg-2">
            <label for="tcPassportNo" class="fw-semibold">TC Kimlik No / Pasaport No:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text"><i class="fas fa-id-card"></i></div>
                <input type="text" class="form-control" id="tcPassportNo" name="tcPassportNo" placeholder="TC Kimlik No veya Pasaport No Giriniz" maxlength="11" value="<?php echo $kisi->kimlik_no ?? ''; ?>">
            </div>
        </div>


        <div class="col-lg-2">
            <label for="gender" class="fw-semibold">Cinsiyet:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group flex-nowrap w-100">
                <div class="input-group-text"><i class="fas fa-venus-mars"></i></div>
                <select class="form-select select2 w-100" id="gender" name="gender">
                    <option value="">Cinsiyet Seçiniz</option>
                    <option value="E" <?= (isset($kisi->cinsiyet) && $kisi->cinsiyet === 'E') ? 'selected' : '' ?>>Erkek</option>
                    <option value="K" <?= (isset($kisi->cinsiyet) && $kisi->cinsiyet === 'K') ? 'selected' : '' ?>>Kadın</option>
                </select>
            </div>
        </div>
    </div>

    <!-- İletişim Bilgileri -->
    <div class="row mb-4 align-items-center">

        <div class="col-lg-2">
            <label for="birthDate" class="fw-semibold">Doğum Tarihi:</label>
        </div>

        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text"><i class="fas fa-calendar-alt"></i></div>
                <input type="text" class="form-control flatpickr" id="birthDate" name="birthDate" placeholder="Doğum Tarihi Giriniz" value="<?php echo Date::dmY($kisi->dogum_tarihi ?? null)  ?? ''; ?>">
            </div>
        </div>

        <div class="col-lg-2">
            <label for="email" class="fw-semibold">E-Posta Adresi:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text"><i class="fas fa-envelope"></i></div>
                <input type="email" class="form-control" id="email" name="email" placeholder="E-posta Adresi Giriniz" value="<?php echo $kisi->eposta ?? ''; ?>">
            </div>
        </div>
    </div>
    <div class="row mb-4 align-items-center">

        <div class="col-lg-2">
            <label for="status" class="fw-semibold">Kullanım Durumu:</label>
        </div>
        <div class="col-lg-4 ms-3">
            <div class="form-check form-switch d-flex align-items-center">
                <input class="form-check-input" type="checkbox" id="kullanim_durumu" name="kullanim_durumu" style="transform: scale(2.0);"
                    data-kullanim="<?= isset($kisi->kullanim_durumu) ? (int)$kisi->kullanim_durumu : 0 ?>"
                    <?= (!empty($kisi->kullanim_durumu) && $kisi->kullanim_durumu != 0) ? 'checked' : '' ?>>
                <label class="form-check-label ms-4" for="status"></label>
                <small id="kullanimDurumu" class="form-text text-muted">
                Kişinin İlgili Bağımsız bölümü aktif olarak kullanıp kullanmama durumunu belirtir. </small>
            </div>
            
        </div>
    </div>
</div>
<!-- Konut sakin türüne göre satın alma tarihi aktif etme -->
<script>
    $(document).ready(function() {
        function toggleBuyDateField() {
            const val = $('#residentType').val();
            if (val === '1') {
                $('#buyDate').prop('disabled', false);
                $('#buyDateHelp').show();
            } else {
                $('#buyDate').prop('disabled', true);
                $('#buyDateHelp').show(); // istersen hide() da olabilir
                $("#buyDate").val('');
            }
        }

        // Sayfa yüklendiğinde kontrol et
        toggleBuyDateField();

        // Değişiklik olduğunda tekrar kontrol et
        $('#residentType').on('change', toggleBuyDateField);
    });
</script>


<script>
    $(document).ready(function() {
        // Telefon numarasına maske uygula (Türkiye GSM formatı)
        $("#phoneNumber").inputmask({
            mask: "0(999) 999 99 99",
            placeholder: "0(___) ___ __ __",
            showMaskOnHover: false,
            showMaskOnFocus: true,
            clearIncomplete: true
        });

        // TC Kimlik veya Pasaport alanı için otomatik maske
        $("#tcPassportNo").on("input", function() {
            let value = $(this).val();

            // Eğer sadece rakamsa ve uzunluğu 11'e kadar çıkıyorsa TC Kimlik varsayılır
            if (/^\d*$/.test(value)) {
                $(this).inputmask("99999999999"); // 11 haneli TC
            } else {
                $(this).inputmask('remove'); // Pasaport için maske kaldırılır
                // Harf ve rakam dışındaki karakterleri sil, maksimum 9 karakter
                this.value = value.replace(/[^a-zA-Z0-9]/g, "").slice(0, 9);
            }
        });
    });
</script>