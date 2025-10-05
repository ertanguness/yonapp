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
            <div class="col-lg-4 d-flex align-items-center gap-2">
                <input type="hidden" id="selectedLogo" name="selectedLogo" value="<?= $site->logo_path ?? '' ?>">
                <?php
                $fullLogo = $site->logo_path ?? '';
                $justLogo = $fullLogo ? explode('-', $fullLogo, 2)[1] : 'default.png';
                ?>
                <?php
                // Logo dosyasının yolu
                $logoFile = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/logo/' . $fullLogo;

                // Eğer dosya varsa onu, yoksa varsayılan logoyu kullan
                $logoUrl = file_exists($logoFile) && !empty($fullLogo)
                    ? '/assets/images/logo/' . $fullLogo
                    : '/assets/images/logo/default-logo.png'; // default-logo.png dosyasını bu klasöre koymalısın
                ?>

                <img id="logoPreview"
                    src="<?= $logoUrl ?>"
                    alt="Site Logosu"
                    style="height: 60px; background-color: #f8f9fa; padding: 6px; border-radius: 12px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);"
                    class="img-fluid">

                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#logoModal">Seç / Yükle</button>
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
    <style>
        .logo-option.border-primary {
            box-shadow: 0 0 0 3px #0d6efd !important;
        }
    </style>

    <!-- Modal Başlangıç -->
    <div class="modal fade" id="logoModal" tabindex="-1" aria-labelledby="logoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoModalLabel">Logo Seç veya Yükle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                </div>
                <div class="modal-body">

                    <!-- Mevcut logolar -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mevcut Logolar:</label>
                        <div class="d-flex flex-wrap gap-2">
                            <?php
                            $dir = dirname(__DIR__, 4) . '/assets/images/logo/';
                            $webPath = '/assets/images/logo/';
                            $files = glob($dir . '*.{png,jpg,jpeg,gif}', GLOB_BRACE);

                            foreach ($files as $file) {
                                $filename = basename($file);

                                // "-" işaretine kadar olan kısmı al
                                $prefix = explode('-', $filename)[0];

                                // Eğer $id boşsa sadece "0-" ile başlayanlar gösterilsin
                                if ($id == 0 && $prefix != "0") continue;

                                // Eğer $id varsa hem 0- hem de $id- ile başlayanlar gösterilsin
                                if ($id != 0 && !in_array($prefix, ["0", (string)$id], true)) continue;

                                echo '<img src="' . $webPath . $filename . '" class="logo-option border rounded" style="width:80px; height:80px; object-fit:contain; cursor:pointer;">';
                            }
                            ?>

                        </div>
                    </div>

                    <!-- Yeni logo yükle -->
                    <div class="mb-3 mt-4">
                        <label for="uploadLogo" class="form-label fw-bold">Yeni Logo Yükle:</label>
                        <form id="logoUploadForm" enctype="multipart/form-data">
                            <input type="file" name="logoFile" class="form-control" id="uploadLogo" accept="image/*">
                        </form>
                        <div id="previewUpload" class="mt-2"></div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" id="confirmLogo" class="btn btn-success" data-bs-dismiss="modal">Seçimi Onayla</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Bitiş -->


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
            let selectedFileName = "";

            // Modal içindeki görsele tıklanırsa seç
            $(".logo-option").on("click", function() {
                $(".logo-option").removeClass("border-primary border-3");
                $(this).addClass("border-primary border-3");
                selectedFileName = $(this).attr("src").split("/").pop(); // logo dosya adı
            });

            // Modal onayla butonuna tıklanınca seçilen logo önizleme ve inputa yazılsın
            $("#confirmLogo").on("click", function() {
                if (selectedFileName !== "") {
                    $("#logoPreview").attr("src", "assets/images/logo/" + selectedFileName);
                    $("#selectedLogo").val(selectedFileName);
                }
            });

            // Dosya seçildiğinde önizleme ve otomatik yükleme
            $("#uploadLogo").on("change", function() {
                const file = this.files[0];
                if (file) {
                    const formData = new FormData();
                    formData.append("logoFile", file);
                    formData.append("site_id", "<?= (empty($id) || $id == 0) ? $siteYeniID  : $id ?>"); // PHP'den alınan ID 1 artırılarak gönderiliyor

                    $.ajax({
                        url: "pages/management/sites/content/upload_logo.php",
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(res) {
                            const json = JSON.parse(res);
                            if (json.status === "success") {
                                const imagePath = "assets/images/logo/" + json.filename;
                                $("#logoPreview").attr("src", imagePath);
                                $("#selectedLogo").val(json.filename); // Tüm isim: 42-logox.jpg gibi
                                $("#logoModal").modal("hide");
                            } else {
                                alert("Logo yükleme başarısız: " + json.message);
                            }
                        }
                    });

                }
            });
        });
    </script>