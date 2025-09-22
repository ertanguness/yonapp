<?php

use App\Helper\Security;
use App\Helper\Helper;
use App\Helper\Date;
use App\Helper\Aidat;
use Model\DairelerModel;
use Model\TahsilatOnayModel;
use Model\KisilerModel;

$DueHelper = new Aidat();
$Daire = new DairelerModel();
$Kisi = new KisilerModel();

$TahsilatOnay = new TahsilatOnayModel();

$bekleyen_tahsilatlar = $TahsilatOnay->BekleyenTahsilatlar($_SESSION['site_id']);

?>

<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Site Borç Listesi</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="index?p=home">Ana Sayfa</a></li>
            <li class="breadcrumb-item">Borç Listesi</li>
        </ul>
    </div>
    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex d-md-none">
                <a href="javascript:void(0)" class="page-header-right-close-toggle">
                    <i class="feather-arrow-left me-2"></i>

                </a>
            </div>
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">

                <a href="/yonetici-aidat-odeme" class="btn btn-outline-secondary">
                    <i class="feather-arrow-left me-2"></i>Listeye Dön
                </a>
                <a href="index?p=dues/payment/upload-from-xls" class="btn btn-outline-success">
                    <i class="feather-file-plus me-2"></i>Toplu Onay
                </a>
            </div>
        </div>
    </div>
</div>

<div class="main-content">
    <?php
    $title = "Tahsilat Onaylama";
    $text = "Bu sayfada yüklenen tahsilatlardan kategori bazlı ödeme onayı yapabilirsiniz.";
    require_once 'pages/components/alert.php';
    ?>

    <div class="row mb-5">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card widget-tasks-content">
                        <div class="card-body custom-card-action p-0">
                            <div class="table-responsive tasks-items-wrapper m-3">
                                <table class="table table-hover datatables " id="tahsilatOnayTable">
                                    <thead>
                                        <tr>
                                            <th>Sıra</th>
                                            <th>Referans No</th>
                                            <th style="width:30%">Açıklama</th>
                                            <th>Ödenen Tutarı</th>
                                            <th>İşlenen Tutar</th>
                                            <th>Kalan Tutar</th>
                                            <th>İşlenecek Tutar</th>

                                        </tr>
                                    </thead>
                                    <!-- ... thead kısmı aynı ... -->
                                    <tbody>
                                        <?php
                                        foreach ($bekleyen_tahsilatlar as $index => $onay):
                                            $enc_id = Security::encrypt($onay->id);
                                            $tahsilat_tutari = $onay->tutar ?? 0;
                                            // Bu veriyi optimize edilmiş SQL sorgusundan aldığımızı varsayıyoruz
                                            $islenen_tutar = $onay->islenen_tutar ?? 0;
                                            $kalan_tutar = $tahsilat_tutari - $islenen_tutar;
                                            $unique_id = 'onay-satiri-' . $onay->id;
                                        ?>

                                            <!-- SADECE ANA BİLGİ SATIRI KALACAK -->
                                            <tr class="bekleyen-tahsilat-satiri">
                                                <td><?php echo $index + 1 ?></td>
                                                <td>
                                                    <span class="badge bg-light-secondary"><?php echo Security::escape($onay->referans_no ?? '#'); ?></span>
                                                </td>
                                                <td>
                                                    <div class="fw-bold"><?php echo $onay->daire_kodu  . " | " . $onay->adi_soyadi; ?></div>
                                                    <div class="text-muted fs-12 mt-1">Ödeme Tarihi: <?php echo Date::dmY($onay->islem_tarihi); ?></div>
                                                    <p class="fs-12 text-muted mt-1 fst-italic">"<?php echo htmlspecialchars($onay->aciklama) ?>"</p>
                                                </td>
                                                <td class="text-end fw-bold"><?= Helper::formattedMoney($tahsilat_tutari) ?></td>
                                                <td class="text-end text-success"><?php echo Helper::formattedMoney($islenen_tutar) ?></td>
                                                <td class="text-end text-danger fw-bold"><?php echo Helper::formattedMoney($kalan_tutar) ?></td>
                                                <td class="text-center">
                                                    <?php if ($kalan_tutar > 0.009): ?>
                                                        <!-- DİKKAT: data-bs-toggle ve data-bs-target KALDIRILDI -->
                                                        <button
                                                            class="btn btn-sm rounded btn-primary borclari-goster-btn"
                                                            data-kisi-id="<?php echo Security::encrypt($onay->kisi_id); ?>"
                                                            data-onay-id="<?php echo $onay->id; ?>">
                                                            <i class="feather-git-merge me-2"></i>Borçları Eşleştir
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="badge bg-success"><i class="feather-check-circle me-1"></i>Tamamlandı</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="tahsilatDetayModal" tabindex="-1" data-bs-keyboard="false" role="dialog">
    <div class="modal-dialog modal-dialog-scrollable modal-xl modal-dialog-centered" role="document">
        <div class="modal-content tahsilat-detay">

            <div class="modal-footer">
                <button id="btn-n-save" class="float-left btn btn-success">Save</button>
                <button class="btn btn-danger" data-dismiss="modal">Discard</button>
                <button id="btn-n-add" class="btn btn-success" disabled="disabled">Add Note</button>
            </div>
        </div>
    </div>
