<?php
$site_id = $_SESSION['site_id'] ?? 0;

use Model\BloklarModel;
use Model\DefinesModel;
use Model\DairelerModel;
use App\Helper\DefinesHelper;

$Block = new BloklarModel();
$daireModel = new DairelerModel();
$definesModel = new DefinesModel();
$DefinesHelper = new DefinesHelper();

$blocks = $Block->SiteBloklari($site_id);
$daire = $daireModel->DaireBilgisi($site_id, $id ?? 0);
$apartmentTypes = $definesModel->getDefinesTypes($site_id, 3);
?>

<div class="card-body apartment-info">
    <div class="row mb-4 align-items-center">
        <div class="col-lg-2">
            <label for="daire_kodu" class="fw-semibold">Daire Kodu:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text"><i class="feather-key"></i></div>
                <input type="text" class="form-control" id="daire_kodu" name="daire_kodu" placeholder="Daire Kodu"
                    value="<?= htmlspecialchars($daire->daire_kodu ?? '') ?>" readonly>
            </div>
        </div>
    </div>
    <div class="row mb-4 align-items-center">
        <div class="col-lg-2">
            <label for="blockName" class="fw-semibold">Blok Adı:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group flex-nowrap w-100">
                <div class="input-group-text"><i class="feather-trello"></i></div>

               
                <select class="form-select select2 w-100" id="blockName" name="blockName">
                    <option value="">Blok Seçiniz</option>
                    <?php foreach ($blocks as $block): ?>
                        <option value="<?= htmlspecialchars($block->id) ?>"
                            <?= (isset($daire->blok_id) && $daire->blok_id == $block->id) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($block->blok_adi) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

            </div>
        </div>
        <div class="col-lg-2">
            <label for="floor" class="fw-semibold">Kat:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text"><i class="feather-layers"></i></div>
                <input type="text" class="form-control" id="floor" name="floor" placeholder="Kat Giriniz"
                    value="<?php echo $daire->kat ?? ''; ?>">
            </div>
        </div>
    </div>
    <div class="row mb-4 align-items-center">
        <div class="col-lg-2">
            <label for="flatNumber" class="fw-semibold">Daire No:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text"><i class="feather-hash"></i></div>
                <input type="text" class="form-control" id="flatNumber" name="flatNumber" placeholder="Daire No Giriniz"
                    value="<?php echo $daire->daire_no ?? ''; ?>">
            </div>
        </div>
        <div class="col-lg-2">
            <label for="apartmentType" class="fw-semibold">Daire Tipi:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group flex-nowrap w-100">
                <div class="input-group-text"> <i class="feather-home"></i></div>
                <?php echo $DefinesHelper->DaireTipiSelect("apartment_type", $daire->daire_tipi ?? null); ?>
                

            </div>
        </div>
    </div>
    <div class="row mb-4 align-items-center">
        <div class="col-lg-2">
            <label for="grossArea" class="fw-semibold">Brüt Alan (m²):</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text"><i class="feather-maximize"></i></div>
                <input type="number" class="form-control" id="grossArea" name="grossArea"
                    placeholder="Brüt Alan Giriniz" min="1" value="<?php echo $daire->brut_alan ?? ''; ?>">
            </div>
        </div>

        <div class="col-lg-2">
            <label for="netArea" class="fw-semibold">Net Alan (m²):</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text"><i class="feather-minimize"></i></div>
                <input type="number" class="form-control" id="netArea" name="netArea" placeholder="Net Alan Giriniz"
                    min="1" value="<?php echo $daire->net_alan ?? ''; ?>">
            </div>
        </div>
    </div>

    <div class="row mb-4 align-items-center">
        <div class="col-lg-2">
            <label for="landShare" class="fw-semibold">Arsa Payı (m²):</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text"><i class="feather-map"></i></div>
                <input type="number" class="form-control" id="landShare" name="landShare"
                    placeholder="Arsa Payı Giriniz" min="1" value="<?php echo $daire->arsa_payi ?? ''; ?>">
            </div>
        </div>

        <div class="col-lg-2">
            <label for="status" class="fw-semibold">Kullanım Durumu:</label>
        </div>
        <div class="col-lg-4">
            <div class="form-check form-switch d-flex align-items-center">
                <input class="form-check-input" type="checkbox" id="status" name="status" style="transform: scale(2.0);"
                    data-aktif="<?= isset($daire->aktif_mi) ? (int)$daire->aktif_mi : 0 ?>"
                    <?= (!empty($daire->aktif_mi) && $daire->aktif_mi != 0) ? 'checked' : '' ?>>
                <label class="form-check-label ms-4" for="status"></label>
                <small id="kullanimDurumu" class="form-text text-muted">
                    Bağımsız bölümde aktif olarak kalan var mı ? yok mu? onu belirtir.</small>
            </div>
        </div>
    </div>

    <div class="row mb-4 align-items-center">

        <div class="col-lg-2">
            <label for="aidattan_muaf" class="fw-semibold">Aidattan Muaf:</label>
        </div>
        <div class="col-lg-4">
            <div class="form-check form-switch d-flex align-items-center">
                <input class="form-check-input" type="checkbox" id="aidattan_muaf" name="aidattan_muaf" style="transform: scale(2.0);"
                    data-aktif="<?= isset($daire->aidattan_muaf) ? (int)$daire->aidattan_muaf : 0 ?>"
                    <?= (!empty($daire->aidattan_muaf) && $daire->aidattan_muaf != 0) ? 'checked' : '' ?>>
                <label class="form-check-label ms-4" for="aidatMuaf"></label>
                <small id="aidattanMuaf" class="form-text text-muted">
                    Seçili ise aidattan Muaftır, seçili değilse aidattan Muaf değildir.</small>
            </div>
        </div>

    </div>
    <div class="row mb-4 align-items-center">
        <div class="col-lg-2">
            <label for="aciklama" class="fw-semibold">Açıklama: </label>
        </div>
        <div class="col-lg-10">
            <div class="input-group">
                <div class="input-group-text"><i class="feather-type"></i></div>
                <textarea class="form-control" id="aciklama" name="aciklama" cols="30" rows="5" placeholder="Daire ile ilgili açıklama yazabilirsiniz"><?php echo $daire->aciklama ?? ''; ?></textarea>
            </div>
        </div>
    </div>
