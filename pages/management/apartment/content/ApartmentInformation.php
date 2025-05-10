<?php

    require_once __DIR__ . '/../../../../App/Helper/cities.php';
?>

<div class="card-body apartment-info">
    <div class="row mb-4 align-items-center">
        <div class="col-lg-2">
            <label for="blockName" class="fw-semibold">Blok Adı:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group flex-nowrap w-100">
                <div class="input-group-text"><i class="feather-trello"></i></div>
                <select class="form-select select2 w-100" id="blockName" required>
                    <option value="">Seçiniz</option>
                </select>
            </div>
        </div>

        <div class="col-lg-2">
            <label for="flatNumber" class="fw-semibold">Daire No:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text"><i class="feather-hash"></i></div>
                <input type="text" class="form-control" id="flatNumber" placeholder="Daire No Giriniz" required>
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
                <input type="number" class="form-control" id="grossArea" placeholder="Brüt Alan Giriniz" min="1">
            </div>
        </div>

        <div class="col-lg-2">
            <label for="netArea" class="fw-semibold">Net Alan (m²):</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text"><i class="feather-minimize"></i></div>
                <input type="number" class="form-control" id="netArea" placeholder="Net Alan Giriniz" min="1">
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
                <input type="number" class="form-control" id="landShare" placeholder="Arsa Payı Giriniz" min="1">
            </div>
        </div>

        <div class="col-lg-2">
            <label for="floor" class="fw-semibold">Kat:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text"><i class="feather-layers"></i></div>
                <input type="text" class="form-control" id="floor" placeholder="Kat Giriniz" required>
            </div>
        </div>
    </div>

    <div class="row mb-4 align-items-center">
        <div class="col-lg-2">
            <label for="apartmentType" class="fw-semibold">Daire Tipi:</label>
        </div>
        <div class="col-lg-4">
        <div class="input-group flex-nowrap w-100">
                <div class="input-group-text"> <i class="feather-home"></i></div>
                <select class="form-control select2 w-100" id="apartmentType" required>
                   
                </select>
            </div>
        </div>

        <div class="col-lg-2">
            <label for="status" class="fw-semibold">Durumu:</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text"><i class="feather-check-circle"></i></div>
                <input type="text" class="form-control" id="status" placeholder="Durum Giriniz">
            </div>
        </div>
    </div>   
</div>

<script>
    document.getElementById("saveMySites").addEventListener("click", function(event) {
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