</div>



<!-- Sayfanızın alt kısmına, örneğin </body> etiketinden önce -->

<template id="borc-eslestirme-sablonu">
    <form class="borc-eslestirme-form" data-onay-id="{ONAY_ID}">
        <h6 class="mb-3">İşlenecek Tutarı Borçlara Dağıtın</h6>

        <!-- BORÇ LİSTESİ BURAYA EKLENECEK -->
        <div class="borclar-checkbox-grubu mb-3">
            <!-- Bu alan dinamik olarak doldurulacak -->
        </div>

        <!-- İŞLEM ALANI -->
        <div class="d-flex align-items-center justify-content-between bg-white p-3 rounded shadow-sm">
            <div>
                <label for="islenecek-tutar-{ONAY_ID}" class="form-label fs-12 text-muted">İşlenecek Tutar</label>
                <input type="text" class="form-control form-control-sm money islenecek-tutar-input" style="width: 150px;" id="islenecek-tutar-{ONAY_ID}" value="{KALAN_TUTAR_FORMATLI}">
            </div>
            <div class="text-end">
                <span class="d-block fs-12 text-muted">Seçilen Borç Toplamı</span>
                <strong class="secilen-borc-toplami fs-5">0.00 ₺</strong>
            </div>
            <div class="hstack gap-2">
                <button type="button" class="btn btn-sm btn-secondary iptal-btn">İptal</button>
                <button type="submit" class="btn btn-sm btn-success tahsilati-onayla-btn">
                    <i class="feather-check me-1"></i>Onayla ve Kaydet
                </button>
            </div>
        </div>
        <div class="alert alert-warning mt-2 fs-12 uyari-mesaji" style="display: none;">
            Uyarı mesajı alanı...
        </div>
    </form>
</template>

<!-- Borç satırları için ayrı bir template de yapabiliriz, bu daha da modüler olur -->
<template id="borc-satiri-sablonu">
    <div class="form-check border-bottom py-2">
        <input class="form-check-input borc-checkbox" type="checkbox" value="{BORC_DETAY_ID}" id="borc-{BORC_ID_UNIQUE}">
        <label class="form-check-label d-flex justify-content-between w-100" for="borc-{BORC_ID_UNIQUE}">
            <!-- Kişi id gizli inputa -->
            <input type="hidden" name="kisi_id" id="kisi_id" value="{KISI_ID}">
            <span>
                <strong>{BORC_ADI}</strong>
                <small class="text-muted d-block">Son Ödeme: {SON_ODEME_TARIHI}</small>
            </span>
            <span class="text-end">
                <span class="d-block">Anapara: <strong>{ANAPARA_TUTARI}</strong></span>
                <span class="d-block text-danger">Gecikme Zammı: <strong>{GECIKME_ZAMMI}</strong></span>
            </span>
        </label>
    </div>
</template>

