<?php
$site_id = $_SESSION['site_id'] ?? 0;

use Model\BlockModel;
use Model\DefinesModel;

$Block = new BlockModel();
$blocks = $Block->getBlocksBySite($site_id);

$definesModel = new DefinesModel();
$apartmentTypes = $definesModel->getDefinesTypes($site_id,3);
?>

<div class="card-body apartment-info">
    <div class="row mb-4 align-items-center">
        <div class="col-lg-2">
            <label for="blockName" class="fw-semibold">Blok Adı:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group flex-nowrap w-100">
                <div class="input-group-text"><i class="feather-trello"></i></div>
                <select class="form-select select2 w-100" id="blockName" name="blockName"  required>
                    <option value="">Blok Seçiniz</option>
                    <?php foreach ($blocks as $block): ?>
                        <option value="<?= htmlspecialchars($block->id) ?>">
                            <?= htmlspecialchars($block->block_name) ?>
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
                <input type="text" class="form-control" id="floor" name="floor" placeholder="Kat Giriniz" required>
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
                <input type="text" class="form-control" id="flatNumber" name="flatNumber" placeholder="Daire No Giriniz" required>
            </div>
        </div>
        <div class="col-lg-2">
            <label for="apartmentType" class="fw-semibold">Daire Tipi:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group flex-nowrap w-100">
                <div class="input-group-text"> <i class="feather-home"></i></div>
                <select class="form-select select2 w-100" name="apartment_type" id="apartment_type" required>
                    <option value="">Daire Tipi Seçin</option>
                    <?php foreach ($apartmentTypes as $type): ?>
                        <option value="<?= htmlspecialchars($type['id']) ?>">
                            <?= htmlspecialchars($type['define_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
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
                <input type="number" class="form-control" id="grossArea" name="grossArea" placeholder="Brüt Alan Giriniz" min="1">
            </div>
        </div>

        <div class="col-lg-2">
            <label for="netArea" class="fw-semibold">Net Alan (m²):</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text"><i class="feather-minimize"></i></div>
                <input type="number" class="form-control" id="netArea" name="netArea" placeholder="Net Alan Giriniz" min="1">
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
                <input type="number" class="form-control" id="landShare" name="landShare" placeholder="Arsa Payı Giriniz" min="1">
            </div>
        </div>

        <div class="col-lg-2">
            <label for="status" class="fw-semibold">Kullanım Durumu:</label>
        </div>
        <div class="col-lg-4">
            <div class="form-check form-switch d-flex align-items-center">
                <input class="form-check-input" type="checkbox" id="status" name="status" style="transform: scale(2.0);">
                <label class="form-check-label ms-4" for="status"></label>
            </div>
        </div>
    </div>

    
</div>
<!--
<script>
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

    document.querySelectorAll(".card-body.apartment-info [required]").forEach(function(field) {
        field.addEventListener("input", function() {
            if (field.value.trim()) {
                field.classList.remove("is-invalid");
            }
        });
    });
</script>
-->