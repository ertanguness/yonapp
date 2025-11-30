<?php
require_once dirname(__DIR__, levels: 3) . '/configs/bootstrap.php';

use Model\BakimMaliyetModel;
use App\Helper\Security;
use App\Services\FlashMessageService;
use App\Controllers\AuthController; // AuthController'ı da kullanabiliriz

$id = Security::decrypt($id ?? 0);
$BakimMaliyetleri = new BakimMaliyetModel();
$Maliyet = $BakimMaliyetleri->MaliyetBilgileri($id);

?>
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Maliyet ve Faturalandırma</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="ana-sayfa">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Maliyet Takip</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex d-md-none">
                <a href="javascript:void(0)" class="page-header-right-close-toggle">
                    <i class="feather-arrow-left me-2"></i>
                    <span>Geri</span>
                </a>
            </div>
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <a href="/maliyet-faturalandirma" class="btn btn-outline-secondary route-link me-2">
                    <i class="feather-arrow-left me-2"></i>
                    Listeye Dön
                </a>
                <button type="button" class="btn btn-primary" id="maliyet_kaydet">
                    <i class="feather-save me-2"></i>
                    Kaydet
                </button>
            </div>
        </div>
    </div>
</div>

<div class="main-content">
    <?php
    $title = "Maliyet ve Faturalandırma";
    $text = "Bu modülde bakım işlemlerinin maliyetlerini ve faturalarını takip edebilirsiniz. 
             Yapılan işlemler ve ödemeler sistemde kayıtlı tutulur.";
    ?>

    <div class="row">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <form id="maliyetForm" method="post">
                            <input type="hidden" id="maliyet_id" name="maliyet_id" value="<?php echo ($id > 0) ? Security::encrypt($id) : '0'; ?>">
                            <div class="card-body cost-info">

                                <!-- Bakım Türü ve Fatura No -->
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-2">
                                        <label for="bakimTuru" class="fw-semibold">İşlem Türü:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group flex-nowrap w-100">
                                            <div class="input-group-text"><i class="fas fa-tools"></i></div>
                                            <select class="form-control select2 w-100" id="bakimTuru" name="bakimTuru">
                                                <option value="">İşlem Türünü Seçiniz</option>
                                                <option value="1" <?php echo (isset($Maliyet->bakim_turu) && $Maliyet->bakim_turu == '1') ? 'selected' : ''; ?>>Bakım / Arıza / Onarım </option>
                                                <option value="2" <?php echo (isset($Maliyet->bakim_turu) && $Maliyet->bakim_turu == '2') ? 'selected' : ''; ?>>Periyodik Bakım </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <label for="talepNo" class="fw-semibold">Talep No:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group flex-nowrap w-100">
                                            <div class="input-group-text"><i class="fas fa-hashtag"></i></div>
                                            <select class="form-control select2 w-100" id="talepNo" name="talepNo">
                                                <option value="">Kayıtlı Talep No Seçiniz</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-2">
                                        <label for="makbuzTuru" class="fw-semibold">Makbuz Türü:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group flex-nowrap w-100">
                                            <div class="input-group-text"><i class="fas fa-file-invoice"></i></div>
                                            <select class="form-control select2 w-100" id="makbuzTuru" name="makbuzTuru">
                                                <option value="">Makbuz Türünü Seçiniz</option>
                                                <option value="1" <?php echo (isset($Maliyet->makbuz_turu) && $Maliyet->makbuz_turu == '1') ? 'selected' : ''; ?>>Fatura</option>
                                                <option value="2" <?php echo (isset($Maliyet->makbuz_turu) && $Maliyet->makbuz_turu == '2') ? 'selected' : ''; ?>>Gider Makbuzu</option>
                                                <option value="3" <?php echo (isset($Maliyet->makbuz_turu) && $Maliyet->makbuz_turu == '3') ? 'selected' : ''; ?>>Diğer</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-2">
                                        <label for="makbuzNo" class="fw-semibold">Makbuz No:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-receipt"></i></div>
                                            <input type="text" class="form-control" id="makbuzNo" name="makbuzNo" placeholder="Fatura Numarasını Giriniz" value="<?php echo $Maliyet->makbuz_no ?? ''; ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-2">
                                        <label for="toplamMaliyet" class="fw-semibold">Toplam Maliyet (₺):</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-money-bill-wave"></i></div>
                                            <input type="text" class="form-control" id="toplamMaliyet" name="toplamMaliyet" placeholder="Maliyeti Giriniz" onkeyup="hesaplaKalanBorç()" value="<?php echo isset($Maliyet->toplam_maliyet) ? rtrim(rtrim(number_format((float)$Maliyet->toplam_maliyet, 2, '.', ''), '0'), '.') : ''; ?>">
                                        </div>
                                        <small class="form-text text-muted">Ondalık giriş için virgül kullanın. Örnek: 1.234,56</small>
                                    </div>
                                    <div class="col-lg-2">
                                        <label for="odenenTutar" class="fw-semibold">Ödenen Tutar (₺):</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-credit-card"></i></div>
                                            <input type="text" class="form-control" id="odenenTutar" name="odenenTutar" placeholder="Ödenen Tutarı Giriniz" onkeyup="hesaplaKalanBorç()" value="<?php echo isset($Maliyet->odenen_tutar) ? rtrim(rtrim(number_format((float)$Maliyet->odenen_tutar, 2, '.', ''), '0'), '.') : ''; ?>">
                                        </div>
                                        <small class="form-text text-muted">Ondalık giriş için virgül kullanın. Örnek: 250,75</small>
                                    </div>
                                </div>
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-2">
                                        <label for="kalanBorc" class="fw-semibold">Kalan Borç (₺):</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-exclamation-circle"></i></div>
                                            <input type="text" class="form-control fw-bold" id="kalanBorc" name="kalanBorc" readonly value="<?php echo isset($Maliyet->kalan_borc) ? rtrim(rtrim(number_format((float)$Maliyet->kalan_borc, 2, '.', ''), '0'), '.') : ''; ?>">
                                        </div>
                                    </div>
                                </div>
                                <!-- Ödeme Durumu ve Tarihi -->
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-2">
                                        <label for="odemeDurumu" class="fw-semibold">Ödeme Durumu:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group flex-nowrap w-100">
                                            <div class="input-group-text"><i class="fas fa-check-circle"></i></div>
                                            <select class="form-control select2 w-100" id="odemeDurumu" name="odemeDurumu">
                                                <option value="">Ödeme Türü Seçiniz</option>
                                                <option value="1" <?php echo (isset($Maliyet->odeme_durumu) && $Maliyet->odeme_durumu == '1') ? 'selected' : ''; ?>>Kısmi Ödeme</option>
                                                <option value="2" <?php echo (isset($Maliyet->odeme_durumu) && $Maliyet->odeme_durumu == '2') ? 'selected' : ''; ?>>Ödendi</option>
                                                <option value="3" <?php echo (isset($Maliyet->odeme_durumu) && $Maliyet->odeme_durumu == '3') ? 'selected' : ''; ?>>Ödenmedi</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-2">
                                        <label for="odemeTarihi" class="fw-semibold">Ödeme Tarihi:</label>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-calendar-alt"></i></div>
                                            <input type="text" class="form-control flatpickr" id="odemeTarihi" name="odemeTarihi" value="<?php echo $Maliyet->odeme_tarihi ?? ''; ?>">
                                        </div>
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
                                            <textarea class="form-control" id="aciklama" name="aciklama" placeholder="Açıklama Giriniz" rows="3"><?php echo $Maliyet->aciklama ?? ''; ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                <!-- Fatura Ekleme -->
                                <div class="row mb-4 align-items-center">
                                    <div class="col-lg-2">
                                        <label for="makbuzEkle" class="fw-semibold">Makbuz Ekle:</label>
                                    </div>
                                    <div class="col-lg-10">
                                        <div class="input-group">
                                            <div class="input-group-text"><i class="fas fa-file-upload"></i></div>
                                            <input type="file" name="makbuzEkle[]" id="makbuzEkle" multiple>
                                            </div>
                                        <small class="text-muted d-block">Desteklenen formatlar: PDF, JPG, JPEG, PNG</small>
                                        <small class="text-info">Birden fazla dosya seçmek için Ctrl tuşuna basarak seçim yapabilirsiniz.</small>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Talep No Çekme -->