</div>

<!-- Daire kodu oluşturma başlangıç -->

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const blockSelect = document.getElementById('blockName');
        const flatNumberInput = document.getElementById('flatNumber');
        const hiddenCodeInput = document.getElementById('daire_kodu');
        const statusCheckbox = document.getElementById('status');

        function generateDaireKodu() {
            const selectedOption = blockSelect.options[blockSelect.selectedIndex];
            let blokAdi = selectedOption.text.trim();
            const daireNo = flatNumberInput.value.trim();
            const eskiKod = hiddenCodeInput.value.trim();

            if (!blokAdi || !daireNo) {
                return;
            }

            const blokIndex = blokAdi.toLowerCase().indexOf('blok');
            if (blokIndex !== -1) {
                blokAdi = blokAdi.substring(0, blokIndex);
            }

            const firstWord = blokAdi.trim().split(' ')[0];
            const yeniKod = `${firstWord}D${daireNo}`.toUpperCase();

            if (eskiKod && eskiKod !== yeniKod) {
                Swal.fire({
                    title: "Daire Kodu Değiştirilsin mi?",
                    html: `<div style="text-align:left;">
                        <p><strong>Mevcut Kodu:</strong> ${eskiKod}</p>
                        <p><strong>Yeni Önerilen Kod:</strong> ${yeniKod}</p>
                        <p>Yeni koda geçmek ister misiniz?</p>
                    </div>`,
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonText: "Evet, değiştir",
                    cancelButtonText: "Hayır, eski kalsın",
                }).then(result => {
                    if (result.isConfirmed) {
                        hiddenCodeInput.value = yeniKod;
                    } else {
                        hiddenCodeInput.value = eskiKod;
                    }
                });
            } else {
                hiddenCodeInput.value = yeniKod;
            }
        }

        blockSelect.addEventListener('change', generateDaireKodu);
        
        flatNumberInput.addEventListener('blur', function () {
            if (flatNumberInput.value.trim()) {
                generateDaireKodu();
            }
        });

        if (!hiddenCodeInput.value) {
            generateDaireKodu(); // sadece yeni kayıt için üret
        }
    });
</script>

<!-- <script>
    document.getElementById("save_apartment").addEventListener("click", function(event) {
        event.preventDefault(); // Formun post edilmesini engelle

        var requiredFields = document.querySelectorAll(".card-body.apartment-info [required]");
        var allFilled = true;

        requiredFields.forEach(function(field) {
            if (!field.value.trim()) {
                allFilled = false;
                field.classList.add("is-invalid");
            } else {
                field.classList.remove("is-invalid");
            }
        });

        if (allFilled) {
            var daireBilgileriTab = document.querySelector('[data-bs-target="#dairebilgileriTab"]');
            if (daireBilgileriTab) {
                new bootstrap.Tab(blokBilgileriTab).show();
            }
        } else {
            var toast = new bootstrap.Toast(document.getElementById('warningToast'));
            toast.show();
        }
    });

   
</script> -->
