<div class="card-body personal-info">
    <div class="row mb-4 align-items-center">
        <div class="col-lg-2">
            <label for="fullname" class="fw-semibold">Site Adı: </label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text"><i class="feather-home"></i></div>
                <input type="text" class="form-control" id="fullname" placeholder="Site Adı yazınız" required>
            </div>
        </div>

        <div class="col-lg-2">
            <label class="fw-semibold">Site Logosu: </label>
        </div>
        <div class="col-lg-4 d-flex align-items-center">
            <div id="selectedLogoPreview" class="border rounded" style="width:60px; height:60px; background:#f8f9fa; display:inline-block;"></div>
            <input type="hidden" id="selectedLogo" name="selectedLogo">
            <button type="button" class="btn btn-primary btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#logoModal" style="height:40px;"> 
                Logo Seç / Yükle
            </button>
        </div>
    </div>



    <div class="row mb-4 align-items-center">
        <div class="col-lg-2">
            <label for="fullnameInput" class="fw-semibold">İl: </label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text"><i
                        class="feather-tag"></i></div>
                <input type="text" class="form-control" id="il" placeholder="İl Seçiniz" required>
            </div>
        </div>

        <div class="col-lg-2">
            <label for="fullnameInput" class="fw-semibold">İlçe: </label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <div class="input-group-text"><i
                        class="feather-tag"></i></div>
                <input type="text" class="form-control" id="ilce" placeholder="İlçe Seçiniz" required>
            </div>
        </div>
    </div>
    <div class="row mb-4 align-items-center">
        <div class="col-lg-2">
            <label for="adres" class="fw-semibold">Adres: </label>
        </div>
        <div class="col-lg-10">
            <div class="input-group">
                <div class="input-group-text"><i
                        class="feather-map-pin"></i></div>
                <textarea class="form-control" id="adres" cols="30" rows="3" placeholder="Site Adresini yazınız" required></textarea>
            </div>
        </div>
    </div>
    <div class="row mb-4 align-items-center">
        <div class="col-lg-2">
            <label for="aboutInput" class="fw-semibold">About: </label>
        </div>
        <div class="col-lg-10">
            <div class="input-group">
                <div class="input-group-text"><i
                        class="feather-type"></i></div>
                <textarea class="form-control" id="aboutInput" cols="30"
                    rows="5" placeholder="Site ile ilgili açılama yazabilirsiniz"></textarea>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-end mt-3">
        <button type="button" class="btn btn-primary" id="sitesTabButton">
            İlerle &nbsp;&nbsp; <i class="feather-arrow-right"></i>
        </button>
    </div>
</div>

<!-- Modal -->
<!-- Modal -->
<div class="modal fade" id="logoModal" tabindex="-1" aria-labelledby="logoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoModalLabel">Logo Seç veya Yükle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-wrap gap-2">
                    <?php
                    $dir = __DIR__ . '/../../../assets/images/logo/';
                    $webPath = '/assets/images/logo/';
                    $files = glob($dir . '*.{png,jpg,jpeg,gif}', GLOB_BRACE);
                    foreach ($files as $file) {
                        $filename = basename($file);
                        echo '<img src="' . $webPath . $filename . '" class="logo-option border rounded" style="width:80px; height:80px; cursor:pointer;">';
                    }

                    ?>
                </div>
                <label for="uploadLogo" class="form-label">Yeni logo yükle:</label>
                <input type="file" class="form-control" id="uploadLogo" accept="image/*">
                <div id="previewUpload" class="mt-2"></div>
            </div>
            <div class="modal-footer">
                <button type="button" id="confirmLogo" class="btn btn-success" data-bs-dismiss="modal">Seçimi Onayla</button>
            </div>
        </div>
    </div>
</div>


<!-- Zorunlu alan kontro ve diğer sekmeyi aktif etme başlangıç -->
<!-- Uyarı Mesajı (Toast) -->


<script>
    document.getElementById("sitesTabButton").addEventListener("click", function(event) {
        event.preventDefault(); // Formun post edilmesini engelle

        var requiredFields = document.querySelectorAll(".card-body.personal-info [required]");
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
            var blokBilgileriTab = document.querySelector('[data-bs-target="#blokbilgileriTab"]');
            if (blokBilgileriTab) {
                new bootstrap.Tab(blokBilgileriTab).show();
            }
        } else {
            var toast = new bootstrap.Toast(document.getElementById('warningToast'));
            toast.show();
        }
    });

    document.querySelectorAll(".card-body.personal-info [required]").forEach(function(field) {
        field.addEventListener("input", function() {
            if (field.value.trim()) {
                field.classList.remove("is-invalid");
            }
        });
    });
</script>
<script>
    let selectedLogoSrc = '';

    const logos = document.querySelectorAll('.logo-option');
    logos.forEach(logo => {
        logo.addEventListener('click', function() {
            logos.forEach(l => l.classList.remove('border-primary'));
            this.classList.add('border-primary');
            selectedLogoSrc = this.src;
            document.getElementById('previewUpload').innerHTML = ''; // yeni yükleme varsa temizle
        });
    });

    document.getElementById('uploadLogo').addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                selectedLogoSrc = e.target.result;
                document.getElementById('previewUpload').innerHTML = `<img src="${selectedLogoSrc}" class="border rounded" style="width:80px; height:80px;">`;
                logos.forEach(l => l.classList.remove('border-primary'));
            };
            reader.readAsDataURL(file);
        }
    });

    document.getElementById('confirmLogo').addEventListener('click', function() {
        document.getElementById('selectedLogo').value = selectedLogoSrc;
        document.getElementById('selectedLogoPreview').style.backgroundImage = `url('${selectedLogoSrc}')`;
        document.getElementById('selectedLogoPreview').style.backgroundSize = 'cover';
        document.getElementById('selectedLogoPreview').style.backgroundPosition = 'center';
    });
</script>


<!-- Zorunlu alan kontro ve diğer sekmeyi aktif etme bitiş -->