<script>
    $(document).ready(function() {
        var talepNoSelect = $("#talepNo");

        function talepleriGetir(bakimTuru, seciliTalepNo = "") {
            if (!bakimTuru) {
                talepNoSelect.html('<option value="">Kayıtlı Talep No Seçiniz</option>');
                return;
            }

            $.ajax({
                url: "/pages/repair/cost/maliyetApi.php",
                type: "GET",
                data: {
                    action: "get_talepler",
                    bakimTuru: bakimTuru
                },
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        var options = '<option value="">Kayıtlı Talep No Seçiniz</option>';
                        $.each(response.data, function(index, item) {
                            var selected = item.id == seciliTalepNo ? "selected" : "";
                            options += `<option value="${item.id}" ${selected}>${item.talep_no}</option>`;
                        });
                        talepNoSelect.html(options);
                        var val = seciliTalepNo ? String(seciliTalepNo) : '';
                        talepNoSelect.val(val).trigger("change");
                    } else {
                        talepNoSelect.html('<option value="">Talep bulunamadı</option>');
                    }
                },
                error: function() {
                    talepNoSelect.html('<option value="">Hata oluştu</option>');
                }
            });
        }

        // Kullanıcı değişiklik yaptığında
        $("#bakimTuru").on("change", function() {
            var bakimTuru = $(this).val();
            talepleriGetir(bakimTuru);
        });

        // Sayfa ilk yüklendiğinde (düzenleme modundaysa)
        var mevcutBakimTuru = $("#bakimTuru").val();
        var seciliTalepNo = "<?php echo $Maliyet->talep_no ?? ''; ?>";
        var isEdit = <?php echo ($id > 0) ? 'true' : 'false'; ?>;
        if (isEdit && mevcutBakimTuru) {
            talepleriGetir(mevcutBakimTuru, seciliTalepNo);
        } else {
            $("#bakimTuru").val('').trigger('change');
            talepNoSelect.html('<option value="">Kayıtlı Talep No Seçiniz</option>');
            $("#talepNo").val('').trigger('change');
        }
    });
