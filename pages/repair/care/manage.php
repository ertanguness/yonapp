<?php

use Model\BloklarModel;
use Model\PeriyodikBakimModel;
use App\Helper\Security;

$Bakimlar = new PeriyodikBakimModel();
$Bloklar = new BloklarModel();

// -------------Bakım ve Arıza Takip Sistemi için talep numarası oluşturma------------
$bugün = date('Ymd'); // Örn: 20250724
$talepNo = "P" . $bugün . '-' . $Bakimlar->PeriyodikBakimSonID()['last_id'];
// ------------------------------------------------------------------------------

$id = Security::decrypt($id ?? 0);
$bakim = $Bakimlar->PeriyodikBakimBilgileri($id);
$Blok = $Bloklar->SiteBloklari($_SESSION['site_id']);
?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10"> Bakım ve Arıza Takip </h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Periyodik Bakım Takip</li>
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

                <a href="/periyodik-bakim" class="btn btn-outline-secondary route-link me-2">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </a>
                <button type="button" class="btn btn-primary" id="periyodikBakim_kaydet">
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
    <?php
    $title = 'Periyodik Bakım  Ekle';
    $text = "Bu sayfadan periyodik bakım takibi ekleyebilirsiniz.";

    require_once 'pages/components/alert.php'
    ?>
    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <form id="periyodikBakimForm" method="post">
                            <input type="hidden" id="periyodikBakim_id" name="periyodikBakim_id" value="<?php echo Security::encrypt($id ?? 0 ) ?>">
                            <div class="card-body care-info">
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-2">
                                        <label for="talepNo" class="fw-semibold">Bakım No:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-hashtag"></i></div>
                                            <input type="text" class="form-control fw-bold" id="talepNo" name="talepNo" placeholder="Veritabanından çekilip tanımlanacak" value="<?php echo ($id == 0 || empty($id)) ? $talepNo : ($bakim->talep_no ?? $talepNo); ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                                <!-- Bakım Planlama Bilgileri -->
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-2">
                                        <label for="bakimAdi" class="fw-semibold">Bakım Adı:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-tools"></i></div>
                                            <input type="text" class="form-control" id="bakimAdi" name="bakimAdi" placeholder="Bakım Adını Giriniz" value="<?php echo $bakim->bakim_adi ?? ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <label for="bakimPeriyot" class="fw-semibold">Bakım Periyodu:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group flex-nowrap w-100">
                                            <div class="input-group-text"><i class="fas fa-sync-alt"></i></div>
                                            <select class="form-control select2 w-100" id="bakimPeriyot" name="bakimPeriyot">
                                                <option value="">Bakım Periyodu Seçiniz</option>
                                                <option value="1" <?php echo (isset($bakim->bakim_periyot) && $bakim->bakim_periyot == "1") ? 'selected' : ''; ?>>Aylık</option>
                                                <option value="3" <?php echo (isset($bakim->bakim_periyot) && $bakim->bakim_periyot == "3") ? 'selected' : ''; ?>>3 Aylık</option>
                                                <option value="6" <?php echo (isset($bakim->bakim_periyot) && $bakim->bakim_periyot == "6") ? 'selected' : ''; ?>>6 Aylık</option>
                                                <option value="12" <?php echo (isset($bakim->bakim_periyot) && $bakim->bakim_periyot == "12") ? 'selected' : ''; ?>>Yıllık</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bakım Yapılacak Yer -->
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-2">
                                        <label for="bakimYeri" class="fw-semibold">Bakım Yapılacak Yer:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group flex-nowrap w-100">
                                            <div class="input-group-text"><i class="fas fa-map-marker-alt"></i></div>
                                            <select class="form-control select2 w-100" id="bakimYeri" name="bakimYeri" onchange="toggleBlokSecimi()">
                                                <option value="">Bakım Yeri Seçiniz</option>
                                                <option value="Blok" <?php echo (isset($bakim->bakim_yeri) && $bakim->bakim_yeri == "Blok") ? 'selected' : ''; ?>>Blok</option>
                                                <option value="Site Bahçesi" <?php echo (isset($bakim->bakim_yeri) && $bakim->bakim_yeri == "Site Bahçesi") ? 'selected' : ''; ?>>Site Bahçesi</option>
                                                <option value="Havuz" <?php echo (isset($bakim->bakim_yeri) && $bakim->bakim_yeri == "Havuz") ? 'selected' : ''; ?>>Havuz</option>
                                                <option value="Spor Salonu" <?php echo (isset($bakim->bakim_yeri) && $bakim->bakim_yeri == "Spor Salonu") ? 'selected' : ''; ?>>Spor Salonu</option>
                                                <option value="Otopark" <?php echo (isset($bakim->bakim_yeri) && $bakim->bakim_yeri == "Otopark") ? 'selected' : ''; ?>>Otopark</option>
                                                <option value="Diğer" <?php echo (isset($bakim->bakim_yeri) && $bakim->bakim_yeri == "Diğer") ? 'selected' : ''; ?>>Diğer</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <label for="blokSecimi" class="fw-semibold">Blok Seçimi:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group flex-nowrap w-100">
                                            <div class="input-group-text"><i class="fas fa-building"></i></div>
                                            <select class="form-control select2 w-100" id="blokSecimi" name="blokSecimi" disabled>
                                                <?php if (empty($Blok)): ?>
                                                    <option value="">Kayıtlı blok bulunamadı</option>
                                                <?php else: ?>
                                                    <option value="">Blok Seçiniz</option>
                                                    <?php foreach ($Blok as $blok): ?>
                                                        <option value="<?php echo htmlspecialchars($blok->id); ?>"
                                                            <?php echo (isset($bakim->blok) && $bakim->blok == $blok->id) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($blok->blok_adi); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <!-- Son Bakım Tarihi ve Planlanan Tarih -->
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-2">
                                        <label for="sonBakimTarihi" class="fw-semibold">Son Bakım Tarihi:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-calendar-alt"></i></div>
                                            <input type="text" class="form-control flatpickr" id="sonBakimTarihi" name="sonBakimTarihi" placeholder="Son Bakım Tarihini Giriniz" value="<?php echo $bakim->sonBakim_tarihi ?? ''; ?>">
                                            </div>
                                        <small class="form-text text-muted">Tarih seçildiğinde program Bakım periyodunda seçilen periyoda göre bir sonraki otomatik bakım tarihini ayarlar.</small>
                                    </div>
                                    <div class="col-lg-2">
                                        <label for="planlananBakimTarihi" class="fw-semibold">Planlanan Bakım Tarihi:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-calendar-check"></i></div>
                                            <input type="text" class="form-control flatpickr" id="planlananBakimTarihi" name="planlananBakimTarihi" value="<?php echo isset($bakim->planlanan_bakim_tarihi) && $bakim->planlanan_bakim_tarihi != '' ? date('d.m.Y', strtotime($bakim->planlanan_bakim_tarihi)) : ''; ?>" readonly>
                                            </div>
                                        <small class="form-text text-muted">Planlanan bakım tarihi, son bakım tarihine göre otomatik olarak hesaplanacaktır.</small>
                                    </div>
                                </div>
                                <!-- Sorumlu Kişi/Firma -->
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-2">
                                        <label for="sorumluFirma" class="fw-semibold">Sorumlu Firma / Kişi:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-building"></i></div>
                                            <input type="text" class="form-control" id="sorumluFirma" name="sorumluFirma" placeholder="Firma veya Kişi Adı" value="<?php echo $bakim->sorumlu_firma ?? ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <label for="sorumluFirma" class="fw-semibold">Planlanan Bakım Ayarı</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-check form-switch d-flex align-items-center">
                                            <input class="form-check-input" type="checkbox" id="planlananBakimDurumu" name="planlananBakimDurumu" role="switch" style="width: 2.5em; height: 1.5em;">
                                            <label class="form-check-label ms-2" for="planlananBakimDurumu">Pasif / Aktif</label>
                                        </div>
                                        <small class="form-text text-muted">Planlanan bakım tarihini alanını aktif etmek ve ve tarihi kendiniz belirlemek için kullanılır</small>
                                    </div>
                                </div>


                                <!-- Açıklama -->
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-2">
                                        <label for="aciklama" class="fw-semibold">Açıklama:</label>
                                    </div>
                                    <div class="col-lg-10">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-info-circle"></i></div>
                                            <textarea class="form-control" id="aciklama" name="aciklama" placeholder="Açıklama Giriniz" rows="3"><?php echo $bakim->aciklama ?? ''; ?></textarea>
                                        </div>
                                    </div>
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

    function toggleBlokSecimi() {
        const bakimYeri = document.getElementById("bakimYeri");
        const blokSecimi = document.getElementById("blokSecimi");
        blokSecimi.disabled = (bakimYeri.value !== "Blok");
    }

    function hesaplaPlanlananTarih() {

        const sonBakimStr = document.getElementById("sonBakimTarihi").value;
        const periyotStr = document.getElementById("bakimPeriyot").value;
        const planlananTarihInput = document.getElementById("planlananBakimTarihi");

        if (!sonBakimStr || !periyotStr) {
            planlananTarihInput.value = '';
            return;
        }

        const parts = sonBakimStr.split(".");
        if (parts.length !== 3) {
            planlananTarihInput.value = '';
            return;
        }

        const gun = parseInt(parts[0], 10);
        const ay = parseInt(parts[1], 10) - 1;
        const yil = parseInt(parts[2], 10);
        const periyot = parseInt(periyotStr, 10);

        if (isNaN(gun) || isNaN(ay) || isNaN(yil) || isNaN(periyot)) {
            planlananTarihInput.value = '';
            return;
        }

        let sonBakimTarihi = new Date(yil, ay, gun);
        sonBakimTarihi.setMonth(sonBakimTarihi.getMonth() + periyot);

        const yeniGun = sonBakimTarihi.getDate().toString().padStart(2, '0');
        const yeniAy = (sonBakimTarihi.getMonth() + 1).toString().padStart(2, '0');
        const yeniYil = sonBakimTarihi.getFullYear();

        planlananTarihInput.value = `${yeniGun}.${yeniAy}.${yeniYil}`;
    }

    document.addEventListener("DOMContentLoaded", function () {
        toggleBlokSecimi();

        // Bakım yeri değiştiğinde blok seçimini toggle et
        document.getElementById("bakimYeri").addEventListener("change", toggleBlokSecimi);

        // Flatpickr son bakım tarihi ayarı
        flatpickr("#sonBakimTarihi", {
            dateFormat: "d.m.Y",
            onChange: hesaplaPlanlananTarih
        });

        // Planlanan bakım tarihi readonly, kullanıcı değiştiremesin
        flatpickr("#planlananBakimTarihi", {
            dateFormat: "d.m.Y",
            clickOpens: false,
            allowInput: false
        });

        // Select2 kullanıyorsan, bakım periyot değişiminde hesaplama için:
        // jQuery üzerinden select2 event dinleyicisi ekle
        if (window.jQuery && $('#bakimPeriyot').hasClass('select2-hidden-accessible')) {
            $('#bakimPeriyot').on('change.select2', function () {
                hesaplaPlanlananTarih();
            });
        } else {
            // Select2 yoksa normal event dinle
            const periyotInput = document.getElementById("bakimPeriyot");
            periyotInput.addEventListener("change", hesaplaPlanlananTarih);
        }
        // Sayfa yüklendiğinde hesaplama yap
        hesaplaPlanlananTarih();
        // Checkbox ile planlananBakimTarihi alanının readonly durumunu kontrol et
        const planlananBakimDurumu = document.getElementById("planlananBakimDurumu");
        const planlananBakimTarihi = document.getElementById("planlananBakimTarihi");

        function togglePlanlananBakimReadonly() {
            // Sadece readonly ve flatpickr ayarlarını değiştir, değeri silme
            if (planlananBakimDurumu.checked) {
                planlananBakimTarihi.removeAttribute("readonly");
                planlananBakimTarihi._flatpickr.set("clickOpens", true);
                planlananBakimTarihi._flatpickr.set("allowInput", true);
            } else {
                planlananBakimTarihi.setAttribute("readonly", true);
                planlananBakimTarihi._flatpickr.set("clickOpens", false);
                planlananBakimTarihi._flatpickr.set("allowInput", false);
            }
        }

        planlananBakimDurumu.addEventListener("change", togglePlanlananBakimReadonly);
        togglePlanlananBakimReadonly();
    });
</script>