<script>
    $(function() {

        // =g=====================================================
        // 2. GEREKLİ TEMPLATE'LERİ SEÇME
        // =======================================================
        const borcEslestirmeSablonu = $('#borc-eslestirme-sablonu');
        const borcSatiriSablonu = $('#borc-satiri-sablonu');

        if (borcEslestirmeSablonu.length === 0 || borcSatiriSablonu.length === 0) {
            console.error('Gerekli HTML template (şablon) etiketleri bulunamadı!');
            return;
        }


        // --- 1. MERKEZİ FONKSİYON: Bir satırın çocuk (child) bölümünü açar/kapatır ---
    
        function toggleChildRow(mainRow) {
        const $mainRow = $(mainRow); // jQuery nesnesi olduğundan emin ol
        const $button = $mainRow.find('button.borclari-goster-btn');
        const row = table.row($mainRow);

        if (row.child.isShown()) {
            // --- KAPATMA İŞLEMİ ---
            row.child.hide();
            $mainRow.removeClass('details-shown');
            $button.html('<i class="feather-git-merge me-2"></i>Borçları Eşleştir')
                   .removeClass('btn-secondary')
                   .addClass('btn-primary');
        } else {
            // --- AÇMA İŞLEMİ ---
            $button.html('<i class="feather-x me-2"></i>Kapat')
                   .removeClass('btn-primary')
                   .addClass('btn-secondary');

            row.child('<div class="borc-listesi-wrapper p-3 text-center">Yükleniyor...</div>').show();
            $mainRow.addClass('details-shown');

            const kisiId = $button.data('kisi-id');
            const onayId = $button.data('onay-id');

            //console.log('Kişi ID:', kisiId, 'Onay ID:', onayId);

            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'get_kisi_borclari',
                    kisi_id: kisiId
                },
                success: function(response) {
                    if (row.child.isShown()) { // Kullanıcı beklerken kapatmamışsa içeriği bas
                        if (response.status === 'success') {
                            const content = renderBorclarFormu(response.data, $mainRow, onayId);
                            row.child(content).show();
                        } else {
                            row.child('<div class="alert alert-danger m-3">Hata: ' + response.message + '</div>').show();
                        }
                    }
                    console.log('Borçlar yüklendi:', response.data);
                },
                error: function() {
                    if (row.child.isShown()) {
                        row.child('<div class="alert alert-danger m-3">Borçlar yüklenirken bir sunucu hatası oluştu.</div>').show();
                    }
                }
            });
        }
    }

    // --- 2. OLAY YÖNETİCİLERİ (EVENT HANDLERS) ---

    // a) "Borçları Eşleştir / Kapat" butonuna tıklandığında
    $('#tahsilatOnayTable tbody').on('click', 'button.borclari-goster-btn', function() {
        const mainRow = $(this).closest('tr');
        toggleChildRow(mainRow); // Merkezi fonksiyonu çağır
    });

    // b) Form içindeki "İptal" butonuna tıklandığında
    $('#tahsilatOnayTable tbody').on('click', '.iptal-btn', function() {
        // Önceki cevaptaki gibi, çocuk satırın bir önceki kardeşini (ana satırı) bul
        const childRow = $(this).closest('tr');
        const mainRow = childRow.prev('.dt-hasChild');

        if (mainRow.length > 0) {
            toggleChildRow(mainRow); // Yine aynı merkezi fonksiyonu çağır
        } else {
            console.error("Ana satır (.prev()) bulunamadı. DOM yapısını kontrol edin.");
        }
    });


    });


    /**
     * Gelen borç verileriyle formu oluşturur ve bir jQuery nesnesi olarak DÖNDÜRÜR.
     * @param {Array} borclar - API'den gelen borçlar dizisi
     * @param {jQuery} $anaSatir - Ana <tr> satırının jQuery nesnesi
     * @param {string|number} onayId - İşlem yapılan onay kaydının ID'si
     * @returns {jQuery} Oluşturulan formun tamamını içeren bir div'in jQuery nesnesi
     */
    function renderBorclarFormu(borclar, $anaSatir, onayId) {
        // Kalan tutarı ana satırın 6. hücresinden (index 5) al
        const kalanTutar = $anaSatir.find('td').eq(5).text().trim();

        // Ana formu template'den klonla
        const $formClone = $($('#borc-eslestirme-sablonu').html());

        // Formun ana bilgilerini doldur
        $formClone.attr('data-onay-id', onayId);
        $formClone.find('.islenecek-tutar-input').val(kalanTutar).attr('id', `islenecek-tutar-${onayId}`);

        const $borclarListesiDiv = $formClone.find('.borclar-checkbox-grubu');
        const borcSatiriHtml = $('#borc-satiri-sablonu').html();

        if (borclar.length === 0) {
            $borclarListesiDiv.html('<div class="alert alert-info">Bu kişiye ait ödenmemiş borç bulunamadı.</div>');
        } else {
            // Her borç için satırları oluştur ve listeye ekle
            $.each(borclar, function(index, borc) {
                let uniqueId = `${onayId}-${borc.id}`;
                let $borcSatiri = $(borcSatiriHtml);

                $borcSatiri.find('.borc-checkbox').val(borc.id).attr('id', `borc-${uniqueId}`);
                $borcSatiri.find('.form-check-label').attr('for', `borc-${uniqueId}`);

                let labelHtml = $borcSatiri.find('.form-check-label').html()
                    .replace('{ONAY_ID}', onayId)
                    .replace('{KISI_ID}', borc.kisi_id)
                    .replace('{BORC_ADI}', borc.borc_adi)
                    .replace('{SON_ODEME_TARIHI}', borc.son_odeme_tarihi)
                    .replace('{ANAPARA_TUTARI}', borc.anapara)
                    .replace('{GECIKME_ZAMMI}', borc.gecikme_zammi);

                $borcSatiri.find('.form-check-label').html(labelHtml);
                $borclarListesiDiv.append($borcSatiri);
            });
        }

        // Yeni eklenen .money input'ları için inputmask'ı başlat (varsa)
        $formClone.find('.money').inputmask();

        // Oluşturulan formu bir wrapper div içinde döndür. Bu, child row'un stilini korur.
        return $('<div class="borc-listesi-wrapper p-3 bg-light rounded"></div>').append($formClone);
    }

    $(function() {
        let guncellemeIstegi; // Sadece debounce için kullanılacak.

        // =======================================================
        // CHECKBOX DEĞİŞİM OLAYI
        // =======================================================
        $('#tahsilatOnayTable tbody').on('change', '.borc-checkbox', function() {
            const $form = $(this).closest('.borc-eslestirme-form');

            // Dizi, olay içinde anlık olarak oluşturuluyor.
            const secilenBorcIdleri = $form.find('.borc-checkbox:checked').map(function() {

                return $(this).val();
            }).get();

            $form.find('.secilen-borc-toplami').html('<span class="spinner-border spinner-border-sm"></span>');

            clearTimeout(guncellemeIstegi);
            guncellemeIstegi = setTimeout(function() {
                guncelleToplamTutarSunucudan(secilenBorcIdleri, $form);
            }, 300);
        });

        // =======================================================
        // "İŞLENECEK TUTAR" INPUT DEĞİŞİM OLAYI
        // =======================================================
        $('#tahsilatOnayTable tbody').on('input', '.islenecek-tutar-input', function() {
            const $form = $(this).closest('.borc-eslestirme-form');
            uyariMesajiniGuncelle($form);
        });
    });


    /**
     * Sunucuya AJAX isteği göndererek seçilen borçların toplamını alır ve ekranı günceller.
     */
    function guncelleToplamTutarSunucudan(borcIdleri, $form) {
        const $toplamGosterge = $form.find('.secilen-borc-toplami');
        console.log('Seçilen borç ID\'leri:', borcIdleri);
        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: {
                "action": 'hesapla_toplam_tutar',
                "borc_idler": borcIdleri
                // kisi_id gereksiz olduğu için kaldırıldı.
            },
            success: function(response) {
                // DÜZELTME 1: PHP'den gelen 'success' anahtarını kontrol ediyoruz.
                if (response.success) {

                    console.log('Gelen cevap:', response);
                    const toplamTutar = parseFloat(response.toplam_tutar) || 0;

                    // DÜZELTME 2: Ham sayısal değeri data attribute'üne kaydet.
                    $toplamGosterge.data('raw-total', toplamTutar);

                    // DÜZELTME 3: Gelen sayıyı JavaScript ile para formatına çevir.
                    const formatliTutar = toplamTutar.toLocaleString('tr-TR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }) + ' ₺';
                    $toplamGosterge.text(formatliTutar);

                    // Toplam güncellendi, uyarıyı kontrol et.
                    uyariMesajiniGuncelle($form);

                } else {
                    $toplamGosterge.text('Hata!').addClass('text-danger');
                    // response.message PHP'den geliyorsa göster, gelmiyorsa genel bir mesaj yaz.
                    const message = response.message || 'Toplam hesaplanamadı.';
                    Swal.fire('Hata!', message, 'error');
                }
            },
            error: function() {
                $toplamGosterge.text('Hata!').addClass('text-danger');
                Swal.fire('Hata!', 'Toplam tutar hesaplanırken sunucuya ulaşılamadı.', 'error');
            }
        });
    }


    /**
     * Sadece uyarı mesajının görünürlüğünü kontrol eder.
     */
    function uyariMesajiniGuncelle($form) {
        const $uyariMesaji = $form.find('.uyari-mesaji');
        const $toplamGosterge = $form.find('.secilen-borc-toplami');
        const $islenecekTutarInput = $form.find('.islenecek-tutar-input');

        const secilenToplam = parseFloat($toplamGosterge.data('raw-total')) || 0;
        const islenecekTutar = parseFloat($islenecekTutarInput.val().replace(/\./g, '').replace(',', '.')) || 0;

        if (secilenToplam > 0 && islenecekTutar < secilenToplam) {
            const formatliToplam = secilenToplam.toLocaleString('tr-TR', {
                style: 'currency',
                currency: 'TRY'
            });
            $uyariMesaji.text(`İşlenecek tutar, seçilen borçların toplamından (${formatliToplam}) az. Ödeme kısmi olarak yansıtılacaktır.`).show();
        } else {
            $uyariMesaji.hide();
        }
    }


    /**
     * Yapılan Tahsilatı borç ile eşleştirir ve kaydeder.
     */
    $('#tahsilatOnayTable tbody').on('submit', '.borc-eslestirme-form', function(e) {
        e.preventDefault();
        let APIurl = "/pages/dues/payment/api/APItahsilat_onay.php";

        const $form = $(this);
        const onayId = $form.data('onay-id');
        const islenecekTutar = parseFloat($form.find('.islenecek-tutar-input').val().replace(/\./g, '').replace(',', '.')) || 0;
        const secilenBorcIdleri = $form.find('.borc-checkbox:checked').map(function() {
            return $(this).val();
        }).get();
        const $submitBtn = $form.find('.tahsilati-onayla-btn');
        const $iptalBtn = $form.find('.iptal-btn');
        const $toplamGosterge = $form.find('.secilen-borc-toplami');
        const secilenToplam = parseFloat($toplamGosterge.data('raw-total')) || 0;
        console.log('Form gönderildi. Onay ID:', onayId, 'İşlenecek Tutar:', islenecekTutar, 'Seçilen Borç ID\'leri:', secilenBorcIdleri);
       
        if (secilenBorcIdleri.length === 0) {
            Swal.fire('Uyarı', 'Lütfen en az bir borç seçin.', 'warning');
            return;
        }
        if (islenecekTutar <= 0) {
            Swal.fire('Uyarı', 'İşlenecek tutar sıfır veya negatif olamaz.', 'warning');
            return;
        }
        if (islenecekTutar > secilenToplam) {
            Swal.fire('Uyarı', 'İşlenecek tutar, seçilen borçların toplamından fazla olamaz.', 'warning');
            return;
        }
        $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Kaydediliyor...');
        $iptalBtn.prop('disabled', true);
       
         // AJAX isteği
        
        $.ajax({
            url: APIurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'tahsilati_borc_ile_eslestir',
                onay_id: onayId,
                islenecek_tutar: islenecekTutar,
                borc_idler: secilenBorcIdleri
            },
            success: function(response) {
                if (response.success) {

                    Swal.fire('Başarılı', response.message, 'success').then(() => {
                        console.log('Sunucudan gelen cevap:', response);
                        
                        // İşlem başarılı ise, ilgili satırı tablodan kaldır
                        const $anaSatir = $form.closest('tr').prev('.dt-hasChild');
                        if ($anaSatir.length > 0) {
                            const row = table.row($anaSatir);
                            row.remove().draw();
                        }
                    });
                } else {
                    const message = response.message || 'Tahsilat borçlara yansıtılırken bir hata oluştu.';
                    Swal.fire('Hata', message, 'error');
                }
            },
            error: function() {
                Swal.fire('Hata', 'Sunucuya ulaşılamadı veya bir hata oluştu.', 'error');
            },
            complete: function() {
                $submitBtn.prop('disabled', false).html('<i class="feather-check me-1"></i>Onayla ve Kaydet');
                $iptalBtn.prop('disabled', false);
            }
        });
    });
</script>