</script>

<!-- Para birimi formatlı giriş -->
<script>
    $(document).ready(function() {
        $("#toplamMaliyet, #odenenTutar").inputmask("decimal", {
            prefix: "₺ ",
            groupSeparator: ".",
            radixPoint: ",",
            digits: 2,
            digitsOptional: true,
            placeholder: '',
            autoGroup: true,
            rightAlign: false,
            removeMaskOnSubmit: true,
            unmaskAsNumber: true
        });

        // kalanBorc için de aynı maskeyi uygula (readonly olsa da görüntü için)
        $("#kalanBorc").inputmask("decimal", {
            prefix: "₺ ",
            groupSeparator: ".",
            radixPoint: ",",
            digits: 2,
            digitsOptional: true,
            placeholder: '',
            autoGroup: true,
            rightAlign: false,
            allowMinus: false
        });
    });

    function hesaplaKalanBorç() {
        function toNum(v){
            v = String(v || '').trim();
            v = v.replace(/\s|₺/g,'');
            v = v.replace(/\./g,'');
            v = v.replace(',', '.');
            v = v.replace(/[^0-9.-]/g,'');
            return parseFloat(v || '0') || 0;
        }
        var toplam = toNum($("#toplamMaliyet").val());
        var odenen = toNum($("#odenenTutar").val());
        var kalan = Number((toplam - odenen).toFixed(2));
        $("#kalanBorc").inputmask('setvalue', kalan);
    }
</script>
