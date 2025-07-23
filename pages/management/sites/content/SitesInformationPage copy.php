    <div class="card-body personal-info">
        <div class="row mb-4 align-items-center">
            <div class="col-lg-2">
                <label for="sites_name" class="fw-semibold">Site Adı: </label>
            </div>
            <div class="col-lg-4">
                <div class="input-group">
                    <div class="input-group-text"><i class="feather-home"></i></div>
                    <input type="text" class="form-control" id="sites_name" name="sites_name" placeholder="Site Adı yazınız" value="<?php echo $site->site_adi ?? ''; ?>">
                </div>
            </div>

            <div class="col-lg-2">
                <label class="fw-semibold">Site Logosu: </label>
            </div>
            <div class="col-lg-4 d-flex align-items-center">
                <div id="selectedLogoPreview" class="border rounded" style="width:60px; height:60px; background:#f8f9fa; display:inline-block;
                background-image: url('<?php echo $site->logo_path ?? ''; ?>'); background-size:cover; background-position:center;"></div>
                <input type="hidden" id="selectedLogo" name="selectedLogo" value="<?php echo $site->logo_path ?? ''; ?>">
                <button type="button" class="btn btn-primary btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#logoModal" style="height:40px;">
                    Logo Seç / Yükle
                </button>
            </div>
        </div>

        <div class="row mb-4 align-items-center">
            <div class="col-lg-2">
                <label class="fw-semibold">Telefon: </label>
            </div>
            <div class="col-lg-4">
                <div class="input-group">
                    <div class="input-group-text"><i class="feather-phone"></i></div>
                    <input type="text" class="form-control" id="phone" name="phone" placeholder="0xxx xxx xx xx" value="<?php echo $site->telefon ?? ''; ?>"
                        pattern="0[0-9]{3} [0-9]{3} [0-9]{2} [0-9]{2}"
                        maxlength="14"
                        oninput="this.value = this.value
                            .replace(/[^0-9]/g, '')
                            .replace(/^(\d{1,4})(\d{0,3})(\d{0,2})(\d{0,2}).*/, function(_, a, b, c, d) {
                                return [a, b, c, d].filter(Boolean).join(' ');
                            });" />
                </div>
            </div>
        </div>
        <!-- İl Seçimi -->
        <div class="row mb-4 align-items-center">
            <div class="col-lg-2">
                <label class="fw-semibold">İl: </label>
            </div>
            <div class="col-lg-4">
                <div class="input-group flex-nowrap w-100">
                    <div class="input-group-text"><i class="feather-map-pin"></i></div>
                    <?php echo $cities->citySelect("il", $site->il ?? null); ?>
                </div>
            </div>


            <!-- İlçe Seçimi -->

            <div class="col-lg-2">
                <label class="fw-semibold">İlçe: </label>
            </div>
            <div class="col-lg-4">
                <div class="input-group flex-nowrap w-100">
                    <div class="input-group-text"><i class="feather-map-pin"></i></div>
                    <select name="ilce" id="ilce" class="form-control select2" style="width:100%">
                        <option value="">İlçe Seçiniz</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row mb-4 align-items-center">
            <div class="col-lg-2">
                <label for="adres" class="fw-semibold">Adres: </label>
            </div>
            <div class="col-lg-10">
                <div class="input-group">
                    <div class="input-group-text"><i class="feather-map"></i></div>
                    <textarea class="form-control" id="adres" name="adres" cols="30" rows="3" placeholder="Site Adresini yazınız"><?php echo $site->tam_adres ?? ''; ?></textarea>
                </div>
            </div>
        </div>

        <div class="row mb-4 align-items-center">
            <div class="col-lg-2">
                <label for="description" class="fw-semibold">Açıklama: </label>
            </div>
            <div class="col-lg-10">
                <div class="input-group">
                    <div class="input-group-text"><i class="feather-type"></i></div>
                    <textarea class="form-control" id="description" name="description" cols="30" rows="5" placeholder="Site ile ilgili açıklama yazabilirsiniz"><?php echo $site->aciklama ?? ''; ?></textarea>
                </div>
            </div>
        </div>

    </div>
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
                    <form id="logoUploadForm" enctype="multipart/form-data">
                        <label for="uploadLogo" class="form-label">Yeni logo yükle:</label>
                        <input type="file" name="logoFile" class="form-control" id="uploadLogo" accept="image/*">
                    </form>
                    <div id="previewUpload" class="mt-2"></div>

                </div>
                <div class="modal-footer">
                    <button type="button" id="confirmLogo" class="btn btn-success" data-bs-dismiss="modal">Seçimi Onayla</button>
                </div>
            </div>
        </div>
    </div>


    <!--diğer sekmeyi aktif etme başlangıç -->

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
    </script>
    <!-- Zorunlu alan kontro ve diğer sekmeyi aktif etme bitiş -->

    <script>
        $(document).ready(function() {
            $('#il').change(function() {

                var cityID = $(this).val();


                if (cityID) {
                    $.ajax({
                        type: 'POST',
                        url: '/api/il-ilce.php',
                        data: {
                            city_id: cityID
                        },
                        success: function(html) {
                            $('#ilce').html(html);
                        }
                    });
                } else {
                    $('#ilce').html('<option value="">İlçe Seçiniz</option>');
                }
            });
        });
    </script>
    <!-- il ilçe düzenlemede seçili gelip diğer il ve ilçe seçimi için aktif olması  -->
    <script>
        $(document).ready(function() {
            var selectedIl = '<?= $site->il ?? '' ?>';
            var selectedIlce = '<?= $site->ilce ?? '' ?>';

            if (selectedIl) {
                $.ajax({
                    type: 'POST',
                    url: 'api/il-ilce.php',
                    data: {
                        city_id: selectedIl,
                        selected_ilce: selectedIlce
                    },
                    success: function(html) {
                        $('#ilce').html(html);
                    }
                });
            }
        });
    </script>

    <script>
        $(document).ready(function() {
            // Logo seçim işlemleri
            var selectedLogo = '';

            // Logo modalındaki seçeneklere tıklama
            $(document).on('click', '.logo-option', function() {
                $('.logo-option').removeClass('border-primary');
                $(this).addClass('border-primary');
                selectedLogo = $(this).attr('src');
            });

            // Logo yükleme önizleme
            $('#uploadLogo').change(function(e) {
                var file = e.target.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#previewUpload').html(
                            '<img src="' + e.target.result + '" class="border rounded border-primary" style="width:80px; height:80px; cursor:pointer;">'
                        );
                        selectedLogo = e.target.result; // Base64 formatında
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Logo seçimini onaylama
            $('#confirmLogo').click(function() {
                if (selectedLogo) {
                    // Eğer yeni bir logo yüklendiyse (base64 formatında)
                    if (selectedLogo.startsWith('data:image')) {
                        uploadLogoToServer(selectedLogo);
                    } else {
                        // Hazır logolardan biri seçildiyse
                        $('#selectedLogoPreview').css('background-image', 'url("' + selectedLogo + '")');
                        $('#selectedLogo').val(selectedLogo);
                    }
                }
            });

            // Base64 formatındaki logoyu sunucuya yükleme
            function uploadLogoToServer(base64Image) {
                $.ajax({
                    url: '/pages/management/sites/content/upload-logo.php',
                    type: 'POST',
                    data: {
                        image: base64Image,
                        site_id: '<?php echo $site->id ?? 0; ?>'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#selectedLogoPreview').css('background-image', 'url("' + response.file_path + '")');
                            $('#selectedLogo').val(response.file_path);
                        } else {
                            alert('Logo yükleme hatası: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Logo yükleme sırasında bir hata oluştu.');
                    }
                });
            }
        });
    </script>